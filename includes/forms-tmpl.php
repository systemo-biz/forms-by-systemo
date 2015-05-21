<?php
//Шорткод для вывода шаблонов форм
include_once('shortcodes/form-s.php');

function cp_callback_activation() { //hook for components that require activation
    add_post_type_form_tmpl_s();
    registration_form_tag_s_taxonomy();
    //flush_rewrite_rules(); //r    eset rewrite rules to open the URL as follows
}

//регистрируем новый тип поста для хранения шаблонов форм
function add_post_type_form_tmpl_s() {

    $labels = array(
        'name'                => _x( 'Шаблоны форм', 'Post Type General Name', 'text_domain' ),
        'singular_name'       => _x( 'Шаблон формы', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'           => __( 'Шаблоны форм', 'text_domain' ),
        'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
        //'all_items'           => __( 'All Items', 'text_domain' ),
        'view_item'           => __( 'View Item', 'text_domain' ),
        'add_new_item'        => __( 'Add New Item', 'text_domain' ),
        'add_new'             => __( 'Add New', 'text_domain' ),
        'edit_item'           => __( 'Edit Item', 'text_domain' ),
        'update_item'         => __( 'Update Item', 'text_domain' ),
        'search_items'        => __( 'Search Item', 'text_domain' ),
        'not_found'           => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
    );
    $args = array(
        'labels'              => $labels,
        'supports'            => array( 'title','author'/*'comments', 'custom-fields', 'page-attributes', 'post-formats',*/ ),
       // 'taxonomies'          => array('form_tag_s'),//'messages' ),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => 'edit.php?post_type=message_cp',
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => false,
        'menu_position'       => null,
        'can_export'          => true,
        'has_archive'         => false,
        'query_var'			  => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
    );
    register_post_type( 'form_tmpl_s', $args );
}
//удаление уведомлений при добавлении шаблонов
add_filter( 'post_updated_messages', 'delete_notice' ); 
function delete_notice( $messages ) {
    global $post, $post_ID;
    $messages['form_tmpl_s'] = array(0 => '',1 => '',2 => '',3 => '',4 => '',5 => '',6 => '',7 => '',8 => '',9 => '',10 => '');
    return $messages;
}
//регистрация таксономий для шаблонов форм
function registration_form_tag_s_taxonomy(){

    $labels_taxonomy = array(
        'name' => _x( 'form_tag_s', 'taxonomy general name' ),
        'singular_name' => _x( 'form_tag_s', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search form_tag_s' ),
        'popular_items' => __( 'Popular form_tag_s' ),
        'all_items' => __( 'All form_tag_s' ),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Edit form_tag_s' ),
        'update_item' => __( 'Update form_tag_s' ),
        'add_new_item' => __( 'Add form_tag_s' ),
        'new_item_name' => __( 'New form_tag_s' ),
        'separate_items_with_commas' => __( 'Separate form_tag_s with commas' ),
        'add_or_remove_items' => __( 'Add or remove form_tag_s' ),
        'choose_from_most_used' => __( 'Choose from the most used form_tag_s' ),
        'menu_name' => __( 'form-tag-s'),
    );

    register_taxonomy('form_tag_s', array('message_cp'),array(
        'hierarchical' => false,
        'labels' => $labels_taxonomy,
        'show_ui' => false,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'form_tag_s' ),
        'show_in_nav_menus' => false,

    ));

}

//отключение tiny mce
add_filter( 'user_can_richedit', 'disable_for_cpt' );
function disable_for_cpt( $default ) {
    global $post;
    if ( 'form_tmpl_s' == get_post_type( $post ) )
        return false;
    return $default;
}
//создание колонки для вставки кода в админке и удаление лишних колонок
add_filter('manage_edit-form_tmpl_s_columns', 'add_cp_callback_views_column', 4);
function add_cp_callback_views_column( $columns ){
    $columns['paste_code'] = 'Код вставки';
    unset($columns['comments']);
    unset($columns['date']);
    return $columns;
}

//заполнение колонки для данных кода данными
add_filter('manage_form_tmpl_s_posts_custom_column', 'fill_cp_callback_views_column', 5, 2);
function fill_cp_callback_views_column($column_name, $post_id) {
    if ($column_name != 'paste_code') return;
    if ($column_name == 'paste_code') {
        echo '<input type = "text" readonly = "readonly" onfocus="this.select();" value = "[form-s id=&quot;'.$post_id.'&quot;]" >';
    }

}

//создание мета-бокса для вставки кода и заполнение его данными
function cp_callback_meta_boxes() {
    add_meta_box('truediv', 'Код вставки', 'cp_callback_print_box', 'form_tmpl_s', 'side', 'high');
}

add_action( 'add_meta_boxes', 'cp_callback_meta_boxes' );

function cp_callback_print_box($post) {
    echo '<input type = "text" readonly = "readonly" onfocus="this.select();" value = "[form-s id=&quot;'.$post->ID.'&quot;]" >';
}

function template_meta_boxes() {
    add_meta_box('form_template', 'Шаблон формы', 'form_template_print_box', 'form_tmpl_s', 'normal', 'high');
    add_meta_box('notice_template','Шаблон уведомления','notice_template_print_box','form_tmpl_s','normal','high');
}
 
add_action( 'add_meta_boxes', 'template_meta_boxes');
 
function form_template_print_box($post) {
    $content='[form-cp spam_protect=1]
 
[input-cp type=text name="name" placeholder="Имя" meta="Имя"]
 
[input-cp type=text name="tel" placeholder="Телефон" meta="Телефон"]
 
[input-cp type=email name="email" placeholder="Электронная почта" required="true" meta="Электронная почта"]
 
[textarea-cp placeholder=Комментарий name="comment" meta="Комментарий"]
 
[input-cp type=submit class="btn btn-success" value="Отправить" name="submit"]
 
[/form-cp]';
$args = array('media_buttons' => 0);
if($post->post_content==''){
    wp_editor($content,'content',$args);
}
    else{
        wp_editor($post->post_content,'content',$args);
    }
}

function notice_template_print_box($post){
    $content='[[name]]
[[tel]]
[[email]]
[[comment]]';
    $args = array('media_buttons' => 0);
    if(get_post_meta($post->ID,'notice_template',true)==''){
        wp_editor($content,'notice_template',$args);
    }
    else{
        wp_editor(get_post_meta($post->ID,'notice_template',true),'notice_template',$args);
    }
    $emails.='<br><label>Адреса: <input type="text" name="emails" value="'. get_post_meta($post->ID,'emails',true) .'" /></label> ';
    echo $emails;
}

function save_form_tmpl_s_content($post_id){
        update_post_meta($post_id, 'notice_template', $_REQUEST['notice_template']);
        update_post_meta($post_id, 'emails', esc_attr($_POST['emails']));
        remove_action( 'save_post_form_tmpl_s', 'save_form_tmpl_s_content' );
        wp_update_post( array( 'ID' => $post_id, 'post_content' => $_REQUEST['content']));
        add_action( 'save_post_form_tmpl_s', 'save_form_tmpl_s_content' );
}
add_action( 'save_post_form_tmpl_s', 'save_form_tmpl_s_content' );



//удаление пункта таксономии из подменю Сообщения
/*
add_action( 'admin_menu', 'cp_callback_remove_menu_pages' );
function cp_callback_remove_menu_pages() {

        remove_submenu_page( 'edit.php?post_type=message_cp', 'edit-tags.php?taxonomy=form_tag_s&post_type=message_cp' );

}*/