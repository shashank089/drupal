<?php

namespace Drupal\parkbark_api\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "free_tokens_api",
 *   label = @Translation("Free Tokens Api"),
 *   uri_paths = {
 *     "canonical" = "/api/free-tokens",
 *     "https://www.drupal.org/link-relations/create" = "/api/free-tokens"
 *   }
 * )
 */
class FreeTokens extends ResourceBase {
 
	public function post($var) {
		
		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;

		if(empty($var) || empty($var['userid'])){
			$response['message'] = 'Please provide details';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		$user = USER::load($var['userid']);
		if(empty($user)){
			$response['message'] = 'User does not exist.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		$var['tokens'] = 2;
		
		$connection = \Drupal::database();
		$query = $connection->query(
				"SELECT n.nid AS nid FROM {node} n
				INNER JOIN {node__field_number_of_tokens} t ON n.nid = t.entity_id 
				WHERE (n.type = 'tokens') AND (t.field_number_of_tokens_value = ".$var['tokens'].")
				LIMIT 1 OFFSET 0");
		$nodeid = $query->fetchField();
		if(!$nodeid){
			$response['message'] = 'Some error occured';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		// Total token of user node
		$node = Node::create(['type' => 'user_tokens']);
		$node->set('title', 'User Tokens');
		$body = ['value' => 'User total Tokens.', 'format' => 'basic_html'];
		$node->set('body', $body);
		$node->set('uid', 83);
		$node->set('field_number_of_tokens', $var['tokens']);
		$node->field_user->target_id = $var['userid'];
		$node->status = 1;
		$node->enforceIsNew();
		$node->save();
		
		// Token purchase record
		$node = Node::create(['type' => 'tokens_purchased']);
		$node->set('title', 'Free Tokens');
		$body = ['value' => 'Free Tokens.', 'format' => 'basic_html'];
		$node->set('body', $body);
		$node->set('uid', 83);
		$node->field_tokens->target_id = $nodeid;
		$node->field_user->target_id = $var['userid'];
		$node->status = 1;
		$node->enforceIsNew();
		$node->save();
		$nid = $node->id();
		
		$node = \Drupal\node\Entity\Node::load($nid);
		$user = User::load($node->get('field_user')->target_id);
		$tokens = Node::load($node->get('field_tokens')->target_id);
		
		$response['status']= 'success';
		$response['status_code'] = 0;
		$response['message'] = '2 Free Tokens Provided';
		
		$data = array();
		$data['title'] = $node->getTitle();
		$data['field_user'] = $user->getUsername();
		$data['field_tokens'] = $tokens->getTitle();
		$data['tokens'] = (int) Node::load($nodeid)->get('field_number_of_tokens')->value;

		$response['data'] = $data;
		return new ResourceResponse($response);
	}

}





