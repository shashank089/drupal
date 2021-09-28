<?php

namespace Drupal\parkbark_api\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "social_login",
 *   label = @Translation("Social Login Api"),
 *   uri_paths = {
 *     "canonical" = "/api/social-login",
 *     "https://www.drupal.org/link-relations/create" = "/api/social-login"
 *   }
 * )
 */
class SocialLogin extends ResourceBase {

	public function post($values) {

		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;

		if(empty($values) || empty($values['email']) || empty($values['social_provider'])){
			$response['message'] = 'Please enter details';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$valid_mail = valid_email_address($values['email']);
		if($valid_mail == false){
			$response['message'] = 'Please enter email in correct format.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		$user = user_load_by_mail($values['email']);

		if(!empty($user)){
			$response['status']= 'success';
			$response['existing'] = 'true';
			$response['status_code'] = 0;
			$response['message'] = 'User Logged in';

			$data = array(
				'uid' => $user->id(),
				'name' => $user->getUsername(), 
				'email' => $user->getEmail(),
				'roles' => $user->getRoles(true),
				'social_provider' => $user->get('field_social_provider')->value,
			);
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$user = \Drupal\user\Entity\User::create();
		
		$name = $values['email'];
		$email = $values['email'];
		$pass = $values['email'];

		// Mandatory.
		$user->setUsername($name);
		$user->setEmail($email);
		$user->setPassword($pass);
		
		$user->set('field_social_provider', $values['social_provider']);
		$user->enforceIsNew();
		$user->activate();
		
		$result = $user->save();
		
		if($result == 1){
			$response['status']= 'success';
			$response['existing'] = 'false';
			$response['status_code'] = 0;
			$response['message'] = 'User added successfully';

			$data = array(
				'uid' => $user->id(),
				'name' => $user->getUsername(), 
				'email' => $user->getEmail(),
				'roles' => $user->getRoles(true),
				'social_provider' => $user->get('field_social_provider')->value,
			);
		}
		else{
			$response['status' ]= 'failed';
			$response['status_code'] = 1;
			$response['message'] = 'Some error occured.';
			$data = array();
			$response['data'] = $data;
		}
		$response['data'] = $data;

		return new ResourceResponse($response);
	}

}




