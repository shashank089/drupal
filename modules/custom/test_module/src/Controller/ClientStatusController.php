<?php

namespace Drupal\test_module\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Url;

/**
 * Implements an Controller.
 */

class ClientStatusController extends ControllerBase {

	public function setStatus(Request $request){

		$status = $request->request->get('status');
		$userid = $request->request->get('userid');

		$user = User::load($userid);
		$user->set('field_client_status', $status);
		$user->save();

		$output = ['status' => 200, 'msg' => 'Success'];
		return new JsonResponse($output);
	}

}