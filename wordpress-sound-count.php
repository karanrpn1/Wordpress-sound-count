<?php 
/*
  Plugin Name: Wordpress sound count
  Plugin URI: http://helpfulinsight.in
  Description: Count unique visitor playing sound on post	
  Version: 1.0
  Author: Karan Rupani	
  Author URI: http://helpfulinsight.in
*/  
 
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if (!defined('CUS_WOO_SOUND_THEME_DIR'))
    define('CUS_WOO_SOUND_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template());

if (!defined('CUS_WOO_SOUND_PLUGIN_NAME'))
    define('CUS_WOO_SOUND_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
 
if (!defined('CUS_WOO_SOUND_PLUGIN_DIR'))
    define('CUS_WOO_SOUND_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . CUS_WOO_SOUND_PLUGIN_NAME);	
	
if (!defined('CUS_WOO_SOUND_PLUGIN_URL'))
    define('CUS_WOO_SOUND_PLUGIN_URL', WP_PLUGIN_URL . '/' . CUS_WOO_SOUND_PLUGIN_NAME);	
	  
/*----------------------------------------------------
		CREATE TABLES ON PLUGIN ACTIVATION
-------------------------------------------------------*/
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'custom_woo_sound_create_tables');	
function custom_woo_sound_create_tables() {
   	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$impressionTable = $wpdb->prefix.'count_impression';
	$paidTable = $wpdb->prefix.'count_impression';
	$paidEmailTable = $wpdb->prefix.'impression_email';
	
	if($wpdb->get_var("show tables like '$impressionTable'") != $impressionTable)	{
		$sql = "CREATE TABLE " . $impressionTable . " (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`user_id` int(11) NOT NULL,
			`ip` varchar(255) NOT NULL,
			`date` varchar(100) NOT NULL,
			PRIMARY KEY (`ID`)
		);";		 
		dbDelta($sql);
	} 
	
	if($wpdb->get_var("show tables like '$paidTable'") != $paidTable)	{
		$sql = "CREATE TABLE " . $paidTable . " (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`user_id` int(11) NOT NULL,
			`amount` int(11) NOT NULL,
			`date` varchar(100) NOT NULL,
			PRIMARY KEY (`ID`)
		);";		 
		dbDelta($sql);
	} 
	
	if($wpdb->get_var("show tables like '$paidEmailTable'") != $paidEmailTable)	{
		$sql = "CREATE TABLE " . $paidEmailTable . " (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`view` int(11) NOT NULL,
			`date` varchar(100) NOT NULL,
			PRIMARY KEY (`ID`)
		);";		 
		dbDelta($sql);
	} 
	
	
	if (! wp_next_scheduled ( 'my_daily_event' )) {
		wp_schedule_event(time(), 'daily', 'my_daily_event');
    }
	
}


/*--------------------------------------------------
			DELETE CRON ON DEACTIVATE
-----------------------------------------------------*/
register_deactivation_hook(__FILE__, '_handle_plugin_deactivation');

function _handle_plugin_deactivation() {
	wp_clear_scheduled_hook('my_daily_event');
}


/*--------------------------------------------------
				ADD ADMIN MENU PAGE			
-----------------------------------------------------*/
require_once(CUS_WOO_SOUND_PLUGIN_DIR."/admin/admin-data.php");


