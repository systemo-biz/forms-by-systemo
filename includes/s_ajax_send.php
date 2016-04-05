<?php
function s_ajax_send_script() {
	wp_enqueue_script('malsup',
		plugin_dir_url(__FILE__).'js/jquery.form.js',
		array('jquery')
	);
}
add_action( 'wp_enqueue_scripts', 's_ajax_send_script' );