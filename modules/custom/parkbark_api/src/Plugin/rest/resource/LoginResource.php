<?php

namespace Drupal\parkbark_api\Plugin\rest\resource;

use Drupal\user\Entity\User;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

use Drupal\Core\Access\CsrfTokenGenerator;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "login_rest_api",
 *   label = @Translation("Login Api resource"),
 *   uri_paths = {
 *     "canonical" = "/api/login",
 *     "https://www.drupal.org/link-relations/create" = "/api/login"
 *   }
 * )
 */
 

class LoginResource extends ResourceBase {

	public function post($credentials) {
		
		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;
		
		if (!isset($credentials['name']) && !isset($credentials['pass'])) {
			$response['message'] = 'Missing credentials.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		if (!isset($credentials['name'])) {
			$response['message'] = 'Missing credentials.name.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		// Check if a user exists with that name.
		if (!user_load_by_name($credentials['name'])) {
			if ($loadUser = user_load_by_mail($credentials['name'])) {
				$credentials['name'] = $loadUser->getAccountName();
			}
		}

		if (!isset($credentials['pass'])) {
			$response['message'] = 'Missing credentials.pass.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		if (user_is_blocked($credentials['name'])) {
			$response['message'] = 'The user has not been activated or is blocked.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$response_data = [];
		if ($uid = \Drupal::service('user.auth')->authenticate($credentials['name'], $credentials['pass'])) {
			$user = USER::load($uid);
			//userLoginFinalize($user);

			// Send basic metadata about the logged in user.
			$response_data['current_user']['uid'] = $user->id();
			$response_data['current_user']['name'] = $user->getAccountName();
			$response_data['current_user']['roles'] = $user->getRoles(true);
			//$response_data['csrf_token'] = $this->csrfToken->get('rest');

			//$logout_route = $this->routeProvider->getRouteByName('user.logout.http');
			// Trim '/' off path to match \Drupal\Core\Access\CsrfAccessCheck.
			//$logout_path = ltrim($logout_route->getPath(), '/');
			//$response_data['logout_token'] = $this->csrfToken->get($logout_path);

			$response['status']= 'success';
			$response['status_code'] = 0;
			$response['message'] = 'Logged In';
			$data = array();
			$response['data'] = $response_data;
			return new ResourceResponse($response);
		}

		$response['message'] = 'Sorry, unrecognized username or password.';
		$data = array();
		$response['data'] = $response_data;
		return new ResourceResponse($response);

	}

}


