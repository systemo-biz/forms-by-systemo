<?php 
/*
Plugin Name:       Forms by Systemo
Description:       Forms for WordPress. With save data to post and send to email.
Author:            Systemo
Plugin URI:        https://github.com/systemo-biz/forms-s
Author URI:        http://systemo.biz
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       forms_s
Domain Path:       /languages
GitHub Plugin URI: https://github.com/systemo-biz/forms-s
GitHub Branch: master
Version:           20150609.2
*/


function plugins_loaded_textdomain_forms_s() {
    load_plugin_textdomain( 'forms_s', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
} add_action('plugins_loaded', 'plugins_loaded_textdomain_forms_s');


include_once('includes/emailer.php');
include_once('includes/spam_protect.php');
include_once('includes/add_message_to_post.php');

define ("forms_tmpl_include", 1);   // включить forms-tmpls.php = 1

if (defined ("forms_tmpl_include") && forms_tmpl_include == 1) {
	include_once('includes/forms-tmpl.php');
	add_action('init', 'cp_callback_activation'); //активация и регистрация таксономии и типа поста для хранения шаблонов форм
	register_activation_hook(__FILE__, 'cp_callback_activation');
}

//Шорткоды
include_once('includes/shortcodes/form-cp.php');
include_once('includes/shortcodes/input-cp.php');
include_once('includes/shortcodes/textarea-cp.php');

//регистрируем новый тип поста
add_action( 'init', 'form_message_add_post_type_cp' );
function form_message_add_post_type_cp() {
	$labels = array(
		'name'                => _x( 'Messages', 'Post Type General Name', 'forms_s' ),
		'singular_name'       => _x( 'Message', 'Post Type Singular Name', 'forms_s' ),
		'menu_name'           => __( 'Messages', 'forms_s' ),
		'parent_item_colon'   => __( 'Parent Item:', 'forms_s' ),
		'all_items'           => __( 'All messages', 'forms_s' ),
		'view_item'           => __( 'View Item', 'forms_s' ),
		'add_new_item'        => __( 'Add message', 'forms_s' ),
		'add_new'             => __( 'Add message', 'forms_s' ),
		'edit_item'           => __( 'Edit Item', 'forms_s' ),
		'update_item'         => __( 'Update Item', 'forms_s' ),
		'search_items'        => __( 'Search Item', 'forms_s' ),
		'not_found'           => __( 'Not found', 'forms_s' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'forms_s' ),
	);
	$args = array(
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'page-attributes'),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'menu_position'       => 55,
		'can_export'          => true,
		'has_archive'         => false,
		'query_var'			=> false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
	);
	if (defined ("forms_tmpl_include") && forms_tmpl_include == 1){
		$args['taxonomies']= array( 'form_tag_s' );
	}
	
	register_post_type( 'message_cp', $args );
}
//Метка об отправке на почту
add_filter( 'manage_edit-message_cp_columns', 'set_custom_edit_message_cp_columns' );
add_action( 'manage_message_cp_posts_custom_column' , 'custom_message_cp_column',1,2);

function set_custom_edit_message_cp_columns($columns) {
    $columns['label'] = __( 'Send tag', 'forms_s' );
    return $columns;
}

function custom_message_cp_column( $column, $post_id ) {
    if ($column=='label') {
            $label=get_post_meta($post_id,'email_send',true);
            if ($label=='1')
                echo '<span class="dashicons dashicons-flag"></span>';
            else if($label=='2')
                echo '<span class="dashicons dashicons-yes"></span>';
    }
}

 add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' ); 
 function wpb_adding_scripts() {
//wp_register_script('jquerymask', plugins_url('js/jquery.mask.min.js', __FILE__), array('jquery'),'1.1', true);
//wp_enqueue_script('jquerymask');
}
register_activation_hook(__FILE__, 'activation_form_emailer_cp');
function activation_form_emailer_cp() {
	wp_schedule_event( time(), 'hourly', 'check_new_msg_and_send');
}
register_deactivation_hook(__FILE__, 'deactivation_form_emailer_cp');
function deactivation_form_emailer_cp() {
	wp_clear_scheduled_hook('check_new_msg_and_send');
}
