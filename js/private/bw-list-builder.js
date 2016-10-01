// workaround to allow admin menu items to stay open when clicking on the custom post types

// wait until the page and jQuery have loaded before running the code below
jQuery(document).ready(function($){

  // wp ajax url
  var wpajax_url = document.location.protocol + '//' + document.location.host + '/wp_wecf/wp-admin/admin-ajax.php';

	// stop our admin menus from collapsing
	if( $('body[class*=" bwlb_"]').length || $('body[class*=" post-type-bwlb_"]').length ) {

		$bwlb_menu_li = $('#toplevel_page_bwlb_dashboard_admin_page');

		$bwlb_menu_li
		.removeClass('wp-not-current-submenu')
		.addClass('wp-has-current-submenu')
		.addClass('wp-menu-open');

		$('a:first',$bwlb_menu_li)
		.removeClass('wp-not-current-submenu')
		.addClass('wp-has-submenu')
		.addClass('wp-has-current-submenu')
		.addClass('wp-menu-open');

	}

  // wp-uploader
  // adds WP's builtin uploader to a specially formatted html div.wp-uploader
  $('.wp-uploader').each(function(){

    $uploader = $(this);

    // bind to click event
    $('.upload-btn',$uploader).click(function(e) {

      e.preventDefault();
      var file = wp.media({
        title: 'Upload',
        multiple: false  // set to true for multiple files at once
      }).open()
      .on('select',function(e) {
        // selected image from the media uploader
        var uploaded_file = file.state().get('selection').first();

        // convert selected image to JSON
        var file_url = uploaded_file.attributes.url;
        var file_id = uploaded_file.id;

        if ( $('.file-url',$uploader).attr('accept') !== undefined) {

          var filetype = $('.file-url',$uploader).attr('accept');

          if ( filetype !== uploaded_file.attributes.subtype) {
            $('.upload-text',$uploader).val('');
            alert('The file must be of type: ' + filetype);
          } else {
            $('.file-url',$uploader).val(file_url).trigger('change');
            $('.file-id',$uploader).val(file_id).trigger('change');

          }
        }
      });
    });
  });

  // store input form jquery objects
  $import_form_1 = $('#import_form_1','#import_subscribers');
  $import_form_2 = $('#import_form_2','#import_subscribers');

  // when form 1 is selcted:
  $('.file-id',$import_form_1).bind('change', function(){

    //alert('a csv file has been added successfully');

    // get and serialize the form data
    var form_1_data = $import_form_1.serialize();

    // form_1 action url
    var form_1_action_url = wpajax_url + '?action=bwlb_parse_import_csv';

    // send the file to php to process
    $.ajax({
      url: form_1_action_url,
      method: 'post',
      dataType: 'json',
      data: form_1_data,
      success: function(response) {

        if (response.status == 1) {
          $return_html = bwlb_get_form_2_html(response.data);

          // update the dynamic content section with the html
          $('.bwlb-dynamic-content',$import_form_2).html($return_html);

          $import_form_2.show();
        } else {

          // reset form 1
          $('.file-id',$import_form_1).val(0);
          $('.file-url',$import_form_1).val('');

          // hide form2
          $import_form_2.hide();
          alert(response.message);

        }
      }

    });

  });

  // validate form 2 for all change events and show/hide elements accordingly
  $(document).on('change', '#import_subscribers #import_form_2 .bwlb-input', function() {

    setTimeout(function() {

      if (bwlb_form_2_is_valid()) {
        $('.show-only-on-valid',$import_form_2).show();
      } else {
        $('.show-only-on-valid',$import_form_2).hide();
      }

    },100);

  });

  // toggle all subscribers
  $(document).on('click','#import_subscribers #import_form_2 .check-all', function() {

    var checked = $(this)[0].checked;

    if (checked) {
      $('[name="bwlb_import_rows[]"]:not(:checked)',$import_form_2).trigger('click');
    } else {
      $('[name="bwlb_import_rows[]"]:checked',$import_form_2).trigger('click');
    }

  });

  // ajax form handler for import subscribers form 2
  $(document).on('submit', '#import_subscribers #import_form_2', function(){

    var form_2_action_url = wpajax_url + '?action=bwlb_import_subscribers';

    var form_2_data = $import_form_2.serialize();

    $.ajax({
      url: form_2_action_url,
      method: 'post',
      dataType: 'json',
      data: form_2_data,
      success: function(response) {
        if (response.status == 1) {
          // success - reset input form
          $('.bwlb-dynamic-content').html('');
          $('.show-only-on-valid', $import_form_2).hide();
          $('.file-url',$import_form_1).val('');
          $('.file-id',$import_form_1).val(0);

          alert (response.message);
        } else {

          // build error message
          var msg = response.message + '\n' + response.error + '\n';

          // loop over the errors
          $.each(response.errors, function(key,value) {
            // append the errors one line at a time
            msg += '\n';
            msg += '- '+ value;
          });
          // notify user
          alert(msg);
        }
      }
    });

    // stop form from submitting normally
    return false;
  });

  // return html from import form 2
  function bwlb_get_form_2_html (subscribers) {

    // determine number of columns in subscriber data
    var columns = Object.keys(subscribers[0]).length;

    var return_html = '';

    // select html
    var select_fname = bwlb_get_selector('bwlb_fname_column',subscribers);
    var select_lname = bwlb_get_selector('bwlb_lname_column',subscribers);
    var select_email = bwlb_get_selector('bwlb_email_column',subscribers);

    // assignment html
    var assign_html = '' +
    '<p><label>First Name</label> &nbsp; ' + select_fname + '</p>' +
    '<p><label>Last Name</label> &nbsp; ' + select_lname + '</p>' +
    '<p><label>Email</label> &nbsp; ' + select_email + '</p>';

    // first row
    var row_1 = bwlb_get_form_table_row('Assign Data Column',assign_html);
    return_html += row_1;

    // data table
    var table = '<table class="wp-list-table fixed widefat striped"><thead>';
    var tr = '<tr>';
    var th = '<th scope="col" class="manage-column check-column">'+
      '<label><input type="checkbox" class="check-all"></label></th>';

    tr += th;

    var column_id = 0;

    $.each (subscribers[0], function(key, value) {
      column_id++;
      var th = '<th scope="col">' + key + '</th>';
      tr += th;

    });

    tr += '</tr>';

    table += tr + '</thead>' + '<tbody id="the-list">';

    var row_id = 0;

    // for each of the subscribers
    $.each(subscribers, function(index, subscriber) {

      row_id++;

      var tr = '<tr>';

      var th = '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-' + row_id +
        '" name="bwlb_import_rows[]" class="bwlb-input" value="' + row_id + '"/></th>';

      tr += th;

      var column_id = 0;

      // for each column of the subscriber data
      $.each(subscriber, function(key,value){
        column_id++;

        var field_name = 's_'+row_id+'_'+column_id;

        var td = '<td>' + value + '<input type="hidden" name="' + field_name +
          '" class="bwlb-input" value="' + value + '"></td>';

        tr += td;

      });

      tr += '</tr>';

      table += tr;

    });

    table += '</tbody></table>';

    var row_2 = bwlb_get_form_table_row('Select Subscribers', table, 'Select subscribers to import');

    return_html += row_2;

    return $(return_html); // return as jquery object
  }

  // return html select with subscriber fields as options
  function bwlb_get_selector(input_id, subscribers) {

    var select = '<select name="' + input_id + '" class="bwlb-input">';

    var column_id = 0;

    var option = '<option value="">-- Select One --</option>';

    select += option;

    $.each(subscribers[0], function(key,value) {

      column_id++;

      var option = '<option value="' + column_id + '">' + column_id + '. ' + key + '</option>';

      select += option;

    });

    select += '</select>';

    return select;
  }

  // return html tr formmated for wordpress admin forms
  function bwlb_get_form_table_row (label, input, description) {

    var html = '<tr>' +
      '<th scope="row"><label>' + label +'</label></th>' +
      '<td>' + input;

    if (description !== undefined) {
      html += '<p class="description">' + description + '</p>';
    }

    html += '</td></tr>'

    return html;
  }

  // validates import_form_2
  function bwlb_form_2_is_valid() {

    var is_valid = true;

    // no subscribers selected
    if ( $('[name="bwlb_import_rows[]"]:checked',$import_form_2).length == 0 )
      is_valid = false;

    // no fname column selected
    if ( $('[name="bwlb_fname_column"] option:selected',$import_form_2).val() == '' )
      is_valid = false;

    // no lname selected
    if ( $('[name="bwlb_lname_column"] option:selected',$import_form_2).val() == '' )
      is_valid = false;

    // no email selected
    if ( $('[name="bwlb_email_column"] option:selected',$import_form_2).val() == '' )
      is_valid = false;

    return is_valid;
  }

});
