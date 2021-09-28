<?php

namespace Drupal\parkbark_api\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_tokens_list",
 *   label = @Translation("Get Token list"),
 *   uri_paths = {
 *     "canonical" = "/api/tokens",
 *     "https://www.drupal.org/link-relations/create" = "/api/tokens"
 *   }
 * )
 */
class TokenResource extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
 
	public function get() {

		$nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', 'tokens')
			->execute();
		$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
		
		$response['status']= 'success';
		$response['status_code'] = 0;
		$response['message'] = 'Success';
		
		$data = array();
		foreach($nodes as $key => $node){
			$data[$key]['title'] = $node->getTitle();
			$data[$key]['price'] = $node->get('field_price')->value;
			$data[$key]['number_of_tokens'] = $node->get('field_number_of_tokens')->value;
		}
		$response['data'] = $data;

		return new ResourceResponse($response);
	}

}








