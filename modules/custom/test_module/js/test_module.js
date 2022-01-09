(function ($, window, Drupal) {
  Drupal.behaviors.testModule = {
    attach: function attach(context, settings) {

      $('.client_status').once().change(function(){
        var status = $(this).val();
        var userid = $(this).data('userid');

        /*$.ajax({
          url: "/set-user-status/" + userid + "/" + status,
          type: "GET",
          success: function (response) {},
          complete : function() {}
        });*/

        $.ajax({
          url: "/set-client-status",
          type: "POST",
          data: {'status' : status, 'userid' : userid},
          dataType: 'json',
          beforeSend : function() {},
          success: function (response) {
            alert(response.msg);
          },
          complete : function() {}
        });

      });

    }
  };

})(jQuery, window, Drupal);

