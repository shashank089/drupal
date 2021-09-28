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
 *   id = "register_user_post",
 *   label = @Translation("Register user Api"),
 *   uri_paths = {
 *     "canonical" = "/api/register",
 *     "https://www.drupal.org/link-relations/create" = "/api/register"
 *   }
 * )
 */
class RegisterUserPost extends ResourceBase {

 
	public function post($values) {

		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;
		
		$config = \Drupal::config('user.settings');
		$verify_mail = $config->get('verify_mail');

		if(empty($values) || empty($values['name']) || empty($values['email']) || empty($values['pass'])){
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
		
		$ids = \Drupal::entityQuery('user')
			->condition('name', $values['name'])
			->range(0, 1)
			->execute();
			
		$user = user_load_by_mail($values['email']);

		if(!empty($ids) || !empty($user)){
			$response['message'] = 'Username or Email already exists';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$user = \Drupal\user\Entity\User::create();
		
		$name = $values['name'];
		$email = $values['email'];
		$pass = $values['pass'];

		// Mandatory.
		$user->setUsername($name);
		$user->setEmail($email);
		$user->setPassword($pass);
		$user->enforceIsNew();
		$user->activate();
		
		/* if(!$verify_mail){
			$user->activate();
		} */
		$result = $user->save();
		
		//If require email verification
		if($verify_mail){
			//send mail
			//_user_mail_notify('register_pending_approval', $user);
			//$msg = ("Thank you for applying for an account. A welcome message has been sent to your email address.\n");
		}
		
		if($result == 1){
			$response['status']= 'success';
			$response['status_code'] = 0;
			$response['message'] = 'User added successfully';

			$data = array(
				'uid' => $user->id(),
				'name' => $user->getUsername(), 
				'email' => $user->getEmail(),
				'roles' => $user->getRoles(true),
				'free_tokens' => 3
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




