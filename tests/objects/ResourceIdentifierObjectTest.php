<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class ResourceIdentifierObjectTest extends TestCase {
	public function testEquals_HappyPath() {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject('test', 2);
		$new = new ResourceIdentifierObject('test', 1);
		
		$this->assertFalse($one->equals($two));
		$this->assertTrue($one->equals($new));
	}
	
	public function testEquals_WithoutIdentification() {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject();
		
		$this->expectException(Exception::class);
		
		$one->equals($two);
	}
	
	public function testGetIdentificationKey_HappyPath() {
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 42);
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertSame('42', $array['id']);
		$this->assertTrue($resourceIdentifierObject->hasIdentification());
		$this->assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_SetAfterwards() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setType('user');
		
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setId(42);
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertSame('42', $array['id']);
		$this->assertTrue($resourceIdentifierObject->hasIdentification());
		$this->assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_NoIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayNotHasKey('id', $array);
		$this->assertNull($array['type']);
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testGetIdentificationKey_NoFullIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject('user');
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayNotHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testIsEmpty_WithAtMembers() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$this->assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addAtMember('context', 'test');
		
		$this->assertFalse($resourceIdentifierObject->isEmpty());
	}
	
	public function testIsEmpty_WithExtensionMembers() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$this->assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		$this->assertFalse($resourceIdentifierObject->isEmpty());
	}
}
