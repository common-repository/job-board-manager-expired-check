<?php
/*
Plugin Name: Job Board Manager - Expired check
Plugin URI: http://www.pickplugins.com/
Description: Daily Expired job checker for Job Board Manager.
Version: 1.0.3
Author: pickplugins
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


define('job_bm_expired_check_textdomain', 'job-board-manager-expired-check' );

add_action( 'plugins_loaded',  'job_bm_expired_check_textdomain');

function job_bm_expired_check_textdomain() {
  load_plugin_textdomain( job_bm_expired_check_textdomain , false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 
}




register_activation_hook(__FILE__, 'job_bm_cron_experied_check_init');

function job_bm_cron_experied_check_init() {
	
	$job_bm_experied_check_recurrance = get_option(  'job_bm_experied_check_recurrance');
	
	if(empty($job_bm_experied_check_recurrance)){
		$job_bm_experied_check_recurrance ='daily';
		}
	
	wp_schedule_event(time(), $job_bm_experied_check_recurrance, 'job_bm_cron_expired_check');
}



register_deactivation_hook(__FILE__, 'job_bm_cron_expired_check_deactivation');

function job_bm_cron_expired_check_deactivation() {
	wp_clear_scheduled_hook('job_bm_cron_expired_check');
}





add_action('job_bm_cron_expired_check', 'job_bm_cron_func_daily_expired_check');

function job_bm_cron_func_daily_expired_check() {

	global $wpdb;
	
	$experied_jobs_status 	= get_option(  'job_bm_experied_jobs_post_status');		
	$experied_jobs_status 	= empty( $experied_jobs_status ) ? 'publish' : $experied_jobs_status;
	$job_expiry_days 		= get_option(  'job_bm_job_expiry_days');		
	$job_expiry_days 		= empty( $job_expiry_days ) ? '30' : $job_expiry_days;

	$experied_jobs = get_posts( array(
		'posts_per_page' => -1,
		'post_type' => 'job',
		'meta_query' => array(
			array(
				'key' => 'job_bm_job_status',
				'value' => 'expired',
				'compare' => '!=',
			),
			array(
				'key' => 'job_bm_expire_date',
				'value' => date('Y-m-d'),
				'compare' => '<',
				'type' => 'DATE',
			),
		),
	) );
	
	foreach( $experied_jobs as $job ){
			
		update_post_meta( $job->ID, "job_bm_job_status", "expired" );
		$wpdb->update( $wpdb->posts, array( "post_status" => $experied_jobs_status ), array( 'ID' => $job->ID ) );
	}
}


function check_expiry_function(){
	
	ob_start();
	
	
	global $wpdb;
	
	$experied_jobs_status 	= get_option(  'job_bm_experied_jobs_post_status');		
	$experied_jobs_status 	= empty( $experied_jobs_status ) ? 'publish' : $experied_jobs_status;
	$job_expiry_days 		= get_option(  'job_bm_job_expiry_days');		
	$job_expiry_days 		= empty( $job_expiry_days ) ? '30' : $job_expiry_days;

	$experied_jobs = get_posts( array(
		'posts_per_page' => -1,
		'post_type' => 'job',
		'meta_query' => array(
			array(
				'key' => 'job_bm_job_status',
				'value' => 'expired',
				'compare' => '!=',
			),
			array(
				'key' => 'job_bm_expire_date',
				'value' => date('Y-m-d'),
				'compare' => '<',
				'type' => 'DATE',
			),
		),
	) );
	
	foreach( $experied_jobs as $job ){
		
		// $job_bm_expire_date = get_post_meta( $job->ID, 'job_bm_expire_date', true );
		// echo "<pre>"; print_r( $job_bm_expire_date ); echo "</pre>";
	}

	return ob_get_clean();
}
// add_shortcode( 'check_expiry_function', 'check_expiry_function' ); 


function job_bm_cron_expired_check_extra_options($section_options){
	
				$section_options_extra = array(

									'job_bm_experied_jobs_post_status'=>array(
										'css_class'=>'experied_jobs_post_status',					
										'title'=>__('Experied jobs post status', job_bm_expired_check_textdomain),
										'option_details'=>__('Post status for experied jobs.', job_bm_expired_check_textdomain),						
										'input_type'=>'select', // text, radio, checkbox, select, 
										'input_values'=>'publish', // could be array
										'input_args'=> array('publish'=>__('Publish', job_bm_expired_check_textdomain), 'draft'=>__('Draft', job_bm_expired_check_textdomain), 'pending'=>__('Pending', job_bm_expired_check_textdomain),'private'=>__('Private', job_bm_expired_check_textdomain), 'trash'=>__('Trash', job_bm_expired_check_textdomain)),
										),
	
									'job_bm_experied_check_recurrance'=>array(
										'css_class'=>'experied_check_recurrance',					
										'title'=>__('Expired check Recurrence', job_bm_expired_check_textdomain),
										'option_details'=>'',						
										'input_type'=>'select', // text, radio, checkbox, select, 
										'input_values'=>'publish', // could be array
										'input_args'=> array('hourly'=>__('Hourly', job_bm_expired_check_textdomain), 'twicedaily'=>__('Twicedaily', job_bm_expired_check_textdomain), 'daily'=>__('Daily', job_bm_expired_check_textdomain)),
										),

									'job_bm_job_expiry_days'=>array(
										'css_class'=>'job_bm_job_expiry_days',
										'title'=>__('Expiry days', job_bm_expired_check_textdomain),
										'option_details'=>'',
										'input_type'=>'text', // text, radio, checkbox, select,
										'input_values'=>'30', // could be array

									),


									);
									
				return array_merge($section_options,$section_options_extra);
	
	}


add_filter('job_bm_settings_section_options','job_bm_cron_expired_check_extra_options');

