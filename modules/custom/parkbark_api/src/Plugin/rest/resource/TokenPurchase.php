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
 *   id = "token_purchase_api",
 *   label = @Translation("Token Purchase Api"),
 *   uri_paths = {
 *     "canonical" = "/api/token-purchase",
 *     "https://www.drupal.org/link-relations/create" = "/api/token-purchase"
 *   }
 * )
 */
class TokenPurchase extends ResourceBase {
 
	public function post($var) {
		
		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;
		
		$config = \Drupal::config('user.settings');
		$verify_mail = $config->get('verify_mail');
		
		$price = $var['price'];
		$ntokens = $var['tokens'];

		if(empty($var) || empty($var['userid']) || empty($var['price']) || empty($var['tokens'])){
			$response['message'] = 'Please provide details';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$connection = \Drupal::database();
		$query = $connection->query(
				"SELECT n.nid AS nid FROM {node} n 
				INNER JOIN {node__field_price} p ON n.nid = p.entity_id 
				INNER JOIN {node__field_number_of_tokens} t ON n.nid = t.entity_id 
				WHERE (n.type = 'tokens') AND (t.field_number_of_tokens_value = '$ntokens') 
				AND (CAST(p.field_price_value AS DECIMAL) = CAST('$price' AS DECIMAL))
				LIMIT 1 OFFSET 0");
		$result = $query->fetchAll();
		
		$nodeid = '';
		foreach($result as $key=>$value){
			$nodeid = $value->nid;
		}

		$user = User::load($var['userid']);
		
		if(empty($nodeid) || !isset($user)){
			$response['message'] = 'Please provide correct details';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		
		
		// Total token of user node
		//check previous token count
		$user_token_nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', 'user_tokens')
			->condition('field_user', $var['userid'])
			->range(0, 1)
			->execute();
		$token_nodes = Node::loadMultiple($user_token_nids);
		
		if(!empty($user_token_nids)){
			foreach($token_nodes as $token_node){
				$token_nid = $token_node->id();
				$tokens = $token_node->get('field_number_of_tokens')->value;
			}
			
			//for all access pass
			if($var['tokens'] == 'vip' && $var['price'] == 19.99){
				$updated_tokens = $var['tokens'];
			}else{
				$updated_tokens = $tokens + $var['tokens'];
			}
			$token_node->set('field_number_of_tokens', $updated_tokens);
			$token_node->save();
		}
		else{
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
		}
		
		// Token purchase record
		$node = Node::create(['type' => 'tokens_purchased']);
		$node->set('title', 'Tokens Purchased');
		$body = ['value' => 'Token purchased.', 'format' => 'basic_html'];
		$node->set('body', $body);
		$node->set('uid', 83);
		$node->field_tokens->target_id = $nodeid;
		$node->field_user->target_id = $var['userid'];
		$node->status = 1;
		$node->enforceIsNew();
		$node->save();
		$nid = $node->id();
		
		//for all park access assign a role
		if($var['tokens'] == 'vip' && $var['price'] == 19.99){
			$user->addRole('vip');
			$user->save();
		}
		
		$node = \Drupal\node\Entity\Node::load($nid);
		$user = User::load($node->get('field_user')->target_id);
		$tokens = Node::load($node->get('field_tokens')->target_id);
		
		$response['status']= 'success';
		$response['status_code'] = 0;
		$response['message'] = 'Success';
		
		$data = array();
		$data['title'] = $node->getTitle();
		$data['field_user'] = $user->getUsername();
		$data['field_tokens'] = $tokens->getTitle();
		$data['tokens'] = Node::load($nodeid)->get('field_number_of_tokens')->value;
		$data['roles'] = array();
		if($user->hasRole('vip')){
			$data['roles'] = $user->getRoles(true);
		}

		$response['data'] = $data;
		return new ResourceResponse($response);
	}

}





