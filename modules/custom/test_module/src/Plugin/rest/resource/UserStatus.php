<?php

namespace Drupal\test_module\Plugin\rest\resource;

use Drupal\user\Entity\User;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "user_status_api",
 *   label = @Translation("User Status Api"),
 *   uri_paths = {
 *     "canonical" = "/set-user-status",
 *     "https://www.drupal.org/link-relations/create" = "/set-user-status"
 *   }
 * )
 */
class UserStatus extends ResourceBase {
 
	public function post($data) {

		$userid = $data['userid'];
		$status = $data['status'];

		$user = User::load($userid);
		$user->set('status', $status);
		$user->save();
		
		$response['status']= 'success';
		$response['status_code'] = 200;
		
		return new ResourceResponse($response);
	}

}





