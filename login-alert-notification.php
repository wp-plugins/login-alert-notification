<?php
/*
Plugin Name: Login Alert Notification
Plugin URI: http://wordpress.org/plugins/login-alert-notification/
Description: Notify alerts with Email and Push Notifiaction Services if someone has tried to login to your WordPress dashboard.
Version: 0.51
Author: hondamarlboro
Author URI: http://daisukeblog.com/
License: GPLv2 or later http://www.gnu.org/licenses/gpl-2.0.html

This software is a derivative work of "WP Login Alerts by DigiP ver.2013-01-09.9" and the original license information is as follows:

Plugin Name: WP Login Alerts by DigiP
Plugin URI: http://www.ticktockcomputers.com/
Description: E-mails the site owner if anyone reaches or attempts to login to the site. Also shows the usernames they tried to brute force in with.
Version: 2013-01-09.9
Author: DigiP
Author URI: http://www.ticktockcomputers.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

//Add menu to WP dashboard
add_action('admin_menu', 'wp_login_alert_addmenu');
function wp_login_alert_addmenu() {
  add_options_page( __( 'Login Alert Notification', 'login-alert-notification' ), __( 'Login Alert Notification', 'login-alert-notification' ), 'manage_options', 'login-alert-notification', 'wpla_admin' );
      return;
}

//Add "Settings" to Plugins List
add_filter( 'plugin_action_links', 'wpla_admin_settings_link', 10, 2  );
function wpla_admin_settings_link( $links, $file ) {

  if ( plugin_basename(__FILE__) == $file ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=login-alert-notification' ) . '">' . 'Settings'. '</a>';
    array_unshift( $links, $settings_link );
  }

  return $links;
}

// Call settings option saved
$login_alerts_options  = get_option('login_alerts_settings');

// Require setting manager file
include('login_alerts_notification_admin.php');


if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/* Plug-in requires Prowl and NMA php libraries */
require "class.php-prowl.php";
require "class.nma.php";
require "class.pushover.php";

function login_alerts($login_status) { 

	global $login_alerts_options;
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$hostaddress = gethostbyaddr($ip);
	$browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'],ENT_QUOTES | ENT_HTML401,"UTF-8");
	$referred =  htmlspecialchars($_SERVER['HTTP_REFERER'],ENT_QUOTES | ENT_HTML401,"UTF-8"); // a quirky spelling mistake that stuck in php

	/* Set current time */
	$blogtime = date('Y-m-d H:i:s',current_time('timestamp',0)); 

	if(isset($_POST['log'])) {
		if($_POST['pwd']!='') {
			$subject = "[id:".($_POST['log'])."] ".$login_status;
		} else {
			if($_POST['log']!='') {
				$subject = "[id:".($_POST['log'])."] was submitted without password";
			} else {
				$subject = "Login button submitted without name and password";
			}
		}
	} else {
		$subject = "Login page opened but not tried to login yet";
	}

	if (isset($login_alerts_options['excludefail_enable']) && $login_status != 'Login Success') {
		return;
	}

	$message = "Date: ".$blogtime." \nIP: ".$ip." \nHostname: ".$hostaddress." \nBrowser: ".htmlentities($browser)." \nReferral: ".htmlentities($referred)." \n";

	//Exclude admin user
	if ( isset($login_alerts_options['excludeadmin_enable']) && $_POST['log']=='admin') {
		return;
	}

	//Exclude just-opened
	if ( isset($login_alerts_options['excludereach_enable']) && !isset($_POST['log'])) {
		return;
	}

	//Email
	if ( isset($login_alerts_options['email_enable']) ){
		$admin_email = get_option('admin_email');
		sleep(5);

		$to = $admin_email; //E-Mails the site owner, set in the dashboard Settings panel.
		if(isset($login_alerts_options['emailfrom'])){
			$from = $login_alerts_options['emailfrom'];
		} else{
			$from = $admin_email;
		}
		$headers = "From: $from";
		mail($to,$subject,$message,$headers);
	}

	//im.kayac.com
	if ( isset($login_alerts_options['imkayac_enable']) ){
		$username = $login_alerts_options['username'];
		$password = $login_alerts_options['secretkey'];

		$data = array(
			"message" => $subject."\n".$message,
			"password" => $password,
		);

		$data['sig'] = sha1($data['message'] . $data['password']);
		unset($data['password']);

		$data = http_build_query($data, "", "&");
	
		$header = array(
			"Content-Type: application/x-www-form-urlencoded",
			"Content-Length: ".strlen($data)
		);

		$context = array(
			"http" => array(
				"method"  => "POST",
				"header"  => implode("rn", $header),
				"content" => $data
			)
		);

		$url = "http://im.kayac.com/api/post/{$username}";
		file_get_contents($url, false, stream_context_create($context));
	}

	//Prowl
	if ( isset($login_alerts_options['prowl_enable']) ){
		$prowl_api_key = $login_alerts_options['prowlapikey'];
		$prowl = new Prowl();
		$prowl->setApiKey($prowl_api_key);

		$application = "WP Login Alert";
		$event = $subject;
		$description = $message;
		$url_prowl = "";
		$priority = 0;
		$prowl->add($application,$event,$priority,$description,$url_prowl);
	}
	
	//NMA
	if ( isset($login_alerts_options['nma_enable']) ){
		$nma = new NotifyMyAndroid(); 

		$nma_params = array(
			'apikey' => $login_alerts_options['nmaapikey'],
			'priority' => 0,
			'application' => 'WP Login Alert',
			'event' => $subject,
			'url' => '',
			'description' => $message
		);
		
		$nma->push( $nma_params );
	}

	//Pushover
	if ( isset($login_alerts_options['po_enable']) ){
		$push = new Pushover();
		$push->setToken($login_alerts_options['poapptoken']);
		$push->setUser($login_alerts_options['poapikey']);
		$push->setTitle($subject);
		$push->setMessage($message);
		//$push->setUrl('http://');
		//$push->setUrlTitle('');
		//$push->setDevice('');
		$push->setPriority(0);
		//$push->setTimestamp(time());
		//$push->setDebug(true);
		$push->send();
	}

}

add_action( 'login_enqueue_scripts', 'login_alerts' );


function login_alerts_url() {
    return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'login_alerts_url' );

function login_alerts_url_title() {
    return 'All login attempts are reported to the Administrator. You have been warned.';
}

add_filter( 'login_headertitle', 'login_alerts_url_title' );

add_action('wp_login', 'login_success'); 
add_action('wp_login_failed', 'login_fail');


function login_success(){
	login_alerts('Login Success');
}

function login_fail(){
	login_alerts('Login Failure');
}

if (isset($login_alerts_options['failredirect_enable'])) {

	add_action('wp_login_failed', 'my_front_end_login_fail'); 

	function my_front_end_login_fail(){
		wp_redirect( get_option('home'), 302 );
		exit;
	}
}

?>