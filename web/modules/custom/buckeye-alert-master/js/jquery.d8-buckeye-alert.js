(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.BuckeyeAlert = {
    attach: function (context, settings) {
      // Only add alert if page is not embedded in another
      if (window.location == window.parent.location) {
        $('body', context).once('buckeye-alert-init').prepend('<div id="buckeye_alert"></div>');

        $("#buckeye_alert").once('buckeye-alert').buckeyeAlert({
          url: drupalSettings.buckeye_alert.feed_url,
          messageClass: drupalSettings.buckeye_alert.messageClass,
          animate: drupalSettings.buckeye_alert.animate,
          callback: function() {
            if (drupalSettings.buckeye_alert.additional) {
              $("#buckeye_alert").find("#buckeye_alert_msg").append('<div id="buckeye_alert_extra">'+drupalSettings.buckeye_alert.additional+'</div>');
            }
          }
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
