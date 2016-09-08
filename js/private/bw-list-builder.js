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

});
