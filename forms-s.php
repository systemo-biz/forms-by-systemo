<?php 
/*
Plugin Name: Forms by Systemo
Description: Calback Form
Version: 20150502
GitHub Plugin https://github.com/systemo-biz/forms-cp
GitHub Branch: master
Author: http://systemo.org
*/
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
		'name'                => _x( 'Сообщения', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Сообщение', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Сообщения', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
		'all_items'           => __( 'Все сообщения', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Добавить сообщение', 'text_domain' ),
		'add_new'             => __( 'Добавить сообщение', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
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
    $columns['label'] = __( 'Метка отправки', '' );
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
