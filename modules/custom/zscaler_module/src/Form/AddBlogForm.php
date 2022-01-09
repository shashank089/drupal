<?php

namespace Drupal\zscaler_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Implements custom form to add blog.
 */

class AddBlogForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'add_blog_form';
	}

	/**
   	* {@inheritdoc}
   	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['title'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Title'),
			'#size' => 30,
			'#required' => TRUE
		];
		$form['body'] = [
			'#type' => 'text_format',
			'#title' => $this->t('Body'),
			'#format'=> 'full_html'
		];
		$form['read_more_link'] = [
			'#type' => 'url',
			'#title' => $this->t('Read more link'),
			'#size' => 30,
		];
		$form['cover_image'] = [
			'#type' => 'managed_file',
			//'#type' => 'file',
			'#title' => t('Cover Image'),
			'#upload_location' => 'public://2021-12',
			'#upload_validators' => array(
				'file_validate_extensions' => array(),
                'file_validate_size' => array(250 * 1024),
                'file_validate_image_resolution' => array('200x350')
            ),
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

	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$current_user = \Drupal::currentUser()->id();

		$title = $form_state->getValue('title');
		$body = $form_state->getValue('body');
		$read_more_link = $form_state->getValue('read_more_link');
		// var_dump($read_more_link);die;
		$cover_image = $form_state->getValue('cover_image');

		/*$file = file_save_upload('cover_image', array(
		    	'file_validate_is_image' => array(),
		    	'file_validate_image_resolution' => array('200x350'),
		  	), 
			'public://2021-12/', FILE_EXISTS_RENAME
		);*/

	    if (!empty($cover_image)) {
	    	$fid = $cover_image[0];
	      	$file = File::load($fid);
	      	$file->setPermanent();
	      	$file->save();
	    }

		$blog = Node::create(['type' => 'blog']);
		$blog->set('title', $title);
		$blog->set('body', $body);
		$blog->field_cover_image[] = [
			//'target_id' => $file->id(),
			'target_id' => $fid,
			'alt' => '',
		];
		$blog->set('field_read_more_link', [
		    	'uri'=> $read_more_link, 
		    	'title' => ''
		  	]
		);
		$blog->set('uid', $current_user);
		$blog->enforceIsNew();

		$blog->save();
		
		\Drupal::messenger()->addMessage($this->t('Blog Added Successfully'), 'status', TRUE);
	}
}




