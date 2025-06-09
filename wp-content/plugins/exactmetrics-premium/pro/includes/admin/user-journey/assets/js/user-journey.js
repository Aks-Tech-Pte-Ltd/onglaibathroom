'use strict';

jQuery(function ($) {

  $(document).on('click', '#exactmetrics-activate-user-journey', function (event) {

    event.preventDefault();

    var button = $(this);
    var redirect_url = $(this).data('admin-url');

    $.ajax({
      type: "post",
      dataType: "json",
      url: exactmetrics_user_journey.ajax_url,
      data: {
        action: "exactmetrics_activate_addon",
        plugin: 'exactmetrics-user-journey/exactmetrics-user-journey.php',
        nonce: exactmetrics_user_journey.activate_addon_nonce,
        isnetwork: exactmetrics_user_journey.is_network,
      },
      success: function (response) {
        if (response && true === response) {
          window.location = redirect_url;
        } else {
          alert(response.error);
          return false;
        }
      }
    });
  });
});
