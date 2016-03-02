<?php

namespace Wikibase\InternalSerialization\Deserializers;

use Deserializers\DispatchableDeserializer;
use Deserializers\Exceptions\DeserializationException;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\LegacyIdInterpreter;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LegacyEntityIdDeserializer implements DispatchableDeserializer {

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
		}
		elseif ( $this->isDeserializerFor( $serialization ) ) {
			return $this->getIdFromLegacyFormat( $serialization );
		}
		else {
			throw new DeserializationException( 'Entity id format not recognized' );
		}
	}

	/**
	 * @param string $serialization
	 *
	 * @throws DeserializationException
	 * @return EntityId
	 */
	private function getParsedId( $serialization ) {
		try {
			return $this->idParser->parse( $serialization );
		}
		catch ( EntityIdParsingException $ex ) {
			throw new DeserializationException( $ex->getMessage(), $ex );
		}
	}

	private function getIdFromLegacyFormat( array $serialization ) {
		try {
			return LegacyIdInterpreter::newIdFromTypeAndNumber( $serialization[0], $serialization[1] );
		}
		catch ( InvalidArgumentException $ex ) {
			throw new DeserializationException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * @see DispatchableDeserializer::isDeserializerFor
	 *
	 * @since 2.2
	 *
	 * @param mixed $serialization
	 *
	 * @return bool
	 */
	public function isDeserializerFor( $serialization ) {
		return is_array( $serialization )
			&& count( $serialization ) === 2
			&& array_key_exists( 0, $serialization )
			&& array_key_exists( 1, $serialization );
	}

}
