<?php
/**
 * Plugin Name: BW List Builder
 * Plugin URI: http://blargmedia.ca
 * Description: Email List builder example project from Udemy Ultimate WP Plugin course
 * Version: 0.0.1
 * Author: Ben Wong // blargmedia.ca
 * Author URI: http://blargmedia.ca
 */

/* 0. table of contents */
/*
    1. hooks
      1.1   custom shortcodes
      1.2   custom admin column headers - subscribers
      1.3   custom admin column data - subscribers
      1.4   titles for post types without titles
      1.5   custom admin column headers - lists
      1.6   custom admin column data - lists
      1.7   ajax actions for non logged in users
      1.8   ajax actions for logged in users with privs
      1.9   load external files - ajax and css
      1.10  filters required by including ACF within this plugin
      1.11  register custom menus
      1.12  load external scripts for admin
      1.13  register plugin options

    2. shortcodes
      2.1   bwlb_register_shortcodes()
      2.2   bwlb_form_shortcode()

    3. filters
      3.1   bwlb_subscriber_column_headers()
      3.2   bwlb_subscriber_column_data()
      3.2.1 bwlb_register_custom_admin_titles()
      3.2.2 bwlb_custom_admin_titles()
      3.3   bwlb_list_column_headers()
      3.4   bwlb_list_column_data()
      3.5   bwlb_admin_menus()

    4. external scripts
      4.1   include advanced-custom-fields
      4.2   bwlb_public_scripts()
      4.3   bwlb_admin_scripts()

    5. actions
      5.1   bwlb_save_subscription()
      5.2   bwlb_save_subscriber()
      5.3   bwlb_add_subscription()

    6. helpers
      6.1   bwlb_subscriber_has_subscription()
      6.2   bwlb_get_subscriptions()
      6.3   bwlb_get_subscriptions()
      6.4   bwlb_return_json()
      6.5   bwlb_get_acf_key()
      6.6   bwlb_get_subscriber_data()
      6.7   bwlb_validate_subscriber()
      6.8   bwlb_get_page_select()
      6.9   bwlb_get_default_options()
      6.10  bwlb_get_option()
      6.11  bwlb_get_current_options()

    7. custom post types
      7.1   include subscriber code generated by advanced-custom-fields
      7.2   include list code generated by custom post type ui

    8. admin pages
      8.1   bwlb_dashboard_admin_page()
      8.2   bwlb_import_admin_page()
      8.3   bwlb_options_admin_page()

    9. settings
      9.1   bwlb_register_options()

*/


/* 1. hooks */

// 1.1
// register custom shortcodes on init action hook
add_action('init', 'bwlb_register_shortcodes');

// 1.2
// register custom admin column headers
add_filter('manage_edit-bwlb_subscriber_columns', 'bwlb_subscriber_column_headers');

// 1.3
// register custom admin column data with priority 1 and indicate we need 2 params
add_filter('manage_bwlb_subscriber_posts_custom_column', 'bwlb_subscriber_column_data', 1, 2);

// 1.4
// register custom titles for post type data without titles
add_action('admin_head-edit.php', 'bwlb_register_custom_admin_titles');

// 1.5
// register custom admin column headers
add_filter('manage_edit-bwlb_list_columns', 'bwlb_list_column_headers');

// 1.6
// register custom admin column data with priority 1 and indicate we need 2 params
add_filter('manage_bwlb_list_posts_custom_column', 'bwlb_list_column_data', 1, 2);

// 1.7
// register ajax actions for regular website (not logged in) visitors
add_action('wp_ajax_nopriv_bwlb_save_subscription', 'bwlb_save_subscription');

// 1.8
// register ajax actions for logged-in users with privs
add_action('wp_ajax_bwlb_save_subscription','bwlb_save_subscription');

// 1.9
// load external files
add_action('wp_enqueue_scripts','bwlb_public_scripts');

// 1.10
// filters that ACF requires when including ACF as part of your own plugin
add_filter('acf/settings/path', 'bwlb_acf_settings_path');
add_filter('acf/sttings/dir', 'bwlb_acf_settings_dir');
add_filter('acf/settings/show_admin', 'bwlb_acf_show_admin');
if ( !defined('ACF_LITE')) define('ACF_LITE', true); // turns off the ACF plugin menu

