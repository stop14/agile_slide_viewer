(function ($, Drupal, once) {
  Drupal.behaviors.initSlideViewer = {
    attach: function (context, settings) {
      once('initialize', 'slide-viewer', context).forEach(function (element) {
        $(element).attr('data-async-ready',true);
      });
    }
  };

  Drupal.behaviors.initSlideItem = {
    attach: function (context, settings) {
      once('initialize', 'slide-item', context).forEach(function (element) {
        $(element).attr('data-async-ready',true);
      });
    }
  };

})(jQuery, Drupal, once);
