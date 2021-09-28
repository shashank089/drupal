<?php
namespace Drupal\parkbark_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\braintree_api\BraintreeApiService;
use Braintree\Transaction;
use Braintree\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;


/**
 * Provides route responses for the Example module.
 */
class PaymentController extends ControllerBase {

  /**
   * Drupal\braintree_api\BraintreeApiService definition.
   *
   * @var \Drupal\braintree_api\BraintreeApiService
   */
  protected $braintreeApi;
  
  /**
   * Constructs a new PaymentController object.
   */
  /* public function __construct(BraintreeApiService $braintree_api_braintree_api) {
    $this->braintreeApi = $braintree_api_braintree_api;
  } */
  
  /**
   * {@inheritdoc}
   */
  /* public static function create(ContainerInterface $container) {
    return new static(
      $container->get('braintree_api.braintree_api')
    );
  } */


  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function paymentPage() {
	  
	/* $gateway = $this->braintreeApi->getGateway();
	$environment = $this->braintreeApi->getEnvironment();
	$merchant_id = $this->braintreeApi->getMerchantId();
	$privatekey = $this->braintreeApi->getPrivateKey();
	$publickey = $this->braintreeApi->getPublicKey(); */
	
	/* Configuration::environment('sandbox');
	Configuration::merchantId($merchant_id);
	Configuration::publicKey($publickey);
	Configuration::privateKey($privatekey);
	*/

	$path = \Drupal::request()->getpathInfo();
	$arg  = explode('/',$path);

	$user_id = $arg[2];
	$tokens = $arg[3];

	$user = User::load($user_id);
	$name = $user->getUsername();
	
	$connection = \Drupal::database();
	$query = $connection->select('node', 'n');
	$query->join('node__field_number_of_tokens', 'ft', 'n.nid = ft.entity_id');
	$query->fields('n', array('nid'));
	$query->condition('n.type', 'tokens');
	$query->condition('ft.field_number_of_tokens_value', $tokens);
	$query->range(0, 1);
	$result = $query->execute();
	$row = $result->fetchField();
	$node = Node::load($row);
	$amount = $node->field_price->value;
	
	if(isset($_POST['payload_nonce'])){
		
		$result = Transaction::sale([
			'amount' => $amount,
			'paymentMethodNonce' => $_POST['payload_nonce'],
			'options' => [
				'submitForSettlement' => True
		  ]
		]);
		
		if($result->success){
			\Drupal::messenger()->addMessage(t('Payment Success.'), 'info');
		}
	}

	$element['name'] = array(
		'#type' => 'markup',
		'#markup' => '<div id="dropin-name" >Name : '.$name.'</div>',
    );
	$element['amount'] = array(
		'#type' => 'markup',
		'#markup' => '<div id="dropin-amount" >Total Amount To Pay: '.$amount.'</div>',
    );
    $element['dropin_container'] = array(
		'#type' => 'markup',
		'#markup' => '<div id="dropin-container"></div>',
    );
	$element['button_container'] = array(
		'#type' => 'button',
		'#value' => 'Request payment method',
		'#attributes' => array('id'=>'submit-button'),
    );
	$element['pay-payload'] = array(
		'#type' => 'markup',
		'#markup' => '<div  id="payload"></div>',
    );
	$element['button_container']['#attached']['html_head'][] = [
	[
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => '',
      '#attributes' => array('src' => 'https://js.braintreegateway.com/web/dropin/1.18.0/js/dropin.min.js'),
    ],
    'hello-world2',
  ];

	$element['button_container']['#attached']['html_head'][] = [
		[
		  '#type' => 'html_tag',
		  '#tag' => 'script',
		  '#value' => "
		  var button = document.querySelector('#submit-button');

    braintree.dropin.create({
      authorization: 'sandbox_rzxq9jzr_fbqw7rcxw3ht3wxb',
      container: '#dropin-container', 
	  paypal: {
		flow: 'checkout',
		amount: '10.00',
		currency: 'USD'
	  },
	  paypalCredit: {
		flow: 'checkout',
		amount: '10.00',
		currency: 'USD'
	  },
	  googlePay: {
		googlePayVersion: 2,
		merchantId: '136109611',
		transactionInfo: {
		  totalPriceStatus: 'FINAL',
		  totalPrice: '123.45',
		  currencyCode: 'USD'
		},
		cardRequirements: {
		  // We recommend collecting and passing billing address information with all Google Pay transactions as a best practice.
		  billingAddressRequired: true
		}
	  },
	  venmo: {},
    }, function (createErr, instance) {
      button.addEventListener('click', function () {
        instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
          // Submit payload.nonce to your server
		  
		  console.log(payload.nonce);
		  var payload_nonce = payload.nonce;
		  
			$.post('http://localhost/paytest/index.php', {
					payload_nonce: payload_nonce, 
				},
				function(data,status) {
					document.getElementById('payload').innerHTML  = data; 
				}
			);
        });
      });
    });
		  ",
		  //'#attributes' => array('src' => ''),
		],
		'hello-world',
	  ];
	$element['dropin_container']['#attached']['library'][] = 'core/jquery';
	
    return $element;
  }

}