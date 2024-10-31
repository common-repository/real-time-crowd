<?php

if (!defined('ABSPATH')) 
{
  die;
}

if (!defined('RTC_PLUGIN_DIR')) {
  define('RTC_PLUGIN_DIR', plugin_dir_path(__FILE__));;
}

if (!class_exists('RTCHelper')) 
{
	require_once RTC_PLUGIN_DIR . 'rtchelper.php';
}

class RTCPlugin {
	public function rtcInstall() 
	{
		//Creates new database field
		add_option("rtc_account_name", '', '', 'yes');
		add_option("rtc_display_flag", '', '', 'yes');
	}

	public function rtcUninstall() 
	{
		//Deletes the database field
		delete_option('rtc_account_name');
		delete_option('rtc_display_flag');
	}
	
	public function rtcAdminActionLinks( $links ) 
	{
	   // add Settings link
	   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=real-time-crowd') .'">Settings</a>';
	   return $links;
	}
	
	//adds menu option to the admin panel
	public function rtcAdminLeftMenu() 
	{
		add_menu_page('Real-Time Crowd plugin', 'Real-Time Crowd', 'manage_options', 'real-time-crowd', 'rtc_admin_html_page');
	}
	
	public function rtcAdminHtmlPage() 
	{
	?>
		<div class="wrap">
			<div id="icon-plugins" class="icon32"></div>
			<h2>Real-Time Crowd</h2>
			<form method="POST" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<?php if( isset($_GET['settings-updated']) ) { ?>
					<div id="message" class="updated fade">
						<p><strong><?php _e('Settings saved.') ?></strong></p>
					</div>
				<?php } ?>
				<p style="width: 80%;">
					<h3>How to install</h3>
					Step 1 : Register with <b><a target="_blank" href="http://www.realtimecrowd.net/">RealTimeCrowd.net</a></b>
					<br/>
					Step 2: Input your RealTimeCrowd <b>account alias</b> below. <a target="_blank" href="http://www.realtimecrowd.net/Partner/Account">See a list of your accounts</a>
					<br/>
					<!--<span style="color:#B50C0C;">Please Note: Real-Time Crowd works only with "Pretty Permalinks". It does not work with ugly permalinks based on query strings (like those http://example.com/?p=N). <a href="<?php echo get_admin_url(null, 'options-permalink.php')?>">Permalink settings</a></span>-->
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="rtc_account_name">Account alias</label>
						</th>
						<td>
							<input name="rtc_account_name" value="<?php echo get_option('rtc_account_name'); ?>" class="regular-text" />
							<span class="description">(ex: superblogger)</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="rtc_display_flag">Display type</label>
						</th>
						<td>
							<select name="rtc_display_flag">
								<option value="0" <?php selected(get_option( 'rtc_display_flag' ), 0 ); ?>>Disabled</option>
								<option value="1" <?php selected(get_option( 'rtc_display_flag' ), 1 ); ?>>Admin view only. Visitors will not see anything</option>
								<option value="2" <?php selected(get_option( 'rtc_display_flag' ), 2 ); ?>>Page bottom popup + Admin view</option>
								<option value="3" <?php selected(get_option( 'rtc_display_flag' ), 3 ); ?>>Sidebar widget + Admin view</option>
							</select>
							<span class="description"></span>
						</td>
					</tr>
				</table>   
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="rtc_account_name,rtc_display_flag" />
				<p class="submit">
					<input class="button-primary" type="submit" name="Save" value="<?php _e('Save'); ?>" />
				</p>
				<p style="width: 80%;">
					<br/>
					Send us <a target="_blank" href="http://www.realtimecrowd.net/Contact/">Feedback</a>
				</p>
			</form>
			<?php 
			$accountAlias = get_option("rtc_account_name");
			$displayFlag = get_option("rtc_display_flag");
			if ($displayFlag == "")
				$displayFlag = 0;
			$displayFlag = (int)$displayFlag;

			if (!empty($accountAlias) && $displayFlag>0)
			{
			?>
				<div>
					<h4>Real-Time data for account: <?php echo $accountAlias ?></h4>
					<iframe src="http://rtc.realtimecrowd.net/viewer/v1-1/<?php echo $accountAlias ?>/" style="border:none;width:100%;height:580px;"></iframe>
				</div>
			<?php
			}
			else
			{
			?>
				<div>
					<h4>Save your "Account alias" and enable "Display type" to display Real-Time data from your website</h4>
				</div>
			<?php
			}
			?>  	
		</div>
	<?php
	}
	
	public function rtcFooterTrackingCode() 
	{
		$accountAlias = get_option("rtc_account_name");
		$displayFlag = get_option("rtc_display_flag");
		if ($displayFlag == "")
			$displayFlag = 0;
		$displayFlag = (int)$displayFlag;

		//$displayFlag=0; //nothing works, including the admin side
		//$displayFlag=1; //only script is included without showing popup or sidebar widget. it's good if admin wants to see what is going on without the users knowing it
		//$displayFlag=2; //popup is visible, sidebar widget not visible.
		//$displayFlag=3; //sidebar widget is visible, popup is not visible.

		if (!is_admin() && ($displayFlag == 1 || $displayFlag == 2))
		{
			$helper = new RTCHelper();		
			$trackingCode = $helper->getRtcTrackingCode($accountAlias, 0, $displayFlag);
			echo $trackingCode;
		}
	}
}
?>