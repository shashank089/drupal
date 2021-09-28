
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.parkbark_api = {
    attach: function attach(context, settings) {
		
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
		  
		  venmo: {},
		}, function (createErr, instance) {
		  button.addEventListener('click', function () {
			instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
			  // Submit payload.nonce to your server
			  
			  console.log(payload.nonce);
			  var payload_nonce = payload.nonce;
			  
				$.post("http://synapse.asia/parkbark10668/payment", {
						payload_nonce: payload_nonce, 
					},
					function(data,status) {
						document.getElementById("payload").innerHTML  = data; 
					}
				); 

			  //$("#payload").val(payload_nonce);
			});
		  });
		});
		
    }
  }
})(jQuery, Drupal, drupalSettings);