// 1.11
// register the custom menus
add_action('admin_menu', 'bwlb_admin_menus');

// 1.12
// load external admin files
add_action('admin_enqueue_scripts', 'bwlb_admin_scripts');

// 1.13
// register plugin options
add_action('admin_init', 'bwlb_register_options');


/* 2. shortcodes */

// 2.1
// registers all custom shortcodes - can add more here later
function bwlb_register_shortcodes() {

  // sets bwlb_form against form_shortcode callback function
  add_shortcode('bwlb_form', 'bwlb_form_shortcode');

  // unsusbscribe form
  add_shortcode('bwlb_manage_subscriptions', 'bwlb_manage_subscriptions_shortcode');

}

// 2.2
// create the html output to be generated by the shortcode
function bwlb_form_shortcode( $args, $content="") {  // wp auto passes in the args and contents

  // get the list id from the args with sanity checking
  $list_id = 0;
  if ( isset($args['id']) ) $list_id = (int)$args['id'];

  // allow titles in the shortcode
  $title = '';
  if ( isset($args['title']) ) $title = (string) $args['title'];


  // setup output variable - the form html that will be returned
  $output = '
    <div class="bwlb">
      <form id="bwlb_form" name="bwlb_form" class="bwlb-form" method="post"
      action="/wp_wecf/wp-admin/admin-ajax.php?action=bwlb_save_subscription">

        <input type="hidden" name="bwlb_list" value="'. $list_id .'">';

        if (strlen($title)):

          $output .= '<h3 class="bwlb-title">'. $title .'</h3>';

        endif;

        $output .= '<p class="bwlb-input-container">
          <label>Your Name</label><br/>
          <input type="text" name="bwlb_fname" placeholder="first name" />
          <input type="text" name="bwlb_lname" placeholder="last name" />
        </p>
        <p class="bwlb-input-container">
          <label>Your Email</label><br/>
          <input type="email" name="bwlb_email" placeholder="email" />
        </p>';

        // validate any incoming content and include in the output if needed
        if ( strlen($content) ):
          $output .= '<div clas="bwlb-content">';
          $output .= wpautop($content); // wrap the content with wp paragraphs
          $output .= '</div>';
        endif;

        // close out the form html
        $output .= '<p class="bwlb-input-container">
          <input type="submit" name="bwlb_submit" value="sign up" />
        </p>
      </form>
    </div>
  ';

  return $output;

}


// 2.3
// shortcode to show a form for managing user list subscriptions
// e.g. [bwlb_manage_subscriptions]
function bwlb_manage_subscriptions_shortcode ( $args, $content="" ) {

  // start of the return string
  $output = '<div class="bwlb bwlb-manage-subscriptions">';

  try {

    // get the (sanitized) email from the url
    $email = ( isset($_GET['email']) ) ? esc_attr($_GET['email']) : '';

    // subscriber id from email
    $subscriber_id = bwlb_get_subscriber_id($email);

    // data from id
    $subscriber_data = bwlb_get_subscriber_data($subscriber_id);

    // get the subscriptions html if valid
    if ($subscriber_id) :

      $output .= bwlb_get_manage_subscriptions_html($subscriber_id); // helper

    else:

      $output .= '<p>This link is invalid</p>';

    endif;

  } catch (Exception $e) {

  }

  $output .= '</div>';

  return $output;

}


/* 3. filters */

// 3.1
// filters the display of the subscriber headings in the admin custom post list view
// wp passes in an array of the columns
function bwlb_subscriber_column_headers ( $columns ) {

  // create custom column header data
  // new associative array to override the incoming param
  $columns = array (
    'cb'    => '<input type="checkbox" />',  // checkbox in the subscriber display
    'title' => __('Subscriber Name'), // __() to allow for translatable text strings
    'email' => __('Email Address')
  );

  return $columns;
}

