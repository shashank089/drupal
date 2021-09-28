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
 *   id = "user_tokens_api",
 *   label = @Translation("User Tokens Api"),
 *   uri_paths = {
 *     "canonical" = "/api/user-tokens",
 *     "https://www.drupal.org/link-relations/create" = "/api/user-tokens"
 *   }
 * )
 */
class UserTokens extends ResourceBase {
 
	public function post($values) {

		$nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', 'user_tokens')
			->condition('field_user', $values['userid'])
			->range(0, 1)
			->execute();
		$nodes = Node::loadMultiple($nids);
		
		$response['status']= 'success';
		$response['status_code'] = 0;
		$response['message'] = 'Success';
		
		$data = array();
		$data['userid'] = $values['userid'];
		
		$tokens = "0";
		foreach($nodes as $key => $node){
			$tokens = $node->get('field_number_of_tokens')->value;
		}
		$data['tokens'] = $tokens;
		
		$response['data'] = $data;
		return new ResourceResponse($response);
	}

}





