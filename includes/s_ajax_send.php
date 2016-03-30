<?php
function s_ajax_send_script() {
	wp_enqueue_script('malsup',
		'http://malsup.github.com/jquery.form.js',
		array('jquery')
	);
}
add_action( 'wp_enqueue_scripts', 's_ajax_send_script' );