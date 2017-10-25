<?php

namespace Tests\Integration\Wikibase\InternalSerialization;

use Deserializers\Deserializer;
use Deserializers\DispatchableDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\InternalSerialization\DeserializerFactory;

/**
 * @covers Wikibase\InternalSerialization\DeserializerFactory
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class DeserializerFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var DeserializerFactory
	 */
	private $factory;

	protected function setUp() {
		$this->factory = TestFactoryBuilder::newDeserializerFactory( $this );
	}

	public function testNewEntityDeserializerReturnsDeserializer() {
		$deserializer = $this->factory->newEntityDeserializer();
		$this->assertInstanceOf( Deserializer::class, $deserializer );
	}

	public function testNewStatementDeserializerReturnsDeserializer() {
		$deserializer = $this->factory->newStatementDeserializer();
		$this->assertInstanceOf( Deserializer::class, $deserializer );
	}

	public function testConstructWithCustomEntityDeserializer() {
		$factory = new DeserializerFactory(
			$this->getMock( Deserializer::class ),
			new BasicEntityIdParser(),
			$this->getMock( DispatchableDeserializer::class )
		);

		$deserializer = $factory->newEntityDeserializer();
		$this->assertInstanceOf( Deserializer::class, $deserializer );
	}

}
