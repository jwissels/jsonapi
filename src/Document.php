<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;

abstract class Document implements DocumentInterface {
	const JSONAPI_VERSION_1_0 = '1.0';
	const JSONAPI_VERSION_1_1 = '1.0';
	const JSONAPI_VERSION_DEFAULT = Document::JSONAPI_VERSION_1_0;
	
	const CONTENT_TYPE_OFFICIAL = 'application/vnd.api+json';
	const CONTENT_TYPE_DEBUG    = 'application/json';
	const CONTENT_TYPE_JSONP    = 'application/javascript';
	const CONTENT_TYPE_DEFAULT  = Document::CONTENT_TYPE_OFFICIAL;
	
	const JSONP_CALLBACK_DEFAULT = 'JSONP_CALLBACK';
	
	const LEVEL_ROOT     = 'root';
	const LEVEL_JSONAPI  = 'jsonapi';
	const LEVEL_RESOURCE = 'resource';
	
	/** @var int */
	public $httpStatusCode = 200;
	/** @var LinksObject */
	public $links;
	/** @var MetaObject */
	public $meta;
	/** @var JsonapiObject */
	public $jsonapi;
	
	public function __construct() {
		$this->setJsonapiObject(new JsonapiObject());
	}
	
	/**
	 * options
	 */
	
	/**
	 * @param string|int $statusCode
	 * 
	 * @throws InputException if an invalid code is used
	 */
	public function setHttpStatusCode($statusCode) {
		if (Validator::checkHttpStatusCode($statusCode) === false) {
			throw new InputException('can not use an invalid http status code');
		}
		
		$this->httpStatusCode = (int) $statusCode;
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is Document::LEVEL_JSONAPI, Document::LEVEL_RESOURCE, or unknown
	 */
	public function addLink($key, $href, array $meta=[], $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_ROOT) {
			if ($this->links === null) {
				$this->setLinksObject(new LinksObject());
			}
			
			$this->links->add($key, $href, $meta);
		}
		elseif ($level === Document::LEVEL_JSONAPI) {
			throw new InputException('level "jsonapi" can not be used for links');
		}
		elseif ($level === Document::LEVEL_RESOURCE) {
			throw new InputException('level "resource" can only be set on a ResourceDocument');
		}
		else {
			throw new InputException('unknown level "'.$level.'"');
		}
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is unknown
	 * @throws InputException if the $level is Document::LEVEL_RESOURCE
	 */
	public function addMeta($key, $value, $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_ROOT) {
			if ($this->meta === null) {
				$this->setMetaObject(new MetaObject());
			}
			
			$this->meta->add($key, $value);
		}
		elseif ($level === Document::LEVEL_JSONAPI) {
			if ($this->jsonapi === null) {
				$this->setJsonapiObject(new JsonapiObject());
			}
			
			$this->jsonapi->addMeta($key, $value);
		}
		elseif ($level === Document::LEVEL_RESOURCE) {
			throw new InputException('level "resource" can only be set on a ResourceDocument');
		}
		else {
			throw new InputException('unknown level "'.$level.'"');
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * @param JsonapiObject $jsonapiObject
	 */
	public function setJsonapiObject(JsonapiObject $jsonapiObject) {
		$this->jsonapi = $jsonapiObject;
	}
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->jsonapi !== null && $this->jsonapi->isEmpty() === false) {
			$array['jsonapi'] = $this->jsonapi->toArray();
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toJson(array $array=null, $prettyPrint=false) {
		$array         = $array ?: $this->toArray();
		$encodeOptions = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
		if ($prettyPrint) {
			$encodeOptions |= JSON_PRETTY_PRINT;
		}
		
		$json = json_encode($array, $encodeOptions);
		if ($json === false) {
			throw new Exception('failed to generate json: '.json_last_error_msg());
		}
		
		return $json;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendResponse($json=null, $contentType=Document::CONTENT_TYPE_DEFAULT, $prettyPrint=false) {
		if ($this->httpStatusCode === 204) {
			http_response_code($this->httpStatusCode);
			return;
		}
		
		$json = $json ?: $this->toJson($array=null, $prettyPrint);
		
		http_response_code($this->httpStatusCode);
		header('Content-Type: '.$contentType);
		
		echo $json;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendJsonpResponse($callback=Document::JSONP_CALLBACK_DEFAULT, $json=null, $prettyPrint=false) {
		$json = $json ?: $this->toJson($array=null, $prettyPrint);
		$json = $callback.'('.$json.')';
		
		$this->sendResponse($json, $contentType=Document::CONTENT_TYPE_JSONP, $prettyPrint);
	}
}
