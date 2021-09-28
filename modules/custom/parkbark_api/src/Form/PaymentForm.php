<?php
/**
 * @file
 * Contains \Drupal\parkbark_api\Form\PaymentForm.
 */
namespace Drupal\parkbark_api\Form;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PaymentForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'payment_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $userid = NULL, $tokens = NULL) {
		
		$user = User::load($userid);
		$name = $user->getUsername();
		
		$form['userid'] = array(
			'#type' => 'hidden',
			'#value' => $userid,
		);
		$form['username'] = array(
			'#type' => 'textfield',
			'#title' => t('Name:'),
			'#default_value' => $name,
			'#disabled' => TRUE,
		);
		$form['tokens'] = array(
			'#type' => 'textfield',
			'#title' => t('Number of tokens:'),
			'#default_value' => $tokens,
			'#disabled' => TRUE,
		);
		$form['cardno'] = array(
			'#type' => 'textfield',
			'#title' => t('Card Number:'),
			'#required' => TRUE,
		);
		$form['cvv'] = array(
			'#type' => 'textfield',
			'#title' => t('CVV:'),
			'#required' => TRUE,
		);
		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Confirm Payment'),
			'#button_type' => 'primary',
		);
		return $form;
	}
	
	/**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
		if (empty($form_state->getValue('userid'))) {
			$form_state->setErrorByName('userid', $this->t('User id is not available.'));
		}
		if (empty($form_state->getValue('tokens'))) {
			$form_state->setErrorByName('tokens', $this->t('Invalid Tokens.'));
		}
		else{
			$ntokens = $form_state->getValue('tokens');
			$connection = \Drupal::database();

			$query = $connection->select('node', 'n');
			$query->join('node__field_number_of_tokens', 'ft', 'n.nid = ft.entity_id');
			$query->fields('n', array('nid'));
			$query->condition('n.type', 'tokens');
			$query->condition('ft.field_number_of_tokens_value', $ntokens);
			$query->range(0, 1);
			$result = $query->execute();

			if(empty($result->fetchField())){
				$form_state->setErrorByName('tokens', $this->t('Invalid no of Tokens.'). $nid);
			}
		}

    }

	/**
   * {@inheritdoc}
   */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		
		$userid = $form_state->getValue('userid');
		$ntokens = $form_state->getValue('tokens');
		
		$cardno = $form_state->getValue('cardno');
		$cvv = $form_state->getValue('cvv');
		
		
		// After success
		
		/* $connection = \Drupal::database();
		$query = $connection->query(
				"SELECT n.nid AS nid FROM {node} n
				INNER JOIN {node__field_price} p ON n.nid = p.entity_id 
				INNER JOIN {node__field_number_of_tokens} t ON n.nid = t.entity_id 
				WHERE (n.type = 'tokens') AND (t.field_number_of_tokens_value = '$ntokens')
				LIMIT 1 OFFSET 0");
		$result = $query->fetchAll();
		
		$nodeid = '';
		foreach($result as $key=>$value){
			$nodeid = $value->nid;
		}

		$user = User::load($userid);
		
		// Total token of user node
		//check previous token count
		$user_token_nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', 'user_tokens')
			->condition('field_user', $userid)
			->range(0, 1)
			->execute();
		$token_nodes = Node::loadMultiple($user_token_nids);
		
		if(!empty($user_token_nids)){
			foreach($token_nodes as $token_node){
				$token_nid = $token_node->id();
				$tokens = $token_node->get('field_number_of_tokens')->value;
			}
			
			//for all access pass
			if($ntokens == 'vip'){
				$updated_tokens = $ntokens;
			}else{
				$updated_tokens = $tokens + $ntokens;
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
			$node->set('field_number_of_tokens', $ntokens);
			$node->field_user->target_id = $userid;
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
		$node->field_user->target_id = $userid;
		$node->status = 1;
		$node->enforceIsNew();
		$node->save();
		$nid = $node->id();
		
		//for all park access assign a role
		if($ntokens == 'vip'){
			$user->addRole('vip');
			$user->save();
		} */
		
	}

}
  
  
  
  