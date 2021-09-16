<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;

/**
 * Implements an example form.
 */

class UserListForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'userlist_form';
	}

	/**
   	* {@inheritdoc}
   	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		$query = \Drupal::database()->select('custom_table', 'ct');
	    $query->fields('ct');
	    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
	    $results = $pager->execute()->fetchAll();

		$output = array();
		foreach ($results as $result) {
			$button = [
				'#type' => 'dropbutton',
				'#links' => [
					'edit' => [
						'title' => $this->t('Edit'),
						'url' => Url::fromRoute('userlist.edit', ['id' => $result->id]),
					],
					'delete' => [
						'title' => $this->t('Delete'),
						'url' => Url::fromRoute('userlist.delete', ['id' => $result->id]),
					]
				]
			];
			$output[$result->id] = [
				'id' => $result->id,
				'username' => $result->name,
				'email' => $result->email,
				'phone_no' => $result->phone_no,
				'company_name' => $result->company_name,
				'country' => Term::load($result->country)->getName(),
				'operations' => \Drupal::service('renderer')->render($button)
			];
		}

		$header = [
			'id' => t('User id'),
			'username' => t('Username'),
			'email' => t('Email'),
			'phone_no' => t('Phone no'),
			'company_name' => t('Company Name'),
			'country' => t('Country'),
			'operations' => t('Operations')
		];

		$form['table'] = [
			'#type' => 'tableselect',
			'#header' => $header,
			'#options' => $output,
			'#empty' => t('No Data found'),
		];
		$form['pager'] = array(
			'#type' => 'pager'
		);
		
		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {

	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
	}
}




