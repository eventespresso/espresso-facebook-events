<?php
/*
Plugin Name: Event Espresso - Facebook Events Addon
Plugin URI: http://eventespresso.com/
Description: Facebook integration addon for Event Espresso. <a href="admin.php?page=support" >Support</a>

Reporting features provide a list of events, list of attendees, and excel export.

Version: 1.0

Author: Event Espresso
Author URI: http://www.eventespresso.com
 
Copyright (c) 2008-2010 Seth Shoultes  All Rights Reserved.

After reading this agreement carefully, if you do not agree to all of the terms of this agreement, you may not use this software in any way, shape, or form, partially or in full.

1.0 Ownership.

This software product, and any and all previous versions, produced by Seth Shoultes and/or anyone working in his stead, is a wholly owned product by Seth Shoultes and is protected by united states copyright laws and international copyright treaties extended by the United States of America.  

1.1 Usage.

This software is licensed to you,  You are not obtaining title to the software or any copyrights. You may not sell, resell, sub-license, rent, or lease the software for any purpose. You may not redistribute, or resell this software in any medium without prior written consent.

You are free to modify the code for your own personal use. The license may be transferred to another only if you keep no copies of the software. 

You may make one backup copy of the software, provided your copy is for backup purposes only, that you copy all proprietary notices and licenses contained therein, and such copy is used only if the original copy is defective.

You may not make some or all of the software file(s) available on your web page as a separate or down-loadable reusable file.

This license covers one installation of the program on one domain/url only. 

THIS SOFTWARE AND THE ACCOMPANYING FILES ARE SOLD "AS IS" 
AND WITHOUT WARRANTIES AS TO PERFORMANCE OR MERCHANTABILITY 
OR ANY OTHER WARRANTIES WHETHER EXPRESSED OR IMPLIED.

NO WARRANTY OF FITNESS FOR A PARTICULAR PURPOSE IS OFFERED. 
ANY LIABILITY OF THE SELLER WILL BE LIMITED EXCLUSIVELY TO 
PRODUCT REPLACEMENT OR REFUND OF PURCHASE PRICE. 

Failure to install the program is not a valid reason for 
refund of purchase price.

In no event shall Seth Shoultes be liable for any indirect, 
special, incidental, economic, or consequential damages 
arising out of the use of or inability to use the Software 
or documentation, even if advised of the possibility of such 
damages. In the event any liability is imposed on Seth 
Shoultes, the liability of Seth Shoultes to you or any 
third party shall not exceed the purchase price you paid for 
the software and documentation.

The user assumes the entire risk of using the program.
*/

add_shortcode( "eventdetails", 'espresso_fb_eventdetails_shortcode'  );
add_action( 'admin_menu', 'espresso_fb_menu' );
add_action( 'init', 'espresso_fb_styles' );
add_action( 'wp_head', 'espresso_fb_fbjssdk' );

register_activation_hook( __FILE__, 'espresso_fb_activate' );
//register_deactivation_hook( __FILE__, 'espresso_fb_deactivate' );

function espresso_fb_activate(){
	global $wpdb;
	add_option( 'facebookevents-appid', '' );
	add_option( 'facebookevents-appsecret', '' );
	add_option( 'facebookevents-accesstoken', '' );
	$eventstable = $wpdb->prefix . "fbevents_events";
	if ($wpdb->get_var("SHOW TABLES LIKE '$eventstable'") != $eventstable) {
		$query = "CREATE TABLE $eventstable (
 			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 			event_id VARCHAR(255),
 			fb_event_id VARCHAR(255),
 			event_name VARCHAR(255),
 			read_time DATETIME
 		)";
		$wpdb->query($query);
	}
}
function espresso_fb_deactivate(){
}

function espresso_fb_menu(){
#	add_submenu_page('event_espresso', __('Event Espresso - Facebook Settings','event_espresso'), __('Facebook Settings','event_espresso'), 'administrator', 'espresso_facebook', 'espresso_fb_settings');
	if ( function_exists( 'add_options_page' ) ) {
//		add_options_page( 'FacebookEvents', 'Facebook Events', 'administrator', basename( __FILE__ ), 'espresso_fb_settings' );
	}
}

