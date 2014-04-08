<?php

namespace Wikibase\InternalSerialization\Deserializers;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Deserializers\Exceptions\InvalidAttributeException;
use Deserializers\Exceptions\MissingAttributeException;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LegacyPropertyDeserializer implements Deserializer {

	private $idDeserializer;
	private $fingerprintDeserializer;

	/**
	 * @var Property
	 */
	private $property;
	private $serialization;

	public function __construct( Deserializer $idDeserializer, Deserializer $fingerprintDeserializer ) {
		$this->idDeserializer = $idDeserializer;
		$this->fingerprintDeserializer = $fingerprintDeserializer;
	}

	/**
	 * @param mixed $serialization
	 *
	 * @return Property
	 * @throws DeserializationException
	 */
	public function deserialize( $serialization ) {
		if ( !is_array( $serialization ) ) {
			throw new DeserializationException( 'Item serialization should be an array' );
		}

		$this->serialization = $serialization;
		$this->property = Property::newFromType( $this->getDataTypeId() );

		$this->setPropertyId();
		$this->addFingerprint();

		return $this->property;
	}

	private function getDataTypeId() {
		if ( !array_key_exists( 'datatype', $this->serialization ) ) {
			throw new MissingAttributeException( 'datatype' );
		}

		if ( !is_string( $this->serialization['datatype'] ) ) {
			throw new InvalidAttributeException(
				'datatype',
				$this->serialization['datatype'],
				'The datatype key should point to a string'
			);
		}

		return $this->serialization['datatype'];
	}

	private function setPropertyId() {
		if ( array_key_exists( 'entity', $this->serialization ) ) {
			$this->property->setId( $this->getPropertyId() );
		}
	}

	/**
	 * @return PropertyId
	 * @throws InvalidAttributeException
	 */
	private function getPropertyId() {
		$id = $this->idDeserializer->deserialize( $this->serialization['entity'] );

		if ( !( $id instanceof PropertyId ) ) {
			throw new InvalidAttributeException(
				'entity',
				$this->serialization['entity'],
				'Properties should have a property id'
			);
		}

		return $id;
	}

	private function addFingerprint() {
		$this->property->setFingerprint( $this->getFingerprint() );
	}

	/**
	 * @return Fingerprint
	 */
	private function getFingerprint() {
		return $this->fingerprintDeserializer->deserialize( $this->serialization );
	}

}