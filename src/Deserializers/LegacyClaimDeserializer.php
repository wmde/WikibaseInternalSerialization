<?php

namespace Wikibase\InternalSerialization\Deserializers;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Deserializers\Exceptions\MissingAttributeException;
use InvalidArgumentException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LegacyClaimDeserializer implements Deserializer {

	private $snakDeserializer;
	private $snakListDeserializer;

	private $serialization;

	public function __construct( Deserializer $snakDeserializer, Deserializer $snakListDeserializer ) {
		$this->snakDeserializer = $snakDeserializer;
		$this->snakListDeserializer = $snakListDeserializer;
	}

	/**
	 * @param mixed $serialization
	 *
	 * @return Claim
	 * @throws DeserializationException
	 */
	public function deserialize( $serialization ) {
		$this->serialization = $serialization;

		$this->assertIsArray();
		$this->assertHasKey( 'm', 'Mainsnak serialization is missing' );
		$this->assertHasKey( 'q', 'Qualifiers serialization is missing' );
		$this->assertHasKey( 'g', 'Guid is missing in Claim serialization' );

		return $this->newClaimFromSerialization();
	}

	private function assertIsArray() {
		if ( !is_array( $this->serialization ) ) {
			throw new DeserializationException( 'Claim serialization should be an array' );
		}
	}

	private function assertHasKey( $key, $message ) {
		if ( !array_key_exists( $key, $this->serialization ) ) {
			throw new MissingAttributeException( $key, $message );
		}
	}

	private function newClaimFromSerialization() {
		$claim = $this->getClaim();

		$this->setGuid( $claim );

		return $claim;
	}

	private function getClaim() {
		return new Claim(
			$this->getMainSnak(),
			$this->getQualifiers()
		);
	}

	private function getMainSnak() {
		return $this->snakDeserializer->deserialize( $this->serialization['m'] );
	}

	private function getQualifiers() {
		return $this->snakListDeserializer->deserialize( $this->serialization['q'] );
	}

	private function setGuid( Claim $claim ) {
		try {
			$claim->setGuid( $this->serialization['g'] );
		}
		catch ( InvalidArgumentException $ex ) {
			throw new DeserializationException( $ex->getMessage(), $ex );
		}
	}

}
