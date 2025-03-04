<?php

namespace Wikibase\InternalSerialization\Deserializers;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\LegacyIdInterpreter;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LegacyEntityIdDeserializer implements Deserializer {

	/**
	 * @var EntityIdParser
	 */
	private $idParser;

	public function __construct( EntityIdParser $idParser ) {
		$this->idParser = $idParser;
	}

	/**
	 * @param string|array $serialization
	 *
	 * @return EntityId
	 * @throws DeserializationException
	 */
	public function deserialize( $serialization ) {
		if ( is_string( $serialization ) ) {
			return $this->getParsedId( $serialization );
		} elseif ( $this->isLegacyFormat( $serialization ) ) {
			return $this->getIdFromLegacyFormat( $serialization );
		} else {
			throw new DeserializationException( 'Entity id format not recognized' );
		}
	}

	/**
	 * @param array $serialization
	 */
	private function isLegacyFormat( $serialization ): bool {
		return is_array( $serialization ) && count( $serialization ) == 2
			&& array_key_exists( 0, $serialization ) && array_key_exists( 1, $serialization );
	}

	private function getParsedId( string $serialization ): EntityId {
		try {
			return $this->idParser->parse( $serialization );
		} catch ( EntityIdParsingException $ex ) {
			throw new DeserializationException( $ex->getMessage(), $ex );
		}
	}

	private function getIdFromLegacyFormat( array $serialization ): EntityId {
		try {
			return LegacyIdInterpreter::newIdFromTypeAndNumber( $serialization[0], $serialization[1] );
		} catch ( InvalidArgumentException $ex ) {
			throw new DeserializationException( $ex->getMessage(), $ex );
		}
	}

}
