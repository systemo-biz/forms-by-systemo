<?php
/*
Plugin Name: Forms for WordPress by CasePress
Description: Calback Form
Version: 20141021.2
GitHub Plugin https://github.com/systemo-biz/forms-cp
GitHub Branch: master
Author: http://casepress.org
*/


include_once('includes/emailer.php');
include_once('includes/spam_protect.php');

add_action('init', 'cp_callback_activation');
register_activation_hook( __FILE__, 'cp_callback_activation' );

function cp_callback_activation() { //hook for components that require activation

	add_post_type_form_tmpl_s();
	registration_form_tag_s_taxonomy();
	flush_rewrite_rules(); //reset rewrite rules to open the URL as follows
}

////// шорт код вызова формы + обработчик данных из формы
add_shortcode( 'form-cp', 'cpform_func' );

function cpform_func( $cp_atts, $content){
	global $post;
	if(isset($_REQUEST['data_form_cp'])) return apply_filters('msg_senf_form_cp', '<p>Сообщение отправлено.</p>');

	extract(shortcode_atts( array(
		'method'		=> 'post', // get or post
		'titlepost'		=> '',
		'messagesend'   => 'post',
		'name_form'		=> 'Сообщение с сайта',
		'style'			=> '',
		'email_to'		=> true,
		'spam_protect'	=> '',
	), $cp_atts, 'form-cp' ));

	if($email_to) $email_to = get_bloginfo('admin_email');;

	$spam_protect_html = get_spam_protect_html($spam_protect);

	ob_start();

	?>

	<div class="form_wrapper_external_cp" style='<?php echo $style; ?>'>
		<form method="<?php echo $method; ?>" >
			<?php echo do_shortcode($content); ?>
			<input type="hidden" value="<?php echo $name_form ?>" name="meta_data_form_cp[name_form]">
			<input type="hidden" value="<?php echo $email_to ?>" name="meta_data_form_cp[email_to]">
			<input type="hidden" value="<?php echo $GLOBALS['post_id_for_taxonomy'] ?>" name="meta_data_form_cp[parent_post_id]">
			<?php echo $spam_protect_html; ?>
		</form>
	</div>

	<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}


add_shortcode( 'textarea-cp', 'textarea_callback_cp' );

function textarea_callback_cp( $cp_atts ){
	extract(shortcode_atts(array(
		'name'          => '',
		'class'         => '',
		'id'			=> '',
		'label'			=> '',
		'value' 		=> '',
		'placeholder'	=> '',
		'cols'			=> '25',
		'rows'			=> '5',
		'required'      => false,
	), $cp_atts, 'input-cp' ));

	ob_start();
	?>

	<div class="input_cp textarea_cp <?php if(!empty($name)){  echo $name;  } ?>">
		<?php if(!empty($label)){ ?>
			<label for='<?php echo $id; ?>'><?php echo $label; ?></label>
		<?php } ?>
		<textarea
			<?php

			if(!empty($value)) echo 'value="' . $value . '"';

			if(!empty($cols)) echo 'cols="' . $cols . '"';

			if(!empty($rows)) echo 'rows="' . $rows . '"';

			if(!empty($name)) echo 'name="data_form_cp[' . $name . ']"';

			if(!empty($class)) echo 'class="' . $class . '"';

			if(!empty($id)) echo 'id="' . $id . '"';

			if(!empty($placeholder)) echo 'placeholder="' . $placeholder . '"';

			if($required == 'true') echo 'required="required"';
			?>
			></textarea>

		<input type="hidden" class="metadata" name='meta_data_form_cp[<?php echo $name; ?>]' value='<?php echo $meta ?>'>
	</div>

	<?php

	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}


///////////////////////////////////////////// шорткод input'а
add_shortcode( 'input-cp', 'cpcallbackform_func' );

