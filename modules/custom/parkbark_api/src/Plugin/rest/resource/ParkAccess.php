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
 *   id = "park_access_api",
 *   label = @Translation("Park Access Api"),
 *   uri_paths = {
 *     "canonical" = "/api/park-access",
 *     "https://www.drupal.org/link-relations/create" = "/api/park-access"
 *   }
 * )
 */
class ParkAccess extends ResourceBase {
 
	public function post($var) {
		
		$response = array();
		$response['status'] = 'failed';
		$response['status_code'] = 1;
		
		if(empty($var) || empty($var['userid']) || empty($var['nodeid'])){
			$response['message'] = 'Please provide details.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		$query = \Drupal::database()->select('nodeaccess', 'n');
		$query->fields('n', array('nid'));
		$query->condition('n.nid', $var['nodeid']);
		$query->condition('n.gid', $var['userid']);
		$query->range(0, 1);
		$result = $query->execute();
		$access = $result->fetchField();
		
		if($access){
			$response['message'] = 'Access already granted.';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		$park = Node::load($var['nodeid']);
		$park_tokens = $park->get('field_tokens_for_park')->value;
		
		//$user = User::load($var['userid']);
		$query = \Drupal::database()->select('node', 'n');
		$query->join('node__field_user', 'u', 'n.nid = u.entity_id');
		$query->join('node__field_number_of_tokens', 't', 'n.nid = t.entity_id');
		$query->fields('n', array('nid'));
		$query->fields('t', array('field_number_of_tokens_value'));
		$query->condition('n.type', 'user_tokens');
		$query->condition('u.field_user_target_id', $var['userid']);
		$query->range(0, 1);
		$result = $query->execute();
		$rows = $result->fetchAll();
		
		$user_tokens = 0;
		foreach($rows as $key=>$value){
			$user_tokens_nid = $value->nid;
			$user_tokens = $value->field_number_of_tokens_value;
		}
		
		//$arr[] = ($query->__toString());
		
		if($user_tokens == 0){
			$response['message'] = 'No Tokens';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}

		//grant access
		if($user_tokens >= $park_tokens){
			//deduct user tokens
			$new_tokens = $user_tokens - $park_tokens;
			$node_token = Node::load($user_tokens_nid);
			$node_token->set('field_number_of_tokens', $new_tokens);
			$node_token->save();
			
			//allow access
			$id = db_insert('nodeaccess')
			->fields([
				'nid' => $var['nodeid'],
				'gid' => $var['userid'],
				'realm' => 'nodeaccess_uid',
				'grant_view' => 1,
				'grant_update' => 0,
				'grant_delete' => 0
			])
			->execute();

			/* $id = db_insert('node_access')
			->fields([
				'nid' => $var['nodeid'],
				'langcode' => 'en',
				'fallback' => 1,
				'gid' => $var['userid'],
				'realm' => 'nodeaccess_uid',
				'grant_view' => 1,
				'grant_update' => 0,
				'grant_delete' => 0
			])
			->execute(); */
			
		}
		else{
			$response['message'] = 'Not Enough tokens to unlock park';
			$data = array();
			$response['data'] = $data;
			return new ResourceResponse($response);
		}
		
		$response['status']= 'success';
		$response['status_code'] = 0;
		$response['message'] = 'Park details Access Granted';
		
		$data = array();
		
		$response['data'] = $data;
		return new ResourceResponse($response);
	}

}





