<?php
if ( ! wp_next_scheduled('check_new_msg_and_send') ) {
	wp_schedule_event( time(), 'hourly', 'check_new_msg_and_send' ); // hourly, daily and twicedaily
}


add_filter( 'wp_mail_content_type', 'set_html_content_type_cp' );

function check_new_msg_and_send_callback() {

    //Get all messages without notify
    $posts = get_posts(array(
            'post_type' => 'message_cp',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'email_send',
                    'value' => '2',
                    'compare' => '!=',
                    ),
                array(
                    'key' => 'email_send',
                    'compare' => 'NOT EXISTS',
                    )
                )
            )
    );
    
    //error_log('test: ' . print_r($posts, true));
    
	foreach ($posts as $msg) {

		$emails = explode(', ',get_post_meta($msg->ID, 'meta_email_to', true));
		$mailcheck = wp_mail($emails, $msg->post_title, $msg->post_content);
        
		if($mailcheck) {
			//wp_update_post(array('ID' => $msg->ID, 'post_status' => 'publish'));
			$idmf = update_post_meta($msg->ID,'email_send','2');

		}
	}
}
add_action('check_new_msg_and_send', 'check_new_msg_and_send_callback');

remove_filter( 'wp_mail_content_type', 'set_html_content_type_cp' );

function set_html_content_type_cp() {
	return 'text/html';
}