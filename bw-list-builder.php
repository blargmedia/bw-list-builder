<?php
/**
 * Plugin Name: BW List Builder
 * Plugin URI: http://blargmedia.ca
 * Description: Email List builder example project from Udemy Ultimate WP Plugin course
 * Version: 0.0.1
 * Author: Ben Wong // blargmedia.ca
 * Author URI: http://blargmedia.ca
 */

/* 1. hooks */

// register custome shortcodes on init action hook
add_action('init', 'bwlb_register_shortcodes');



/* 2. shortcodes */

// registers all custom shortcodes - can add more here later
function bwlb_register_shortcodes() {

  // sets bwlb_form against form_shortcode callback function
  add_shortcode('bwlb_form', 'bwlb_form_shortcode');

}

function bwlb_form_shortcode( $args, $content="" ) {  // wp auto passes in the args and contents

  // setup output variable - the form html that will be returned
  $output = '
    <div class="bwlb">
      <form id="bwlb_form" name="bwlb_form" class="bwlb-form" method="post">
        <p class="bwlb-input-container">
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


/* 3. filters */

/* 4. external scripts */

/* 5. actions */

/* 6. helpers */

/* 7. custom post types	*/

/* 8. admin pages */

/* 9. settings */





// eof
?>
