<?php

use alsvanzelf\jsonapi\DataDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * there are a few use-cases for sending meta-only responses
 * in such cases, use the DataDocument
 * 
 * prefer to actually send out a resource, error or collection
 */

$jsonapi = new DataDocument();
$jsonapi->addMeta('foo', 'bar');

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$jsonapi->toJson($options);
