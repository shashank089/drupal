<?php

namespace Drupal\custom_module\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Implements an Controller.
 */

class UserDeleteController extends ControllerBase {

	public function deleteInfo(){

		$id = \Drupal::routeMatch()->getRawParameter('id');
		
		$connection = \Drupal::database();
		$deleted = $connection->delete('custom_table')
			->condition('id', $id)
			->execute();

		$this->messenger()->addStatus($this->t('Details deleted for user.'));
		
		return new RedirectResponse(Url::fromRoute('custom_module.userlistform')->setAbsolute()->toString());
	}

}