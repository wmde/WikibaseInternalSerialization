<?php

namespace Tests\Integration\Wikibase\InternalSerialization;

use Deserializers\Deserializer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @covers Wikibase\InternalSerialization\DeserializerFactory
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RealEntitiesTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Deserializer
	 */
	private $deserializer;

	protected function setUp() {
		$this->deserializer = TestFactoryBuilder::newDeserializerFactoryWithDataValueSupport()->newEntityDeserializer();
	}

	/**
	 * @dataProvider itemLegacySerializationProvider
	 */
	public function testGivenLegacyItem_DeserializationReturnsItem( $fileName, $serialization ) {
		$item = $this->deserializer->deserialize( $serialization );

		$this->assertInstanceOf(
			'Wikibase\DataModel\Entity\Item',
			$item,
			$fileName . ' should deserialize into an Item'
		);
	}

	public function itemLegacySerializationProvider() {
		return $this->getEntitySerializationsFromDir( __DIR__ . '/../data/items/legacy/' );
	}

	private function getEntitySerializationsFromDir( $dir ) {
		$argumentLists = array();

		/**
		 * @var SplFileInfo $fileInfo
		 */
		foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ) as $fileInfo ) {
			if ( $fileInfo->getExtension() === 'json' ) {
				$argumentLists[] = array(
					$fileInfo->getFilename(),
					json_decode( file_get_contents( $fileInfo->getPathname() ), true )
				);
			}
		}

		return $argumentLists;
	}

	public function propertyLegacySerializationProvider() {
		return $this->getEntitySerializationsFromDir( __DIR__ . '/../data/properties/legacy/' );
	}

	/**
	 * @dataProvider propertyLegacySerializationProvider
	 */
	public function testGivenLegacyProperty_DeserializationReturnsProperty( $fileName, $serialization ) {
		$property = $this->deserializer->deserialize( $serialization );

		$this->assertInstanceOf(
			'Wikibase\DataModel\Entity\Property',
			$property,
			$fileName . ' should deserialize into a Property'
		);
	}

	/**
	 * @dataProvider currentEntitySerializationProvider
	 */
	public function testGivenCurrentEntities_DeserializationReturnsCorrectEntity( $fileName, $serialization ) {
		$entity = $this->deserializer->deserialize( $serialization );

		$expectedEntity = TestFactoryBuilder::newCurrentDeserializerFactory()
			->newEntityDeserializer()->deserialize( $serialization );

		$this->assertTrue(
			$entity->equals( $expectedEntity ),
			$fileName . ' should be deserialized into the same entity by both deserializers'
		);
	}

	public function currentEntitySerializationProvider() {
		return $this->getEntitySerializationsFromDir( __DIR__ . '/../data/items/current/' );
	}

}
