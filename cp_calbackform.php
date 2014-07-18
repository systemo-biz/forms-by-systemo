<?php 
/**
 * Plugin Name: CasePress Calback Form
 * Description: Calback Form
 * Version: 1.0
 * Author: http://casepress.org
 */
 
 function wpb_adding_scripts() {

wp_register_script('jquerymask', plugins_url('js/jquery.mask.min.js', __FILE__), array('jquery'),'1.1', true);

wp_enqueue_script('jquerymask');

}

add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' ); 

 
//////////// CasePress форма

//регистрируем новый тип поста
function cp_post_type() {

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
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'cp_message', $args );

}

add_action( 'init', 'cp_post_type', 0 );

////// шорт код вызова формы ++ обработчик данных из формы
 add_shortcode( 'form-cp', 'cpform_func' );
 
 function cpform_func( $cp_atts, $content){
	 
	  $cp_atts = shortcode_atts( array(
		'method'		 => '',
		'titlepost'		 => '',
		'messagesend'    => 'email',
		), $cp_atts, 'form-cp' );
	
	$cpmethod = $cp_atts['method'];
	
	$cptitlepost = $cp_atts['titlepost'];
	
	$cpmessagesend = $cp_atts['messagesend'];
	
	 ob_start(); ?>
     
	
		<form method="<?php echo $cpmethod; ?>" >
    		<?php echo do_shortcode($content); ?>
    
   		</form>
        
          <script>
      $(document).ready(function(){
  $('.tel').mask('+9(000)000 00-00');
  
  
  jQuery("#customer_phone").click(function () {
      jQuery("#customer_phone").attr("value", "+7");
    });
           });
		
  
                </script>
    
	<?php
	 	
	$cp_ret = ob_get_contents();
    ob_end_clean();
   /// return $cp_ret;   вызов формы
   
    if(isset($_GET['activ'])){
		$cp_activ = $_GET['activ'];
		activmail_cp($cp_activ);
		
	}

	// проверяем пустая ли data_cp
	if(isset($_REQUEST['data_cp'])){
		
		$cp_array = $_REQUEST['data_cp']; // если не пустая то записываем значения для  проверки существованя 

		foreach($cp_array as $key => $e){		
			
			if(empty($e)) return $cp_ret; //если хоть одно поле пустое, запись не будет сохранена в БД
			
			if($key == $cptitlepost){
				
				$cp_verificated_key = $key;
				$cp_verificated_e = $e;
		
			}
		}
				
 		if(empty($cp_verificated_key)) $cp_verificated_key = '';
		
		if(empty($cp_verificated_e)) $cp_verificated_e = '';
	
		//проверка на существование записи
	
		if(verefication_cp($cp_verificated_key, $cp_verificated_e)){
			
			// Создаем массив
 		 	$cp_post = array(
				'post_title' => 'Заголовок записи',
				'post_type' => 'cp_message',
				'post_content' => 'Здесь должен быть контент (текст) записи.',
				'post_status' => 'draft',
				'post_author' => 1,
 			 );

		// Вставляем данные в БД
 			$post_id = wp_insert_post( $cp_post );

			foreach($cp_array as $key => $e){
			
				add_post_meta($post_id, $key, $e);
			
				if($key == $cptitlepost){
					
				//Создаем хеш для проверки Email'a письмом
					$cp_hash = hash('ripemd160', $e);
					add_post_meta($post_id, 'cp_hash', $cp_hash);
					
				// убираем маску с телефона и обновляем мету и записываем его в титле поста
					
					$cp_e = preg_replace('/[^0-9]/', '', $e);
					update_post_meta($post_id, $cptitlepost, $cp_e);
				// Создаем массив данных
					$cp_uppost = array();
					$cp_uppost['ID'] = $post_id;
					$cp_uppost['post_title'] = $cp_e;
				
				// Обновляем данные в БД
  					wp_update_post( $cp_uppost );
		
				}
			}
			add_post_meta($post_id, 'cp_verificate', '0');
			$cp_email = get_post_meta($post_id, 'email', true);
			wp_mail($cp_email, 'club-lord.ru', 'Здравствуйте, подтвердите ваш Email по ссылке '.$_SERVER["REQUEST_URI"].'?activ='.$cp_hash);
		}
	}
	
	
	return $cp_ret;
	
 }
 
 
 ///////////////////////////////////////////// шорткод input'а
 add_shortcode( 'input-cp', 'cpcallbackform_func' );
 
 function cpcallbackform_func( $cp_atts ){
	 ob_start(); 
 		$cp_atts = shortcode_atts( array(
		'type'                        => 'text',
		'name'                        => 'text',
		'class'                       => '',
		'id'            		   	  => '',
		'label'            		      => '',
		'value'            		      => '',
		'placeholder'                 => '',
		'required'                    => false,
	  ), $cp_atts, 'input-cp' );
	
	$cptype = $cp_atts['type'];
	
	$cpname = $cp_atts['name'];
	
	$cpclass = $cp_atts['class'];
	
	$cpid = $cp_atts['id'];
	
	$cplabelname = $cp_atts['label'];
	
	$cpvalue = $cp_atts['value'];
	
	$cpplaceholder = $cp_atts['placeholder'];
	
	$cprequired = $cp_atts['required'];
	
	?>
        
    <div class="input_cp <?php if(!empty($cpname)){  echo $cpname;  } ?>"><?php if(!empty($cplabelname)){ ?><label for="<?php echo $cpid; ?>"><?php echo $cplabelname; ?></label>
     <?php } ?>
            	
    <input 
		
		<?php if(!empty($cptype)){ ?> type="<?php echo $cptype; ?>" <?php } ?> 
        
		<?php if(!empty($cpvalue)){ ?> value="<?php echo $cpvalue; ?>" <?php } ?> 
        
		<?php if($cptype != 'submit'){ ?> name="data_cp[<?php echo $cpname; ?>]" <?php } ?> 
        
		<?php if(!empty($cpclass)){ ?> class="<?php echo $cpclass; ?>" <?php } ?> 
        
		<?php if(!empty($cpid)){ ?> id="<?php echo $cpid; ?>" <?php } ?>
         
        <?php if(!empty($cpplaceholder)){ ?> placeholder="<?php echo $cpplaceholder; ?>" <?php } ?>
        
        <?php if($cprequired == 'true'){ ?> required="required" <?php } ?> ></div>
    
    <?php
  
 	$cp_ret = ob_get_contents();
    ob_end_clean();
    return $cp_ret;
 }
 
 /// функция проверки существования записей по ключу
 
 function verefication_cp($cp_mkey, $cp_mvalue) {
	 
	if(empty($cp_mvalue)) return true;
	 
	$cp_args = array( 
			'meta_key'        => $cp_mkey,
			'meta_value'      => $cp_mvalue,
			'post_type'       => 'cp_message', 
			'post_status'     => 'any'
		);
	$cp_postsv = get_posts( $cp_args ); 
	
	if(!empty($cp_postsv)) {
		return false;
	}else{
		return true;
	}
		
	 
 }
 
 /// Функция подтверждения email по ссылке из письма
 
 function activmail_cp($cp_activ) {
	 
	$cp_args = array( 
			'meta_key'        => 'cp_hash',
			'meta_value'      => $cp_activ,
			'post_type'       => 'cp_message', 
			'post_status'     => 'any'
		);
	$cp_postsv = get_posts( $cp_args ); 
	
	if(!empty($cp_postsv)) {
		foreach( $cp_postsv as $post ){
			
			update_post_meta($post->ID, 'cp_verificate', '1');
		}
		
	}
		
	 
 }