<?php
class RTCHelper {
	static $version = 1.0;

	static function get_plugin_version() {
		return $version;
	}

	public function getRtcTrackingCode($accountAlias, $widgetNum, $displayFlag)
	{
		//$displayFlag=0; //nothing works, including the admin side
		//$displayFlag=1; //only script is included without showing popup or sidebar widget. it's good if admin wants to see what is going on without the users knowing it
		//$displayFlag=2; //popup is visible, sidebar widget not visible.
		//$displayFlag=3; //sidebar widget is visible, popup is not visible.
		
		$ret = "";
		if (empty($accountAlias))
		{
			return $ret;
		}
		
		if (empty($displayFlag))
			$displayFlag = 0;
		
		$displayFlag = (int)$displayFlag;			
		
		$pageTitle = "";
		$pageImageUrl = "";
		$postId = $this->rtcGetTheID();
		
		if (empty($postId))
		{
			$postId = 0;
		}

		//if woocommerce
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || class_exists('Woocommerce'))
		{
			//WooCommerce is active
			if ($postId > 0)
			{
				//if WooCommerce is active
				if (is_product_category())
				{
					$pageTitle = 'category';
					global $wp_query;
					$cat = $wp_query->get_queried_object();
					if ($cat)
					{
						$pageTitle = $cat->name;
					}
					$pageImageUrl = $this->getCategoryImageUrl($postId, $cat);
				}
				else if (is_product())
				{
					global $product;
					if ($product)
					{
						$pageTitle = html_entity_decode(get_the_title($postId),ENT_QUOTES,'UTF-8');
					}
					$pageImageUrl = $this->getProductImageUrl($postId, $product);
				}
			}
		}
			
		if (empty($pageTitle))
		{
			//if not yet set, maybe woocommerce doesn't exist or it is just a page with regular post
			if ($postId > 0)
			{
				$pageTitle = html_entity_decode(get_the_title($postId),ENT_QUOTES,'UTF-8');
				if (has_post_thumbnail($postId))
				{
					$images = wp_get_attachment_image_src(get_post_thumbnail_id($postId), array(100, 100));
					if (count($images)>0)
					{
						$pageImageUrl = $images[0];
					}
				}
			}
			else if (is_front_page() || is_home())
			{
				//$pageTitle = get_bloginfo('name');
				$pageTitle = "Homepage";
				$icon = plugin_dir_path(__FILE__)."home-icon.png";
				if (empty($pageImageUrl) && file_exists($icon))
				{
					$pageImageUrl = plugin_dir_url( __FILE__ ) . "home-icon.png";
				}
			}
		}
		