// 3.2
// filters the display of the subscriber data in the admin custom post list view
// wp passes in the column name and the id of the post
function bwlb_subscriber_column_data ( $column, $post_id ) {

  // init return text
  $output = '';

  switch ( $column ) {

    case 'name':
      // get the custom name data
      $fname = get_field('bwlb_fname', $post_id);
      $lname = get_field('bwlb_lname', $post_id);
      $output .= $fname . ' ' . $lname;
      break;
    case 'email':
      // get the custom email data
      $email = get_field('bwlb_email', $post_id);
      $output .= $email;
      break;
  }

  echo $output; // display the retrieved data
}

// 3.2.1
// register special custom admin title columns
function bwlb_register_custom_admin_titles() {
  add_filter ('the_title', 'bwlb_custom_admin_titles', 99, 2);
}

// 3.2.2
// deal with custom admin title column data for post types without titles
// wp passes in the title and post id
function bwlb_custom_admin_titles( $title, $post_id ) {

  global $post; // pulls in the post variable from the global scope

  $output = $title;

  if ( isset($post->post_type) ):   // see if the post has a valid post type
    switch ( $post->post_type ) {
      case 'bwlb_subscriber':  // check that we're using the subscriber type
        $fname = get_field('bwlb_fname', $post_id);
        $lname = get_field('bwlb_lname', $post_id);
        $output = $fname . ' ' . $lname; // setup the title to be the full name
    }
  endif;

  return $output;

}

// 3.3
// filters the display of the list headings in the admin custom post list view
// wp passes in an array of the columns
function bwlb_list_column_headers ( $columns ) {

  // create custom column header data
  // new associative array to override the incoming param
  $columns = array (
    'cb'    => '<input type="checkbox" />',  // checkbox in the subscriber display
    'title' => __('List Name'), // __() to allow for translatable text strings
    'shortcode' => __('Shortcode')
  );

  return $columns;
}

// 3.4
// filters the display of the list data in the admin custom post list view
// wp passes in the column name and the id of the post
function bwlb_list_column_data ( $column, $post_id ) {

  // init return text
  $output = '';

  switch ( $column ) {

    case 'shortcode':
      $output .= '[bwlb_form id="' . $post_id . '"]';
      break;
  }

  echo $output; // display the retrieved data
}

// 3.5
// create custom admin menu for the plugin
// references the menu pages created in section 8
function bwlb_admin_menus() {

  // main menu

  $top_menu_item = 'bwlb_dashboard_admin_page'; // function in section 8

  // wp function to create the menu
  // see also dashicons cheat sheet for wordpress by Caleb Serrno
  add_menu_page( '', 'BW List Builder', 'manage_options', 'bwlb_dashboard_admin_page', 'bwlb_dashboard_admin_page', 'dashicons-email-alt');


  // submenu items - uses wp function for submenus

  // dashboard itself
  add_submenu_page($top_menu_item, '', 'Dashboard', 'manage_options', $top_menu_item, $top_menu_item);

  // email lists - edit url generated by wp
  add_submenu_page($top_menu_item, '', 'Email Lists', 'manage_options', 'edit.php?post_type=bwlb_list');

  // subscibers - edit url from wp itself
  add_submenu_page($top_menu_item, '', 'Subscribers', 'manage_options', 'edit.php?post_type=bwlb_subscriber');

  // import subscribers
  add_submenu_page($top_menu_item, '', 'Import Subscribers', 'manage_options', 'bwlb_import_admin_page', 'bwlb_import_admin_page');

  // plugin options
  add_submenu_page($top_menu_item, '', 'Plugin Options', 'manage_options', 'bwlb_options_admin_page', 'bwlb_options_admin_page');


}

/* 4. external scripts */

// 4.1
// include ACF plugin - copy the contents of the ACF plugin into lib subdir
include_once( plugin_dir_path(__FILE__) .'lib/advanced-custom-fields/acf.php');

