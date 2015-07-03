<?php
/**
Plugin Name: Popping Sidebars and Widgets Light
Plugin URI: http://OTWthemes.com
Description:  Create custom popping layouts with sidebars and widgets in just a few clicks. 
Author: OTWthemes.com
Version: 1.5

Author URI: http://themeforest.net/user/OTWthemes
*/

load_plugin_textdomain('otw_pswl',false,dirname(plugin_basename(__FILE__)) . '/languages/');

$otw_pswl_plugin_url = plugin_dir_url( __FILE__);

$otw_pswl_js_version = '1.2';
$otw_pswl_css_version = '1.3';

$otw_sbm_widget_settings = array( 
	'before_widget' => array( __( 'HTML to be placed before every widget.', 'otw_pswl' ), '<div id="%1$s" class="widget %2$s">' ),
	'after_widget' => array( __( 'HTML to be placed after every widget.', 'otw_pswl' ), '</div>' ),
	'before_title' =>array( __( 'HTML to be placed before every widget title.', 'otw_pswl' ), ' <h3 class="widgettitle">' ),
	'after_title' =>array(  __( 'HTML to be placed after every widget title.', 'otw_pswl' ), '</h3>' )
);

//include functons
require_once( plugin_dir_path( __FILE__ ).'/include/otw_pswl_functions.php' );
require_once( plugin_dir_path( __FILE__ ).'/include/otw_pswl_core.php' );

//components
$otw_pswl_grid_manager_component = false;
$otw_pswl_grid_manager_object = false;
$otw_pswl_shortcode_component = false;
$otw_pswl_form_component = false;
$otw_pswl_validator_component = false;
$otw_pswl_overlay_component = false;
$otw_pswl_overlay_object = false;

//load core component functions
@include_once( 'include/otw_components/otw_functions/otw_functions.php' );

if( !function_exists( 'otw_register_component' ) ){
	wp_die( 'Please include otw components' );
}

//register grid manager component
otw_register_component( 'otw_overlay_grid_manager', dirname( __FILE__ ).'/include/otw_components/otw_overlay_grid_manager_light/', $otw_pswl_plugin_url.'/include/otw_components/otw_overlay_grid_manager_light/' );

//register form component
otw_register_component( 'otw_form', dirname( __FILE__ ).'/include/otw_components/otw_form/', $otw_pswl_plugin_url.'/include/otw_components/otw_form/' );

//register validator component
otw_register_component( 'otw_validator', dirname( __FILE__ ).'/include/otw_components/otw_validator/', $otw_pswl_plugin_url.'/include/otw_components/otw_validator/' );

//register shortcode component
otw_register_component( 'otw_overlay_shortcode', dirname( __FILE__ ).'/include/otw_components/otw_overlay_shortcode/', $otw_pswl_plugin_url.'/include/otw_components/otw_overlay_shortcode/' );

//register overlay component
otw_register_component( 'otw_overlay', dirname( __FILE__ ).'/include/otw_components/otw_overlay_light/', $otw_pswl_plugin_url.'/include/otw_components/otw_overlay_light/' );

add_action('init', 'otw_pswl_init' );

if( is_admin() ){
	add_action( 'wp_ajax_otw_pswl_admin_settings', 'otw_pswl_admin_settings' );
}