function espresso_fb_fbjssdk() {
?>
	<script type='text/javascript'>
		jQuery(document).ready(function() {
			jQuery('body').append('<div id="fb-root"></div>');
  			window.fbAsyncInit = function() {
  				FB.init({appId: '<?php echo get_option('facebookevents-appid'); ?>', status: true, cookie: true,xfbml: true});
			};
			(function() {
				var e = document.createElement('script'); e.async = true;
				e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
				document.getElementById('fb-root').appendChild(e);
  			}());
		});
	</script>
<?
}
function espresso_fb_styles() {
	wp_enqueue_style( 'fbeventscss', plugin_dir_url( __FILE__ ) . 'css/fbevents.css' );
	if ( is_admin() ) {
		wp_enqueue_script( 'fbeventsadminjs', plugin_dir_url( __FILE__ ) . 'js/fbevents_admin.js', array( 'jquery' ) );
	}
	wp_enqueue_script( 'fbeventsint', plugin_dir_url( __FILE__ ) . 'js/fbevents.js', array( 'jquery' ) );
}

function espresso_fb_url($event_id='') {
	if( $event_id ){
		return admin_url( 'options-general.php?page=' . basename( __FILE__ ) );
	}else{
		return admin_url( 'options-general.php?page=' . basename( __FILE__ ) );	
	}
}
function get_facebook_instance() {
	require_once( dirname( __FILE__ ) . "/includes/facebook.php" );
	$app_id = get_option( 'facebookevents-appid' );
	$app_secret = get_option( 'facebookevents-appsecret' );
	$facebook = new Facebook(array(
		'appId' => $app_id,
		'secret' => $app_secret,
		'cookie' => true,
	));
	return $facebook;
}

function espresso_fb_settings(){
	global $wpdb;
	$app_id = get_option( 'facebookevents-appid' );
	$app_secret = get_option( 'facebookevents-appsecret' );
	$facebook = get_facebook_instance();
	$session = $facebook->getSession(FALSE, espresso_fb_url() );
	include("includes/facebook-settings.php");
}

