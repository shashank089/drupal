(function ($, window, Drupal) {
  Drupal.behaviors.testModule = {
    attach: function attach(context, settings) {

      $('.client_status').once().change(function(){
        var status = $(this).val();
        var userid = $(this).data('userid');

        $.ajax({
          url: "/set-user-status/" + userid + "/" + status,
          type: "get",
          //data: {'status' : status, 'userid' : userid} ,
          success: function (response) {},
        });
      });

    }
  };

})(jQuery, window, Drupal);

