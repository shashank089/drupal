<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Implements an example form.
 */

class UserInfoForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'userinfo_form';
	}

	/**
   	* {@inheritdoc}
   	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		$country =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('countries');
		
		foreach ($country as $value) {
			$countries[$value->tid] = $value->name;
		}

		$form['name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Name'),
			'#size' => 30,
			'#description' => $this->t('Enter your Name.'),
			'#required' => TRUE,
			'#attributes' => [
				'autocorrect' => 'none',
				'autocapitalize' => 'none',
				'spellcheck' => 'false',
				'autofocus' => 'autofocus',
			]
		];
		$form['email'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Email'),
			'#size' => 30,
			'#description' => $this->t('Enter your Email.'),
			'#required' => TRUE
		];
		$form['phone_number'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Phone No'),
			'#size' => 30,
			'#description' => $this->t('Enter your Phone No.'),
			'#required' => TRUE
		];
		$form['company_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Company Name'),
			'#size' => 30,
			'#description' => $this->t('Enter your Company Name.'),
			'#required' => TRUE
		];
		$form['country'] = [
			'#type' => 'select',
			'#title' => $this->t('Country'),
			'#description' => $this->t('Select your Country.'),
			'#empty_option' => 'Select...',
			'#options' => $countries,
			'#required' => TRUE
		];
		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Save'),
			'#button_type' => 'primary',
		];
		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if (strlen($form_state->getValue('phone_number')) < 7) {
			$form_state->setErrorByName('phone_number', $this->t('Please enter valid value.'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		$name = $form_state->getValue("name");
		$email = $form_state->getValue("email");
		$phone_number = $form_state->getValue("phone_number");
		$company_name = $form_state->getValue("company_name");
		$country = $form_state->getValue("country");

		$database = \Drupal::database();
		$result = $database->insert('custom_table')
			->fields([
				'name' => $name,
				'email' => $email,
				'phone_no' => $phone_number,
				'company_name' => $company_name,
				'country' => $country,
			])
			->execute();

		if ($result) {
			$this->messenger()->addStatus($this->t('Details saved for user @username.', ['@username' => $name]));
		}

		\Drupal::logger('custom_module')->debug('<pre>'.print_r([], TRUE).'</pre>');
	}
}


