Drupal.zipcart = {

  init: function() {
    $('a.zipcart').click(Drupal.zipcart.addToCart);
  },

  addToCart: function(e) {

    // this next wants cleanup (and possibly using 'is' instead of the below)
    // basically: get me the clicked 'a', or the parent 'a'.
    if (e.target.tagName.toLowerCase() == 'a') {
      a = e.target;
    }
    else {
      if (a = $(e.target).parents('a[href]')) {
        // found parent a
      }
      else {
        return;
      }
    }

    e.preventDefault();
    // add AJAX parameter
    filePath = $(a).attr('href').replace(Drupal.settings.zipcart.path_add, Drupal.settings.zipcart.path_add_ajax);
    // add Drupal basePath
    filePath = Drupal.settings.basePath + filePath;
    // remove multiple slashes at start
    filePath = filePath.replace(/^\/+/, '/');
    $.ajax({
      'url': filePath,
      'dataType': 'json',
      success: function(data, textStatus, req) {
        Drupal.settings.zipcart.cart = data.cart;
        cart = $('.zipcart-block-downloads');
        // copy the element for animation - with thanks to jQuery 'fake' plugin by Carl Fürstenberg
        orig_offset = $(a).offset();
        orig_height = $(a).height();
        orig_width  = $(a).width();
        clone = $(a).clone();
        // prevent this element catching any events on the way across the screen
        possEvents = "blur focus focusin focusout load resize scroll unload click dblclick "  +
          "mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
          "change select submit keydown keypress keyup error";
        clone.bind(possEvents, function(event) { event.preventDefault(); });
        clone.addClass('clone');
        clone.css({
          position: 'absolute',
          visibility: 'visible',
          left: orig_offset.left,
          top: orig_offset.top,
          width: orig_width,
          height: orig_height
        });
        clone.appendTo($('body'));
        animProps = {
          top:  parseInt(cart.offset().top + (($(cart).height() - clone.height()/2) / 2)),
          left: parseInt(cart.offset().left + (($(cart).width() - clone.width()/2) / 2)),
          width: orig_width/2,
          height: orig_height/2
        };
        animCallback = function(data) {
          clone.fadeOut().remove();
          $('.zipcart-download-count').html(Drupal.settings.zipcart.cart.length);
        }
        clone.animate(animProps, 'slow', 'swing', animCallback);
      },
      error: function(req, textStatus, errorThrown) {
        alert(Drupal.t('Unable to add the file to your ZipCart.');
/*
        console.log(req);
        console.log(textStatus);
        console.log(errorThrown);
*/
        switch (textStatus) {
          case 'timeout' :
          case 'null' :
          case 'error' :
          case 'parsererror' :
          case 'notmodified' :
            break;
          default :
            break;
        }
        // probably not permitted - handle file access restriction here
      }
    });
  }

};

Drupal.behaviors.zipcart = Drupal.zipcart.init;