// 4.2
// load external files into public website
function bwlb_public_scripts() {

  // register scripts with WP
  // params...
  // unique name for WP
  // builtin WP function plugins_url
  // required scripts that WP needs to load prior to ours
  wp_register_script('bw-list-builder-js-public',
    plugins_url('/js/public/bw-list-builder.js', __FILE__), array('jquery'), '', true);

  wp_register_style('bw-list-builder-css-public',
    plugins_url('/css/public/bw-list-builder.css', __FILE__));

  // add to queue of scripts that get loaded into every page
  wp_enqueue_script('bw-list-builder-js-public');
  wp_enqueue_style('bw-list-builder-css-public');

}

// 4.3
// load external files for Admin
function bwlb_admin_scripts() {

  // register js with wp
  wp_register_script('bw-list-builder-js-private', plugins_url('/js/private/bw-list-builder.js', __FILE__), array('jquery'), '', true );

  // add to queue of scripts
  wp_enqueue_script('bw-list-builder-js-private');

}


/* 5. actions */

// 5.1
// save subscription data to an existing or new subscriber
function bwlb_save_subscription() {

  // init result data to be the negative outcome
  $result = array(
    'status' => 0,
    'message' => 'Subscription was not saved.',
    'error' => '',
    'errors' => array()
  );

  // php try/catch statement
  /*
  try {
    // code
  } catch (Exception $e) {
    // do something with $e
  }
  */

  try {

    // get the list id from the form post
    $list_id = (int) $_POST['bwlb_list'];

    // prepare the subscriber data from the post
    // esc_attr - wp function to clean input data and ensure it is safe
    $subscriber_data = array(
      'fname'=> esc_attr( $_POST['bwlb_fname'] ),
      'lname'=> esc_attr( $_POST['bwlb_lname'] ),
      'email'=> esc_attr( $_POST['bwlb_email'] )
    );

    // error storage
    $errors = array();

    // form validation
    if ( !strlen( $subscriber_data['fname'] ) ) $errors['fname'] = 'First name is required';
    if ( !strlen( $subscriber_data['email'] ) ) $errors['email'] = 'Email address is required';
    if ( strlen ( $subscriber_data['email'] ) && !is_email( $subscriber_data['email'] ) ) $errors['email'] = 'Email must be valid';

    // check for errors
    if ( count($errors) ):
      $result['error'] = 'Some fields are still required.';
      $result['errors'] = $errors;

    else: // otherwise proceed

      // attempt to create/save subscriber
      $subscriber_id = bwlb_save_subscriber( $subscriber_data );

      // if saved subscriber id will be non zero
      if ( $subscriber_id ):

        // if already subscribed - create and use a helper function
        if (bwlb_subscriber_has_subscription( $subscriber_id, $list_id )):

          // get the list from the form post
          $list = get_post( $list_id );  // wp builtin function to get a post

          // setup the error message
          $result ['error'] = esc_attr( $subscriber_data['email'] . ' is already subscribef to ' . $list->post_title . '.');

        else:

          // save the subscription
          $subscription_saved = bwlb_add_subscription ( $subscriber_id, $list_id );

          // check success (non zero)
          if ( $subscription_saved ):
            $result['status'] = 1;
            $result['message'] = 'Subscription saved';
          else:

            $result['error'] = 'Unable to save subscription.';

          endif;

        endif;
      endif;
    endif;

  } catch ( Exception $e ) {

    // php error - add to error message
    //$result['message'] = 'Caught exception: ' . $e->getMessage();
  }

  // return as json string to be used in js / ajax
  bwlb_return_json($result);
}

// 5.2
// creates a new subscriber or updates an existing one
function bwlb_save_subscriber ( $subscriber_data ) {

  // init subscriber
  $subscriber_id = 0;

  try {

    $subscriber_id = bwlb_get_subscriber_id( $subscriber_data['email'] );

    // if not already a subscriber, add to subscribers
    if ( !$subscriber_id ):

      $subscriber_id = wp_insert_post ( // default wp function to add posts to db
        array(
          'post_type' => 'bwlb_subscriber',
          'post_title' => $subscriber_data['fname'] . ' ' . $subscriber_data['lname'],
          'post_status' => 'publish'
        ),
        true
      );
    endif;

    // add or update the meta data with wp function update_field
    update_field(bwlb_get_acf_key('bwlb_fname'), $subscriber_data['fname'], $subscriber_id);
    update_field(bwlb_get_acf_key('bwlb_lname'), $subscriber_data['lname'], $subscriber_id);
    update_field(bwlb_get_acf_key('bwlb_email'), $subscriber_data['email'], $subscriber_id);

  } catch ( Exception $e ) {

    // php error

  }

  // cleanup wp post data
  wp_reset_query();

  return $subscriber_id;

}

