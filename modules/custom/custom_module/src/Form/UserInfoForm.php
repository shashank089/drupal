<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;

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
			'#type' => 'textfield',
			'#title' => $this->t('Country'),
			'#size' => 30,
			'#description' => $this->t('Enter your Country.'),
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
		if (strlen($form_state->getValue('phone_number')) < 3) {
		$form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$this->messenger()->addStatus($this->t('Your phone number is @number', ['@number' => $form_state->getValue('phone_number')]));
	}
}