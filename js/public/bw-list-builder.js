// ensure page loads jQuery before the rest of the code
jQuery(document).ready(function($){

  // setup location of wp ajax
  var wpajax_url = document.location.protocol + '//' + document.location.host  + '/wp_wecf/wp-admin/admin-ajax.php';

  // add the function call param
  var email_capture_url = wpajax_url + '?action=bwlb_save_subscription';

  $('form#bwlb_register_form').bind('submit', function(){    // hijack submission button

    // get the jquery form object
    $form = $(this);

    // get the form data and setup for ajax post
    var form_data = $form.serialize();

    //init ajax call
    $.ajax({
      'method':'post',
      'url':email_capture_url,
      'data':form_data,
      'dataType':'json',
      'cache':false,
      'success':function(data,textStatus) {
        if(data.status == 1) {
          // reset the form on successful submission
          $form[0].reset();
          // notify user
          alert(data.message);
        } else {

          // build error message
          var msg = data.message + '\n' + data.error + '\n';

          // loop over the errors
          $.each(data.errors, function(key,value) {
            // append the errors one line at a time
            msg += '\n';
            msg += '- '+ value;
          });
          // notify user
          alert(msg);
        }
      },
      'error': function(jqXHR, textStatus, errorThrown) {
        // ajax didn't work
      }
    });

    // stop the form from submitting normally
    return false;

  });

  // unsubscribe function
  var unsubscribe_url = wpajax_url + '?action=bwlb_unsubscribe';

  $(document).on('submit','form#bwlb_manage_subscriptions_form', function() {

    // jquery form object
    $form = $(this);

    // setup form data
    var form_data = $form.serialize();

    // submit form with ajax
    $.ajax({
      'method':'post',
      'url':unsubscribe_url,
      'data':form_data,
      'dataType':'json',
      'cache':false,
      'success': function( data, textStatus ) {
        if( data.status == 1 ) {
          $form.replaceWith(data.html); // update html with subscription list html
          alert(data.message);
        } else {
          var msg = data.message + '\n' + data.error + '\n';
          alert(msg);
        }
      },
      'error': function(jqXHR, textStatus, errorThrown) {
        // ajax didn't work
      }

    });

    // prevent form from submitting normally
    return false;
  });

});