// 5.3
// adds list to subscriber's subscriptions
function bwlb_add_subscription ( $subscriber_id, $list_id ) {

  // init
  $subscription_saved = false;

  // subscriber is not subscribed to the passed list
  if ( !bwlb_subscriber_has_subscription($subscriber_id, $list_id) ):

    // get the subs and append the new list id
    $subscriptions = bwlb_get_subscriptions( $subscriber_id );
    $subscriptions[]=$list_id; // same as array_push( $subscriptions, $list_id );

    // update the subs
    update_field( bwlb_get_acf_key('bwlb_subscriptions'), $subscriptions, $subscriber_id );

    $subscription_saved = true;

  endif;

  return $subscription_saved;
}


/* 6. helpers */

// 6.1
// check a subscriber's subscriptions to see if the passed list is already there
function bwlb_subscriber_has_subscription ( $subscriber_id, $list_id ) {

  // init as negative outcome
  $has_subscription = false;

  // use wp function to get the subscriber 'object' (the custom post type)
  $subscriber = get_post($subscriber_id);

  // check the subscriptions
  $subscriptions = bwlb_get_subscriptions($subscriber_id);

  if ( in_array($list_id, $subscriptions) ):
    // subscriber is already subscribed
    $has_subscription = true;
  endif;

  return $has_subscription;
}

// 6.2
// get a subscriber id from their email address
function bwlb_get_subscriber_id($email) {

  // init
  $subscriber_id = 0;

  try {

    // check subscriber existence
    $subscriber_query = new WP_Query(
      array(
        'post_type'=> 'bwlb_subscriber',
        'posts_per_page' => 1,
        'meta_key' => 'bwlb_email',
        'meta_query' => array(
          array(
            'key' => 'bwlb_email',
            'value' => $email,
            'compare' => '='
          ),
        ),
      )
    );

    if ($subscriber_query->have_posts()):
      $subscriber_query->the_post();
      $subscriber_id = get_the_ID();  // from the_post
    endif;

  } catch (Exception $e) {
    // error
  }

  // reset wp post object - clear the_post object
  wp_reset_query();

  return (int) $subscriber_id;

}

// 6.3
// get an array of list_ids
function bwlb_get_subscriptions ( $subscriber_id ) {

  // init
  $subscriptions = array();

  $lists = get_field( bwlb_get_acf_key('bwlb_subscriptions'), $subscriber_id );

  if ( $lists ):

    // check array-ness and 1 or more items
    if ( is_array($lists) && count($lists) ):

      // build up subscriptions as an array of lists
      foreach ($lists as &$list):
        $subscriptions[]= (int) $list->ID;
      endforeach;

    elseif ( is_numeric($lists) ):
      $subscriptions[]=$lists;
    endif;

  endif;

  return (array)$subscriptions;
}

// 6.4
// convert php array into json
function bwlb_return_json ( $php_array ) {

  // builtin php function to do the encoding
  $json_result = json_encode( $php_array);

  // return and kill the php process
  die($json_result);

  // stop all other php processing
  exit;

}

// 6.5
// switches out custom field names for the ACT field key names
function bwlb_get_acf_key( $field_name ) {

  $field_key = $field_name;

  switch ( $field_name ) {

    case 'bwlb_fname':
      $field_key = 'field_57bf831d4e1d0';
      break;
    case 'bwlb_lname':
      $field_key = 'field_57bf83344e1d1';
      break;
    case 'bwlb_email':
      $field_key = 'field_57bf83504e1d2';
      break;
    case 'bwlb_subscriptions':
      $field_key = 'field_57bf83674e1d3';
      break;

  }

  return $field_key;

}