function espresso_fb_createevent($event_id){
	global $wpdb;
	$app_id = get_option( 'facebookevents-appid' );
	$app_secret = get_option( 'facebookevents-appsecret' );
	$facebook = get_facebook_instance();
	$session = $facebook->getSession(FALSE, espresso_fb_url() );
	if ($session) {
		try {
			$profile_id = get_option( 'facebookevents-appid' );
			if( isset($_REQUEST['espresso_fb']) ){
				$starttime = strtotime($_REQUEST['start_date']. ' '.$_POST['start_time'][0]);
				$endtime = strtotime($_REQUEST['end_date']. ' ' .$_POST['end_time'][0]);
				$events = $facebook->api("/$profile_id/events", 'POST', array(
					"name"=>$_POST['event'],
					"start_time"=>$starttime,
					"end_time"=>$endtime,
					"location"=>($_REQUEST['address'].", ".$_REQUEST['city'].", ".$_REQUEST['city']),
					"venue"=>( array("street"=>$_REQUEST['address'],"city"=>$_REQUEST['city']) ),
					"description"=>$_POST['event_desc'],
				));
				$fb_e_id = $events['id'];
				$eventstable = $wpdb->prefix . "fbevents_events";
				$sql = "INSERT INTO $eventstable (event_id, fb_event_id,event_name) VALUES ('$event_id','$fb_e_id','$_POST[event]')";
				$wpdb->query($sql);
			}
		} catch ( Exception $e ) {
			error_log( $e );
			echo "Unable to create event: $e";
		}
	} else {
		echo "You are not connected to Facebook.";
	}
}
function espresso_fb_updateevent($event_id){
	global $wpdb;
	$app_id = get_option( 'facebookevents-appid' );
	$app_secret = get_option( 'facebookevents-appsecret' );
	$facebook = get_facebook_instance();
	$eventstable = $wpdb->prefix . "fbevents_events";
	$fb_e_id = $wpdb->get_var("SELECT fb_event_id FROM $eventstable WHERE event_id='{$event_id}'");
	$session = $facebook->getSession(FALSE, espresso_fb_url() );
	if ($session) {
		try {
			$profile_id = get_option( 'facebookevents-appid' );
			if( isset($_REQUEST['espresso_fb']) ){
				$starttime = strtotime($_REQUEST['start_date']. ' '.$_REQUEST['start_time'][0]);
				$endtime = strtotime($_REQUEST['end_date']. ' ' .$_REQUEST['end_time'][0]);
				if( !empty($fb_e_id) ){
					$events = $facebook->api("/$fb_e_id/events", 'POST', array(
						"name"=>$_REQUEST['event'],
						"start_time"=>$starttime,
						"end_time"=>$endtime,
						"location"=>($_REQUEST['address'].", ".$_REQUEST['city'].", ".$_REQUEST['city']),
						"venue"=>( array("street"=>$_REQUEST['address'],"city"=>$_REQUEST['city']) ),
						"description"=>$_REQUEST['event_desc'],
					));
				}else{
					$events = $facebook->api("/$profile_id/events", 'POST', array(
						"name"=>$_REQUEST['event'],
						"start_time"=>$starttime,
						"end_time"=>$endtime,
						"location"=>($_REQUEST['address'].", ".$_REQUEST['city'].", ".$_REQUEST['city']),
						"venue"=>( array("street"=>$_REQUEST['address'],"city"=>$_REQUEST['city']) ),
						"description"=>$_POST['event_desc'],
					));
					$fb_e_id = $events['id'];
					$eventstable = $wpdb->prefix . "fbevents_events";
					$sql = "INSERT INTO $eventstable (event_id, fb_event_id,event_name) VALUES ('$event_id','$fb_e_id','$_POST[event]')";
					$wpdb->query($sql);		
				}
			}
		} catch ( Exception $e ) {
			error_log( $e );
			echo "Unable to create event: $e";
		}
	} else {
		echo "You are not connected to Facebook.";
	}
}
function espresso_fb_eventstable(){
	global $wpdb;
	$eventstable = $wpdb->prefix . "fbevents_events";
	$sql = "SELECT * FROM $eventstable";
	$eventslist = $wpdb->get_results($sql);
	$facebook = get_facebook_instance();
?>
	<?php if ( $eventslist ) { ?>
	<?php foreach ($eventslist as $event) { ?>
<?php
			$fb_e_id = $event->fb_event_id;
			$people_attending = array();
			try {
				$data = $facebook->api("/$fb_e_id/attending");
			} catch ( Exception $e ) {
					$pass = 0;
			}
			$attending = count($data['data']);
			$count = 1;
			foreach ($data['data'] as $person) {
				if ($count > 8) break;
				$people_attending[] = $person;
				$count += 1;
			}
			$people_attending = json_encode($people_attending);
			$data = $facebook->api("/$fb_e_id/maybe");
			$maybe = count($data['data']);
			$data = $facebook->api("/$fb_e_id/declined");
			$declined = count($data['data']);
			$data = $facebook->api("/$fb_e_id/noreply");
			$noreply = count($data['data']);
?>
	<tr>
		<td><?php echo  $event->event_id; ?></td>
		<td><?php echo  $event->event_name; ?></td>
		<td><?php echo  $attending;?></td>
		<td><?php echo  $declined;?></td>
		<td><?php echo  $maybe;?></td>
		<td><?php echo  $noreply?></td>
		<td>[eventdetails id=<?php echo  $event->event_id; ?>]</td>
		<td><a href="http://www.facebook.com/event.php?eid=<?php echo  $event->fb_event_id; ?>" target="_blank">Event Page On Facebook</a></td>
	</tr>
	<?php } ?>
	<?php } else { ?>
	<strong>No events created yet!!!</strong>
<?php } ?>
<?php
}
	function espresso_fb_eventdetails_shortcode( $atts ) {
		extract ( shortcode_atts( array("id" => ""), $atts));
		return espresso_fb_eventdetail($id);
	}
	function espresso_fb_eventdetail($event_id, $update=false) {
		global $wpdb;
		$eventstable = $wpdb->prefix . "fbevents_events";
		$sql = "SELECT * FROM $eventstable WHERE event_id='$event_id'";
		$event = $wpdb->get_row($sql,ARRAY_A);
		$fb_e_id = $event['fb_event_id'];
		$last_read = strtotime($event['read_time']);
		$current_time = time();
		$time_elapsed = $current_time-$last_read;
		$facebook = get_facebook_instance();
		if ($time_elapsed > 15 || $update) {
			try {
				$event = $facebook->api("/$fb_e_id");
			} catch (Exception $e) {
				$pass = 0;
			}
			$event['event_id'] = $event_id;
			$description = $event['description'];
			$start_time = $event['start_time'];
			$end_time = $event['end_time'];
			$location = $event['location'];
			$venue = json_encode($event['venue']);
			$people_attending = array();
			try {
				$data = $facebook->api("/$fb_e_id/attending");
			} catch ( Exception $e ) {
					$pass = 0;
			}
			$attending = count($data['data']);
			$count = 1;
			foreach ($data['data'] as $person) {
				if ($count > 8) break;
				$people_attending[] = $person;
				$count += 1;
			}
			$people_attending = json_encode($people_attending);
			$data = $facebook->api("/$fb_e_id/maybe");
			$maybe = count($data['data']);
			$data = $facebook->api("/$fb_e_id/declined");
			$declined = count($data['data']);
			$data = $facebook->api("/$fb_e_id/noreply");
			$noreply = count($data['data']);
			$read_time = date("Y-m-d H:i:s");
			$event['attending'] = $attending;
			$event['maybe'] = $maybe;
			$event['not_attending'] = $declined;
			$event['awaiting'] = $noreply;
			$event['people_attending'] = $people_attending;
			$sql = "UPDATE $eventstable SET read_time='$read_time' WHERE event_id='$event_id'";
			$wpdb->query($sql);
		}
		$when = "$event[start_time] - $event[end_time]";
		$people_attending = json_decode($event['people_attending'], true);
		ob_start();
?>
	<div class="fbeventbox" id="fbeventbox<?php echo $fb_e_id; ?>">
		<h1>Add to Facebook</h1>
		<div class="eventrsvpbox">
			<div class="eventrsvpbutton" onclick="attendEvent(<?php echo $fb_e_id; ?>,'<?php echo site_url(); ?>');">I'm Attending</div>
			<div class="eventrsvpbutton" onclick="maybeEvent(<?php echo $fb_e_id;?>, '<?php echo site_url(); ?>');">Maybe</div>
			<div class="eventrsvpbutton" onclick="declineEvent(<?php echo $fb_e_id;?>, '<?php echo site_url(); ?>');">No</div>
			<span class="fbeventattendtext"><span id="fbeventattending<?php echo $fb_e_id; ?>"><?php echo $event['attending']; ?></span> Attending</span> |
			<span id="fbeventmaybe<?php echo $fb_e_id; ?>"><?php echo $event['maybe']; ?></span> Maybe Attending |
			<span id="fbeventawaiting<?php echo $fb_e_id; ?>"><?php echo $event['awaiting']; ?></span> Awaiting Reply |
			<span id="fbeventdeclined<?php echo $fb_e_id; ?>"><?php echo $event['not_attending']; ?></span> Not Attending
		</div>
		<div class="fbeventattendinglist">
		<?php foreach ($people_attending as $person) { ?>
			<div class="fbeventattendingprofile">
			<div style="float: left;"><img src="http://graph.facebook.com/<?php echo $person['id']; ?>/picture" width="25" height="25" /></div><div style="display: block; float: left" class="fbeventprofilename"><span> <?php echo $person['name']; ?></span></div>
			</div>
		<?php } ?>
		</div>
	</div>
	<div style="clear:both; margin-bottom:10px;"></div>
<?php
	$eventdump = ob_get_clean();
	return $eventdump;
}
