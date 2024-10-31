<?php
/*
Plugin Name: Real-Time Crowd
Plugin URI: http://www.realtimecrowd.net/
Description: Enables Real-Time Crowd tracking and generation on your WordPress site.
Version: 1.7
Author: RealTimeCrowd.net
Author URI: http://www.realtimecrowd.net/
License: GPL2
*/


// this is an include only WP file
if (!defined('ABSPATH')) 
{
  die;
}

if (!defined('RTC_PLUGIN_DIR')) {
  define('RTC_PLUGIN_DIR', plugin_dir_path(__FILE__));;
}

if (!class_exists('RTCPlugin')) 
{
	require_once RTC_PLUGIN_DIR . 'rtcplugin.php';
}

if (!class_exists('RealTimeCrowdWidget')) 
{
	require_once RTC_PLUGIN_DIR . 'rtcwidget.php';
}

//runs when plugin is installed
function rtc_install() 
{
	$rtcplugin = new RTCPlugin();
	$rtcplugin->rtcInstall();
}

//runs when plugin is uninstalled
function rtc_uninstall() 
{
	$rtcplugin = new RTCPlugin();
	$rtcplugin->rtcUninstall();
}

//Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
function rtc_admin_action_links( $links ) 
{
	$rtcplugin = new RTCPlugin();
	return $rtcplugin->rtcAdminActionLinks($links);
}

//adds menu option to the admin panel
function rtc_admin_left_menu() 
{
	$rtcplugin = new RTCPlugin();
	return $rtcplugin->rtcAdminLeftMenu();
}

//admin side plugin page
function rtc_admin_html_page() 
{
	$rtcplugin = new RTCPlugin();
	$rtcplugin->rtcAdminHtmlPage();
}

//generates code for footer
function footer_rtc_tracking_code() 
{
	$rtcplugin = new RTCPlugin();
	$rtcplugin->rtcFooterTrackingCode();
}

// Register the widget
function rtc_widget_init() {
	$displayFlag = get_option("rtc_display_flag");
	if ($displayFlag == "")
		$displayFlag = 0;
	$displayFlag = (int)$displayFlag;

		//$displayFlag=0; //nothing works, including the admin side
		//$displayFlag=1; //only script is included without showing popup or sidebar widget. it's good if admin wants to see what is going on without the users knowing it
		//$displayFlag=2; //popup is visible, sidebar widget not visible.
		//$displayFlag=3; //sidebar widget is visible, popup is not visible.
	if ($displayFlag==3)
	{
		register_widget('RealTimeCrowdWidget');
	}
}

// Puts code on Wordpress pages footer
add_action('wp_footer', 'footer_rtc_tracking_code');

// Runs when plugin is activated 
register_activation_hook(__FILE__, 'rtc_install');

// Runs on plugin deactivation
register_deactivation_hook(__FILE__, 'rtc_uninstall' );

//add menu on the left
add_action('admin_menu', 'rtc_admin_left_menu');

//add admin links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rtc_admin_action_links' ); 

add_action('widgets_init', 'rtc_widget_init');
?>