// 6.6
// return an array of subscriber data, including the subscriptions
function bwlb_get_subscriber_data ( $subscriber_id) {
  // init
  $subscriber_data = array();

  // get the subscriber object (custom post type)
  $subscriber = get_post($subscriber_id);

    // validate and build array
    if ( isset($subscriber->post_type) && $subscriber->post_type == 'bwlb_subscriber' ):

      $fname = get_field(bwlb_get_acf_key('bwlb_fname'), $subscriber_id);
      $lname = get_field(bwlb_get_acf_key('bwlb_lname'), $subscriber_id);

      $subscriber_data = array(
        'name' => $fname . ' ' . $lname,
        'fname' => $fname,
        'lname' => $lname,
        'email' => get_field(bwlb_get_acf_key('bwlb_email'), $subscriber_id),
        'subscriptions' => bwlb_get_subscriptions($subscriber_id)
      );

    endif;

  return $subscriber_data;
}

// 6.7
// ensure subscriber has a post type and it's the correct one
function bwlb_validate_subscriber($subscriber) {

  $valid_subscriber = false;

  if ( isset($subscriber->post_type) && $subscriber->post_type == 'bwlb_subscriber'):
    $valid_subscriber = true;
  endif;

  return $valid_subscriber;

}

// 6.8
// return the html for a page selector
function bwlb_get_page_select( $input_name="bwlb_page", $input_id="", $parent=-1, $value_field="id", $selected_value="" ) {

  // get the WP pages via a wp function and passing in an assoc array
  $pages = get_pages( array(
    'sort_order' => 'asc',
    'sort_column' => 'post_title',
    'post_type' => 'page',
    'parent' => $parent,
    'status' => array('draft','publish')
    )
  );

  // setup the select html
  $select = '<select name="' . $input_name . '" ';

  // if we have an input id
  if ( strlen ($input_id) ):

    // add it to the selector
    $select .= 'id="' . $input_id . '" ';

  endif;

  // first option in selector
  $select .= '><option value="">-- Select One --</option>';

  // iterate over the pages
  foreach ($pages as &$page):

    $value = $page->ID; // use page id by default

    // find the page attribute

    switch ($value_field) {

      case 'slug':
        $value = $page->post_name;
        break;
      case 'url':
        $value = get_page_link($page->ID);
        break;
      default:
        $value = $page->ID;

    }

    // see what's selected
    $selected = '';
    if ( $selected_value == $value ):
      $selected = ' selected="selected" ';
    endif;

    // build html for the option
    $option = '<option value="' . $value . '" ' . $selected . '>';
    $option .= $page->post_title;
    $option .= '</option>';

    $select .= $option;

  endforeach;

  // close the select
  $select .= '</select>';

  return $select;

}


// 6.9
// return default options as an associative array
function bwlb_get_default_options() {

  $defaults = array();

  try {

    // front page
    $front_page_id = get_option('page_on_front'); // builtin option in WP

    // email footer
    $default_email_footer = '
      <p>
        Regards, <br/>
        The ' . get_bloginfo('name') . ' Team<br/>
        <a href="' . get_bloginfo('url') . '">' . get_bloginfo('url') . '</a>
      </p>
    ';

    $defaults = array(
      'bwlb_manage_subscription_page_id' => $front_page_id,
      'bwlb_confirmation_page_id' => $front_page_id,
      'bwlb_reward_page_id' => $front_page_id,
      'bwlb_default_email_footer' => $default_email_footer,
      'bwlb_download_limit' => 3
    );

  } catch (Exception $e) {

  }

  return $defaults;

}