/*--------------------------------------------------
			ENQUE CUSTOM SCRIPTS
----------------------------------------------------*/
add_action( 'wp_enqueue_scripts', '_hangle_custom_script_import' );
function _hangle_custom_script_import() {
	
	wp_register_script( "custom_ajax_script", plugins_url('/assets/js/custom_script.js', __FILE__), array( 'jquery' ) );
   	
	wp_localize_script( 'custom_ajax_script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
   
   	wp_enqueue_script( 'custom_ajax_script' );
  
}


/*--------------------------------------------------
			ENQUE ADMIN CUSTOM SCRIPTS
----------------------------------------------------*/
add_action( 'admin_enqueue_scripts', 'load_woo_sound_wp_admin_style' );
function load_woo_sound_wp_admin_style($hook) {

	if($hook == 'toplevel_page_wordpress-sound-count' || $hook =='sound-impression_page_wordpress-sound-settings') { 
		
		wp_enqueue_style( 'woo_sound_admin_style', plugins_url('/assets/css/admin-style.css', __FILE__) );
		
		wp_register_script( "custom_admin_ajax_script", plugins_url('/assets/js/custom_admin_script.js', __FILE__), array( 'jquery' ) );
   	
		wp_localize_script( 'custom_admin_ajax_script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
   
   		wp_enqueue_script( 'custom_admin_ajax_script' );
	}
	
}

/*--------------------------------------------------
			AJAX COUNT UNIQUE SOUND IMPRESSION
----------------------------------------------------*/
add_action('wp_ajax_count_impression','_handle_sound_impression_count');
add_action('wp_ajax_nopriv_count_impression','_handle_sound_impression_count');

function _handle_sound_impression_count() {	
	
	if(isset($_POST['postID'])) {		
		global $wpdb; 		
		$postID = $_POST['postID'];
		$userIP = getUserIP();
		$current_user = wp_get_current_user();
		$tablename = $wpdb->prefix."count_impression"; 
		
		$result = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE `post_id`='".$postID."' AND (`user_id`='".$current_user->ID."' OR `ip`='".$userIP."')");
		$noOfResult = $wpdb->num_rows;
		
		if($noOfResult==0) {
			$success =  $wpdb->insert( $tablename, array(
				'post_id' => $postID, 
				'user_id' => $current_user->ID,
				'ip' => $userIP,
				'date' => time()
				),
				array('%d','%d','%s','%s')
			);
			
			echo json_encode(array('status' => 'success'));
		}	
		else {
			echo json_encode(array('status' => 'error'));
		}	
	}
	else {
		echo json_encode(array('status' => 'error'));
	}
	die();
}

/*--------------------------------------------------
			GET USER REAL IP ADDRESS	
----------------------------------------------------*/
if(!function_exists('getUserIP')){
	function getUserIP() {
		// Get real visitor IP behind CloudFlare network
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		
		if(filter_var($client, FILTER_VALIDATE_IP))	{
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
			$ip = $forward;
		}
		else {
			$ip = $remote;
		}	
		return $ip;
	}
}

/*--------------------------------------------------
			AJAX ADD PAID COUNT IMPRESSION
----------------------------------------------------*/
add_action('wp_ajax_pay_count_impression','_handle_pay_impression');
add_action('wp_ajax_nopriv_pay_count_impression','_handle_pay_impression');

function _handle_pay_impression() {	
	
	if(isset($_POST['postID']) && !empty($_POST['amount'])) {		
		
		global $wpdb; 	
		
		$post_id = $_POST['postID'];
		$amount = $_POST['amount'];
		
		$impressionTable = $wpdb->prefix.'count_impression';
		$paidTable = $wpdb->prefix.'count_paid';
		
		$viewCount = $wpdb->get_var( "SELECT COUNT(*) FROM ".$impressionTable ." where `post_id`='".$post_id."'" );
		
		$paidCount = $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM ".$paidTable." where `post_id`='".$post_id."'");
		
		$unpaidCount = $viewCount-$paidCount;
		
		if($amount>$unpaidCount) {
			echo json_encode(array('status' => 'error','message'=>'Can\'t pay more than unpaid count.'));
		}
		else {
			$success =  $wpdb->insert( $paidTable, array(
				'post_id' => $post_id, 
				'user_id' => $_POST['userID'],
				'amount' => $amount,
				'date' => time()
				),
				array('%d','%d','%d','%s')
			);
			
			echo json_encode(array('status' => 'success'));
		}
		
	}
	else {
		echo json_encode(array('status' => 'error'));
	}
	die();
}

/*--------------------------------------------------
		SEND EMAIL TO VENDOR ON ACHIVING GOAL
-----------------------------------------------------*/
add_action('my_daily_event', '_handle_send_achive_goal_mail');

//add_action('init','_handle_send_achive_goal_mail');
function _handle_send_achive_goal_mail() {
	
	global $wpdb;
	$impressionTable = $wpdb->prefix.'count_impression';
	$impressionEmailTable = $wpdb->prefix.'impression_email';
	
	$countRange = ( get_option('sound_plugin_range_constant') ) ? get_option('sound_plugin_range_constant') : 1000;
	
	$impressionInfo = $wpdb->get_results( "SELECT COUNT(*) as `total_view`,`post_id`,`user_id` FROM ".$impressionTable ." GROUP BY `post_id` " );
	
	foreach($impressionInfo as $impression) {
		
		$paidEmailInfo = $wpdb->get_results( "SELECT view FROM ".$impressionEmailTable." where `post_id`='".$impression->post_id."'");
		
		$paidEmailCount = $wpdb->num_rows;	
		
		if($paidEmailCount>0) {
			$totalImpression = $impression->total_view-$paidEmailInfo[0]->view;
		}
		else {
			$totalImpression = $impression->total_view;
		}
				
		if($totalImpression>=$countRange) {
			
			$user_info = get_userdata($impression->user_id);
			$userName = $user_info->first_name.' '.$user_info->last_name;	
			
			if(get_option('sound_plugin_email_template') ) {
				$search = array("{{USERNAME}}", "{{TOTALVIEWS}}", "{{POSTTITLE}}");
				$replace   = array( $userName , $impression->total_view,get_the_title($impression->post_id) );
				$mailBody = str_replace($search, $replace, stripslashes(html_entity_decode(get_option('sound_plugin_email_template'))));
			}
			else {
				$mailBody = 'You have achived '.$impression->total_view.' view on'.get_the_title($impression->post_id);
			}		
			
			$userEmail = $user_info->user_email;			 	
			$subject = "Achived New Milestone";
			$headers[] = "Content-type:text/html;charset=UTF-8";
			$headers[] = 'From: '.get_option( 'blogname' ).' <'.get_option( 'admin_email' ).'>';
			$headers[] = 'Cc: '.get_option( 'blogname' ).' <'.get_option( "admin_email" ).'>';
			
			wp_mail($userEmail, $subject,$mailBody, $headers);	

			if($paidEmailCount>0) {
				$wpdb->update( $impressionEmailTable, 
					array(        
					'view' => $paidEmailInfo[0]->view+$countRange,
					'date' => time()
					),  
					array( 'post_id' => $impression->post_id ), 
					array( '%d', '%s'), 
					array( '%d' ) 
				);
			}
			else {
				$wpdb->insert( $impressionEmailTable, array(
					'post_id' => $impression->post_id,
					'view' => $countRange,
					'date' => time()
					),
					array('%d','%d','%s')
				);
			}
					
		}
	}

}