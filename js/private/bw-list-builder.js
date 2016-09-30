// workaround to allow admin menu items to stay open when clicking on the custom post types

// wait until the page and jQuery have loaded before running the code below
jQuery(document).ready(function($){

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
  $('.file_id',$import_form_1).bind('change', function(){

    alert('a csv file has been added successfully');

    // get and serialize the form data
    var form_1_data = $import_form_1.serialize();

  });

});
