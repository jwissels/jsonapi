<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class LinkObject implements ObjectInterface {
	use AtMemberManager, ExtensionMemberManager;
	
	/** @var string */
	protected $href;
	/** @var MetaObject */
	protected $meta;
	
	/**
	 * @param string $href
	 * @param array  $meta optional
	 */
	public function __construct($href=null, array $meta=[]) {
		if ($href !== null) {
			$this->setHref($href);
		}
		if ($meta !== []) {
			$this->setMetaObject(MetaObject::fromArray($meta));
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addMeta($key, $value) {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $href
	 */
	public function setHref($href) {
		$this->href = $href;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if ($this->href !== null) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		if ($this->hasExtensionMembers()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		
		$array['href'] = $this->href;
		
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