		if (!is_admin()) 
		{
			switch ($displayFlag) 
			{
				case 1://only script is included without showing popup or sidebar widget. it's good if admin wants to see what is going on without the users knowing it
					$displayWidget = "false";			
					$ret = $this->generatePopupCode($pageTitle, $accountAlias, $pageImageUrl, $displayWidget);
					break;
				case 2://popup is visible, sidebar widget not visible.
					$displayWidget = "true";
					$ret = $this->generatePopupCode($pageTitle, $accountAlias, $pageImageUrl, $displayWidget);
					break;
				case 3://sidebar widget is visible, popup is not visible.
					$ret = $this->generateSidebarWidgetCode($pageTitle, $accountAlias, $pageImageUrl, $widgetNum);
					break;
				case 0://nothing works, including the admin side
				default:
					$ret = "";
			}
		}
		return $ret;
	}
	private function getProductImageUrl($postId, $product)
	{
		$pageImageUrl = "";
		if ($product)
		{
			if (has_post_thumbnail($postId))
			{
				$images = wp_get_attachment_image_src(get_post_thumbnail_id($postId), array(100, 100));
				if (count($images)>0)
				{
					$pageImageUrl = $images[0];
				}
			}
			else
			{
				$attachment_ids = $product->get_gallery_attachment_ids();
				if (count($attachment_ids)>0)
				{
					$pageImageUrl = wp_get_attachment_url($attachment_ids[0]);
				}
			}
		}

		//if product didnt have image, try to set default image
		$icon = plugin_dir_path(__FILE__)."product-icon.png";
		if (empty($pageImageUrl) && file_exists($icon))
		{
			$pageImageUrl = plugin_dir_url( __FILE__ ) . "product-icon.png";
		}
		return $pageImageUrl;
	}
	private function getCategoryImageUrl($postId, $category)
	{
		$pageImageUrl = "";
		if ($category)
		{
			//try to get image of the category
			$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ); 
			if ($thumbnail_id)
			{
				$pageImageUrl = wp_get_attachment_url( $thumbnail_id ); 
			}
				
			//if category didnt have image try to get thumbnail of first product in this category
			if (empty($pageImageUrl))
			{
				//build our query
				$query_args = array(
					'meta_query' => array(
						array(
							'key' => '_visibility',
							'value' => array( 'catalog', 'visible' ),
							'compare' => 'IN'
						)
					),
					'post_status' => 'publish',
					'post_type' => 'product',
					'posts_per_page' => 1,
					'tax_query' => array(
						array(
							'field' => 'id',
							'taxonomy' => 'product_cat',
							'terms' => $category->term_id
						)
					),
					'orderby'    => 'date',
					'sort_order' => 'desc'
				);
				//Query DB
				$products = get_posts( $query_args );
				//If matching products found
				if($products && count($products)>0)
				{
					if (has_post_thumbnail($products[0]->ID)) 
					{
						// check for a thumbnail
						$images = wp_get_attachment_image_src(get_post_thumbnail_id($products[0]->ID), array(100, 100));
						if (count($images)>0)
						{
							$pageImageUrl = $images[0];
						}
					}
					else
					{
						//check for image of product
						$attachment_ids = $products[0]->get_gallery_attachment_ids();
						if (count($attachment_ids)>0)
						{
							$pageImageUrl = wp_get_attachment_url($attachment_ids[0]);
						}
					}
				}
			}
		}
					
		//if category still doesnt have image, try to set default image
		$icon = plugin_dir_path(__FILE__)."category-icon.png";
		if (empty($pageImageUrl) && file_exists($icon))
		{
			$pageImageUrl = plugin_dir_url( __FILE__ ) . "category-icon.png";
		}
		return $pageImageUrl;
	}
	private function rtcGetTheID() 
	{
		//$postid = url_to_postid($url);
		if (in_the_loop())
		{
			$post_id = get_the_ID();
		}
		else
		{
			global $wp_query;
			$post_id = $wp_query->get_queried_object_id();
		}
		return $post_id;
	}
	private function generateSidebarWidgetCode($pageTitle, $accountAlias, $pageImageUrl, $widgetNum)
	{
		$divid = "rtc-div-main-widget_".$widgetNum;
		$ret = "<!--rtc code-->
			<div id='".$divid."'>
			</div>
			<script type='text/javascript'>
				var iframeElem = document.createElement('iframe');
				iframeElem.frameBorder = 0;
				iframeElem.width = '100%';
				iframeElem.height = '347px';
				iframeElem.id = 'rtc-iframe-main-widget_".$widgetNum."';
				
				var pageTitle ='".$pageTitle."';
				var account = '".$accountAlias."';
				var pageImageUrl='".$pageImageUrl."';
				var additionalCssBUrl='';
				var referrer=document.referrer;
				var accountInDemoMode = false;
							
				if (!pageTitle)
				{ 
					var wintitle = document.title;
					if (wintitle.indexOf('|') != -1)
					{
						wintitle = wintitle.substring(0, wintitle.indexOf('|')).trim();
					}
					pageTitle = wintitle;
				}
				
				if (!account) {
					account = document.location.hostname.toLowerCase(); //if no account set the domain as default account
					accountInDemoMode = true;
				}

				var demoMode = '';
				if (accountInDemoMode) {
					demoMode = '&accountindemomode=1';
				}
				
				var oUrl = ('https:' == document.location.protocol ? 'https' : 'http') + 
							'://rtc.realtimecrowd.net/visitor/v1/' + 
							fixAndEscape(account) + 
							'/?pagetitle=' + fixAndEscape(pageTitle) + 
							'&pageimageurl=' + fixAndEscape(pageImageUrl) + 
							//'&widgettitle=' + fixAndEscape(widgetTitle) + 
							'&additionalcssburl=' + fixAndEscape(additionalCssBUrl) + 
							'&referrerjs=' + fixAndEscape(referrer) +
							demoMode +
							'&hideclosebutton=1';
								
				iframeElem.setAttribute('src', oUrl);

				document.getElementById('".$divid."').appendChild(iframeElem);
					
				function fixAndEscape(val)
				{
					if (typeof (val) !== 'undefined' &&	val !== null &&	val !== '')	{ val = encodeURIComponent(val); } else { val = ''; } return val;
				}
			</script>
			<!--/rtc code-->";
		return $ret;
	}
	private function generatePopupCode($pageTitle, $accountAlias, $pageImageUrl, $displayWidget)
	{
		$ret = "<!--rtc code-->
			<div id='rtc-div-main'>
			</div>
			<script>
				!function (rtcObj, topElem)
				{
					function embedRtcLauncher(oUrl)
					{
						var scriptElem = topElem.createElement('script');
						scriptElem.type = 'text/javascript';
						scriptElem.async = !0;
						scriptElem.src = ('https:' == topElem.location.protocol ? 'https' : 'http') + ':' + oUrl + '/Scripts/RteVisitorLauncher-2.0.js?nd=' + new Date().getTime();
						topElem.body.appendChild(scriptElem);
					}
					rtcObj.Account = '".$accountAlias."';
					rtcObj.ContainerId = 'rtc-div-main';
					rtcObj.RtcUrl = '//rtc.realtimecrowd.net';
					rtcObj.HideWidget = !".$displayWidget.";
					rtcObj.PageTitle = '".$pageTitle."';
					rtcObj.PageImageUrl = '".$pageImageUrl."';
					embedRtcLauncher(rtcObj.RtcUrl);
				}
				(window.RTC = {}, document);
			</script>
			<!--/rtc code-->";
		return $ret;
	}
}
?>