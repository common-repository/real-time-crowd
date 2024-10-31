<?php
// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}

if (!defined('RTC_PLUGIN_DIR')) {
  define('RTC_PLUGIN_DIR', plugin_dir_path(__FILE__));;
}

if (!class_exists('RTCHelper')) {	
	require_once RTC_PLUGIN_DIR . 'rtchelper.php';
}

class RealTimeCrowdWidget extends WP_Widget {

    // Widget construction
    function __construct() {
        $widget_ops = array('classname' => 'widget_text rtc-widget', 'description' => __('Display top viewed posts or products right now in real time', 'rtc_widget'));
        $control_ops = array('width' => 450);
        parent::__construct('RealTimeCrowdWidget', __('Real-Time Crowd', 'rtc_widget'), $widget_ops, $control_ops);
    }

    // Setup the widget output
    function widget( $args, $instance ) {

        if (!isset($args['widget_id'])) {
          $args['widget_id'] = null;
        }

        extract($args);

		$widgetNum = $this->number;
		$displayFlag = get_option("rtc_display_flag");
		$accountAlias = get_option("rtc_account_name");
		if ($displayFlag == "")
			$displayFlag = 0;
		$displayFlag = (int)$displayFlag;
		
		$helper = new RTCHelper();		
        $trackingCode = $helper->getRtcTrackingCode($accountAlias, $widgetNum, $displayFlag);	
		echo $trackingCode;
    }

    // Run on widget update
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        return $instance;
    }

    // Setup the widget admin form
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array(
        ));
    }	
}