// 6.10
// return requested page option value or default
function bwlb_get_option( $option_name ) {

  // init
  $option_value = '';

  try {

    $defaults = bwlb_get_default_options();

    switch ( $option_name ) {
      case 'bwlb_manage_subscription_page_id':
        $option_value = (get_option('bwlb_manage_subscription_page_id')) ?
          get_option('bwlb_manage_subscription_page_id') : $defaults['bwlb_manage_subscription_page_id'];
        break;
      case 'bwlb_confirmation_page_id':
        $option_value = (get_option('bwlb_confirmation_page_id')) ?
          get_option('bwlb_confirmation_page_id') : $defaults['bwlb_confirmation_page_id'];
        break;
      case 'bwlb_reward_page_id':
        $option_value = (get_option('bwlb_reward_page_id')) ?
          get_option('bwlb_reward_page_id') : $defaults['bwlb_reward_page_id'];
        break;
      case 'bwlb_default_email_footer':
        $option_value = (get_option('bwlb_default_email_footer')) ?
          get_option('bwlb_default_email_footer') : $defaults['bwlb_default_email_footer'];
        break;
      case 'bwlb_download_limit':
        $option_value = (get_option('bwlb_download_limit')) ?
          (int) get_option('bwlb_download_limit') : $defaults['bwlb_download_limit'];
        break;
    }

  } catch (Exception $e) {

  }

  return $option_value;

}


// 6.11
// gets current options and returns as an associative array
function bwlb_get_current_options() {

  $current_options = array();

  try {

    // build the array
    $current_options = array(
      'bwlb_manage_subscription_page_id' => bwlb_get_option('bwlb_manage_subscription_page_id'),
      'bwlb_confirmation_page_id' => bwlb_get_option('bwlb_confirmation_page_id'),
      'bwlb_reward_page_id' => bwlb_get_option('bwlb_reward_page_id'),
      'bwlb_default_email_footer' => bwlb_get_option('bwlb_default_email_footer'),
      'bwlb_download_limit' => bwlb_get_option('bwlb_download_limit')

    );

  } catch (Exception $e) {

  }

  return $current_options;
}

// 6.12
//
function bwlb_get_manage_subscriptions_html ($subscriber_id) {

  $output = '';

  try {

    // lists from id
    $lists = bwlb_get_subscriptions ($subscriber_id);

    // data from id
    $subscriber_data = bwlb_get_subscriber_data($subscriber_id);

    // page title
    $title = $subscriber_data['fname'] . '\'s Subscriptions';

    // form html
    $output .= '
      <form id="bwlb_manage_subsriptions_form" class="bwlb-form" method="post"
      action="wp_wecf/wp-admin/admin-ajax.php?action=bwlb_unsubscribe">

        <input type="hidden" name="subscriber_id" value="' . $subscriber_id . '">

        <h3 class="bwlb_title">' . $title . '</h3>';

        if ( !count($lists) ):

          $output .= '<p>No active subscriptions found.</p>';

        else:

          $output .= '
            <table>
              <tbody>';

              foreach ( $lists as &$list_id ):

                $list_object = get_post($list_id);

                $output .= '
                <tr>
                  <td>
                  '.
                    $list_object->post_title
                  .'
                  </td>
                  <td>
                    <label>
                      <input type="checkbox" name="list_ids[]" value="'. $list_object->ID .'"/> Unsubscribe
                    </label>
                  </td>
                </tr>';

              endforeach;

              $output .= '
              </tbody>
            </table>

            <p><input type="submit" value="Save Changes" /></p>';

        endif;

    $output .= '
      </form>
    ';

  } catch (Exception $e) {

  }

  return $output;
}

/* 7. custom post types	*/

// 7.1
// include the subscriber code generated from advanced custom fields
include_once(plugin_dir_path(__FILE__) . 'cpt/bwlb_subscriber.php');

// 7.2
// include the list code generated by custom post types ui
include_once(plugin_dir_path(__FILE__) . 'cpt/bwlb_list.php');


/* 8. admin pages */
// build our own admin pages for the wordpress dashboard

// 8.1
// main list builder admin page
function bwlb_dashboard_admin_page() {

  $output = '
    <div class="wrap">

      <h2>BW List Builder</h2>
      <p>
      Plugin example from the Udemy course, Ultimate Wordpress Plugin Development.
      Email list building plugin - ibncludes subscriber management, list management, import/export.
      </p>

    </div>
  ';

  echo $output;

}

