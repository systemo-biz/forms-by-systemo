<?php


function cp_callback_activation() { //hook for components that require activation
    add_post_type_form_tmpl_s();
    registration_form_tag_s_taxonomy();
    flush_rewrite_rules(); //reset rewrite rules to open the URL as follows
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
        'supports'            => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'page-attributes', 'post-formats', ),
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
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'form_tag_s' ),

    ));

}




//отключение tiny mce
add_filter( 'admin_footer', 'disable_tiny_mce', 99);

function disable_tiny_mce(){

    global $parent_file, $pagenow, $self;

    if( strpos( $parent_file, 'edit.php' ) !== 0 ){
        return;
    }

    $post = array();

    if( isset($_GET['post']) ){
        $post['id'] = (int)$_GET['post'];
        $post['type'] = get_post_type( $post['id'] );
    }elseif( isset($_GET['post_type']) ){
        $post['type'] = esc_sql( $_GET['post_type'] );
    }elseif( $parent_file == 'edit.php' && $pagenow == 'post-new.php' ){
        $post['type'] = 'post';
    }

    if( (count($post) == 0) || ($post['type'] != 'form_tmpl_s') ){
        return;
    }

    echo '  <style type="text/css">
				#content-tmce, #content-tmce:hover, #qt_content_fullscreen{
					display:none;
				}
				</style>';
    echo '	<script type="text/javascript">
			 	jQuery(document).ready(function(){
					jQuery("#content-tmce").attr("onclick", null);
			 	});
			 	</script>';

}

//шорткод для вывода шаблонов форм
add_shortcode('form-s', 'form_s_shortcode' );

function form_s_shortcode( $atts ){

    if ( empty($atts) ) return;
    ob_start();
    extract( shortcode_atts( array(
        'id' => ''
    ), $atts ) );

    $post = get_post($id);
    //echo '<pre>';print_r($post);echo '</pre>';
    if ( is_object($post) && $post->post_type == 'form_tmpl_s'){
        //echo '<input type="hidden" value="'.$post->ID.'" name="meta_data_form_cp[form_id_post]">';
        //global $post_id_for_taxonomy;
        $post_id_for_taxonomy = $post->post_title;
        $GLOBALS['post_id_for_taxonomy'] = $post_id_for_taxonomy;
        echo do_shortcode($post->post_content);
        //$post_id_for_taxonomy = $post->ID;

        //echo $_POST['post_id_for_taxonomy'];
        //echo "<pre>";print_r ($post);echo "</pre>";
    }else{
        echo 'указан неверный ID шаблона формы';
    }
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;

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

