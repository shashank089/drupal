<?php

namespace Drupal\parkbark_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


/**
 * Provides a Api Resource
 *
 * @RestResource(
 *   id = "api_resource",
 *   label = @Translation("Api Resource"),
 *   uri_paths = {
 *     "canonical" = "/parkbark_api/api_resource"
 *   }
 * )
 */

class ApiResource extends ResourceBase {
	
	/**
	* Responds to entity GET requests.
	* @return \Drupal\rest\ResourceResponse
	*/
	public function get() {
		$response = ['message' => 'Hello, this is a rest service'];
		return new ResourceResponse($response);
	}
}













