<?php

if ( ! wp_next_scheduled('check_new_msg_and_send') ) {
	wp_schedule_event( time(), 'hourly', 'check_new_msg_and_send' ); // hourly, daily and twicedaily
}


add_action('check_new_msg_and_send', 'check_new_msg_and_send_callback');

function check_new_msg_and_send_callback() {
	$posts = get_posts('post_type=message_cp&post_status=draft');

	foreach ($posts as $msg) {
		$mailcheck = wp_mail( 
		'a@casepress.org', 
		$msg->post_title, 
		$msg->post_content
		);

		if($mailcheck) {
			wp_update_post(array('ID' => $msg->ID, 'post_status' => 'publish'));
		}

	}


}


add_filter( 'wp_mail_content_type', 'set_html_content_type_cp' );
function set_html_content_type_cp() {
	return 'text/html';
}