function cpcallbackform_func( $cp_atts ){
	extract(shortcode_atts(array(
		'type'          => 'text',
		'name'          => '',
		'class'        	=> '',
		'id'           	=> '',
		'size'			=> '25',
		'label'     	=> '',
		'value'        	=> '',
		'placeholder' 	=> '',
		'meta'			=> '',
		'required'      => false,
	), $cp_atts, 'input-cp' ));

	ob_start();
	?>

	<div class="input_cp <?php if(!empty($name)){  echo $name;  } ?>">
		<?php if(!empty($label)){ ?>
			<label for='<?php echo $id; ?>'><?php echo $label; ?></label>
		<?php } ?>
		<input
			<?php
			if(!empty($type)) echo 'type="' . $type . '"';

			if(!empty($value)) echo 'value="' . $value . '"';

			if(!empty($size)) echo 'size="' . $size . '"';

			if(!empty($name)) {

				//Если это кнопка, то записываем в массив метаданных формы, если это обычное поле, то в массив данных.
				if($type == 'submit') {
					echo 'name="meta_data_form_cp[' . $name . ']"';
				} else {
					echo 'name="data_form_cp[' . $name . ']"';
				}
			}

			if(!empty($class)) echo 'class="' . $class . '"';

			if(!empty($id)) echo 'id="' . $id . '"';

			if(!empty($placeholder)) echo 'placeholder="' . $placeholder . '"';

			if($required == 'true') echo 'required="required"';
			?>
			/>
		<input type="hidden" class="metadata" name='meta_data_form_cp[<?php echo $name; ?>]' value='<?php echo $meta; ?>'>
	</div>

	<?php

	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}


//Добавляем данные в пост
add_action('init', 'add_message_to_posts');
function add_message_to_posts(){

	// проверяем пустая ли data_cp
	if(empty($_REQUEST['data_form_cp'])) return;

	$data_form = $_REQUEST['data_form_cp']; // если не пустая то записываем значения для  проверки существованя
	$meta_data_form = $_REQUEST['meta_data_form_cp']; // если не пустая то записываем значения для  проверки существованя

	//error_log(print_r($dara_form, true));
	// Создаем массив
	$cp_post = array(
		'post_title' => $meta_data_form['name_form'],
		'post_type' => 'message_cp',
		'post_content' => print_r($data_form, true),
		'post_author' => 1,
	);

	// Вставляем данные в БД
	$post_id = wp_insert_post( $cp_post );

	// Присваиваем id поста-шаблона формы как термин таксономии текущему посту-сообщению
	wp_set_object_terms( $post_id, $_REQUEST['meta_data_form_cp']['parent_post_id'], 'form_tag_s',true);


	//Записываем меты
	foreach($meta_data_form as $key => $value):
		add_post_meta($post_id, 'meta_' . $key, $value);
	endforeach;
	$content_data = null;
	foreach($data_form as $key => $value):
		add_post_meta($post_id, $key, $value);
		$content_data .= "
			<div>
				<div><strong>" . get_post_meta($post_id, 'meta_'.$key, true) . "</strong></div>".
			"<div>" . get_post_meta($post_id, $key, true) . "</div>
			</div>
			<hr/>";
	endforeach;


	$post_data = array(
		'ID' => $post_id,
		'post_content' => $content_data,
	);

	wp_update_post( $post_data );

}


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
		'supports'            => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'page-attributes', 'post-formats', ),
		'taxonomies'          => array( 'form_tag_s' ),
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
	register_post_type( 'message_cp', $args );

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


//----------------------------------------------------

//регистрируем новый тип поста для хранения шаблонов форм
//add_action( 'init', 'add_post_type_form_tmpl_s' );
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
		'taxonomies'          => array('form_tag_s'),//'messages' ),
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
//add_action('init', 'registration_form_tag_s_taxonomy');
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

	register_taxonomy('form_tag_s', array('message_cp','form_tmpl_s'),array(
		'hierarchical' => false,
		'labels' => $labels_taxonomy,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'form_tag_s' ),
		//'show_in_nav_menu' => 'locations',
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

//custom list of all location (address)
//create new column
add_filter('manage_edit-form_tmpl_s_columns', 'add_cp_callback_views_column', 4);
function add_cp_callback_views_column( $columns ){
	$columns['paste_code'] = 'Код вставки';
	unset($columns['comments']);
	unset($columns['date']);
	return $columns;
}
//fill column data
add_filter('manage_form_tmpl_s_posts_custom_column', 'fill_cp_callback_views_column', 5, 2);
function fill_cp_callback_views_column($column_name, $post_id) {
	if ($column_name != 'paste_code') return;
	if ($column_name == 'paste_code') {
		echo '<input type = "text" readonly = "readonly" onfocus="this.select();" value = "[form-s id=&quot;'.$post_id.'&quot;]" >';
	}

}


function cp_callback_meta_boxes() {
	add_meta_box('truediv', 'Код вставки', 'cp_callback_print_box', 'form_tmpl_s', 'side', 'high');
}

add_action( 'add_meta_boxes', 'cp_callback_meta_boxes' );


function cp_callback_print_box($post) {
	echo '<input type = "text" readonly = "readonly" onfocus="this.select();" value = "[form-s id=&quot;'.$post->ID.'&quot;]" >';
}

?>