// 8.2
// admin page for importing subscribers
function bwlb_import_admin_page() {

  $output = '
    <div class="wrap">

      <h2>Import Subscribers</h2>

      <p>
      Page Description here...
      </p>

    </div>

  ';

  echo $output;

}

// 8.3
// admin page for plugin options
function bwlb_options_admin_page() {

  $options = bwlb_get_current_options();

  //form-table is a WP class - used to maintain consistence with other WP form tables
  echo ('<div class="wrap">

    <h2>BW List Builder Options</h2>

    <form action="options.php" method="post">');

      // use WP's builtin form security features

      // output unique nonce for the options
      settings_fields('bwlb_plugin_options');

      // generates unique hidden field for the form handling url
      @do_settings_fields('bwlb_plugin_options');

      echo('<table class="form-table">
        <tbody>
          <tr>
            <th scope="row">
              <label for="bwlb_manage_subscription_page_id">Manage Subscriptions Page</label>
            </th>
            <td>
              ' .
              // to return the html selector with the pages in the options
              bwlb_get_page_select('bwlb_manage_subscription_page_id', 'bwlb_manage_subscription_page_id', 0, 'id', $options['bwlb_manage_subscription_page_id'])
              . '
              <p class="description" id="bwlb_manage_subscription_page_id-description">
                This is the page where the list builder will send subscribers to manage their subscriptions.<br/>
                Note: In order to work, the page you select must contain the shortcode
                <strong>[bwlb_manage_subscriptions].</strong>
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="bwlb_confirmation_page_id">Opt-in Confirmation Page</label>
            </th>
            <td>
              ' .
              // to return the html selector with the pages in the options
              bwlb_get_page_select('bwlb_confirmation_page_id', 'bwlb_confirmation_page_id', 0, 'id', $options['bwlb_confirmation_page_id'])
              . '
              <p class="description" id="bwlb_confirmation_page_id-description">
                This is the page where the list builder will send subscribers to confirm their subscriptions.<br/>
                Note: In order to work, the page you select must contain the shortcode
                <strong>[bwlb_confirm_subscription].</strong>
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="bwlb_reward_page_id">Download Reward Page</label>
            </th>
            <td>
              ' .
              // to return the html selector with the pages in the options
              bwlb_get_page_select('bwlb_reward_page_id', 'bwlb_reward_page_id', 0, 'id', $options['bwlb_reward_page_id'])
              . '
              <p class="description" id="bwlb_reward_page_id-description">
                This is the page where the list builder will send subscribers to retrieve their download rewards.<br/>
                Note: In order to work, the page you select must contain the shortcode
                <strong>[bwlb_download_reward].</strong>
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="bwlb_default_email_footer">Email Footer</label>
            </th>
              <td>');

                // end first echo as wp_editor has issues with being stored in a string
                wp_editor($options['bwlb_default_email_footer'],'bwlb_default_email_footer', array('textarea_rows'=>8)); // provides a WP edit box

                //start a new echo..
                echo('
                <p class="description" id="bwlb_default_email_footer-description">
                  The default text that appears at the end of emails generated by this plugin.
                </p>
              </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="bwlb_download_limit">Reward Download Limit</label>
            </th>
            <td>
              <input type="number" name="bwlb_download_limit" value="'. $options['bwlb_download_limit'] .'" class="" />
              <p class="description" id="bwlb_download_limit-description">
                The amount of downloads a reward link will allow before expiring.
              </p>
            </td>
          </tr>

        </tbody>

      </table>');

      // replace html submit with WP's submit
      @submit_button();

    echo('</form>

  </div>
  ');
}



/* 9. settings */

// 9.1
// register our plugin's options with WP - called from action hook
function bwlb_register_options() {

  register_setting('bwlb_plugin_options', 'bwlb_manage_subscription_page_id');
  register_setting('bwlb_plugin_options', 'bwlb_confirmation_page_id');
  register_setting('bwlb_plugin_options', 'bwlb_reward_page_id');
  register_setting('bwlb_plugin_options', 'bwlb_default_email_footer');
  register_setting('bwlb_plugin_options', 'bwlb_download_limit');

}



// eof
?>
