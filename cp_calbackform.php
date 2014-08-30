<?php 
/**
 * Plugin Name: Forms for WordPress by CasePress
 * Description: Calback Form
 * Version: 1.0
 * Author: http://casepress.org
 */


 include_once('includes/emailer.php');
 include_once('includes/spam_protect.php');



////// шорт код вызова формы + обработчик данных из формы
add_shortcode( 'form-cp', 'cpform_func' );
 
function cpform_func( $cp_atts, $content){
	 
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

	error_log(print_r($dara_form, true));
	// Создаем массив
	$cp_post = array(
		'post_title' => $meta_data_form['name_form'],
		'post_type' => 'message_cp',
		'post_content' => print_r($data_form, true),
		'post_author' => 1,
		);

	// Вставляем данные в БД
	$post_id = wp_insert_post( $cp_post );


	//Записываем меты

	foreach($meta_data_form as $key => $value):
		add_post_meta($post_id, 'meta_' . $key, $value);
	endforeach;

	foreach($data_form as $key => $value):
		add_post_meta($post_id, $key, $value);
		$conten_data .= "
			<div>
				<div><strong>" . get_post_meta($post_id, 'meta_'.$key, true) . "</strong></div>".
				"<div>" . get_post_meta($post_id, $key, true) . "</div>
			</div>
			<hr/>";
	endforeach;




	$post_data = array(
		'ID' => $post_id, 
		'post_content' => $conten_data,
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
		'all_items'           => __( 'All Items', 'text_domain' ),
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
		'taxonomies'          => array( 'messages' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 55,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
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