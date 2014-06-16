<?php

namespace Wikibase\InternalSerialization\Deserializers;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Wikibase\DataModel\Entity\Entity;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ClaimDeserializer implements Deserializer {

	private $legacyDeserializer;
	private $currentDeserializer;

	private $serialization;

	public function __construct( Deserializer $legacyDeserializer, Deserializer $currentDeserializer ) {
		$this->legacyDeserializer = $legacyDeserializer;
		$this->currentDeserializer = $currentDeserializer;
	}

	/**
	 * @param mixed $serialization
	 *
	 * @return Entity
	 * @throws DeserializationException
	 */
	public function deserialize( $serialization ) {
		$this->serialization = $serialization;

		if ( $this->isLegacySerialization() ) {
			return $this->fromLegacySerialization();
		}
		elseif ( $this->isCurrentSerialization() ) {
			return $this->fromCurrentSerialization();
		}
		else {
			return $this->fromUnknownSerialization();
		}
	}

	private function isLegacySerialization() {
		return array_key_exists( 'm', $this->serialization );
	}

	private function isCurrentSerialization() {
		return array_key_exists( 'mainsnak', $this->serialization );
	}

	private function fromLegacySerialization() {
		return $this->legacyDeserializer->deserialize( $this->serialization );
	}

	private function fromCurrentSerialization() {
		return $this->currentDeserializer->deserialize( $this->serialization );
	}

	private function fromUnknownSerialization() {
		try {
			return $this->legacyDeserializer->deserialize( $this->serialization );
		}
		catch ( DeserializationException $ex ) {}

		try {
			return $this->currentDeserializer->deserialize( $this->serialization );
		}
		catch ( DeserializationException $ex ) {}

		throw new DeserializationException( 'The provided serialization is not a valid claim' );
	}

}