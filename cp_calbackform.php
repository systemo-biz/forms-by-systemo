<?php 
/**
 * Plugin Name: CasePress Calback Form
 * Description: filter post
 * Version: 1.0
 * Author: http://casepress.org
 */
 
 // Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                => 'Сообщения',
		'singular_name'       => 'Сообщение',
		'menu_name'           => 'Post Type',
		'parent_item_colon'   => 'Parent Item:',
		'all_items'           => 'All Items',
		'view_item'           => 'View Item',
		'add_new_item'        => 'Add New Item',
		'add_new'             => 'Add New',
		'edit_item'           => 'Edit Item',
		'update_item'         => 'Update Item',
		'search_items'        => 'Search Item',
		'not_found'           => 'Not found',
		'not_found_in_trash'  => 'Not found in Trash',
	);
	$args = array(
		'label'               => 'cp_message',
		'description'         => 'Сообщения из формы',
		'labels'              => $labels,
		'supports'            => array( ),
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

// Hook into the 'init' action
add_action( 'init', 'custom_post_type', 0 );
 
 
 
 add_shortcode( 'form-cp', 'cpform_func' );
 
 function cpform_func( $cp_atts, $content){
	 
	 if(isset($_REQUEST['data_cp'])){
	 
 		$cp_array = $_REQUEST['data_cp'];

		foreach($cp_array as $e){
			
				echo $e.'<br>';
			
			}

	 } else {
	 
	ob_start(); 
 		$cp_atts = shortcode_atts( array(
		'method'		 => '',
		), $cp_atts, 'form-cp' );
	
	$cpmethod = $cp_atts['method'];
	
	?>
    
    <form method="<?php echo $cpmethod; ?>" >
    <?php echo do_shortcode($content); ?>
    
   </form>
    
	<?php
 
	 $cp_ret = ob_get_contents();
    ob_end_clean();
    return $cp_ret;
	
	 }
 }

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
	  ), $cp_atts, 'input-cp' );
	
	$cptype = $cp_atts['type'];
	
	$cpname = $cp_atts['name'];
	
	$cpclass = $cp_atts['class'];
	
	$cpid = $cp_atts['id'];
	
	$cplabelname = $cp_atts['label'];
	
	$cpvalue = $cp_atts['value'];
	
	?>
        
    			<div class="input_cp <?php if(!empty($cpname)){  echo $cpname;  } ?>"><?php if(!empty($cplabelname)){ ?><label for="<?php echo $cpid; ?>"><?php echo $cplabelname; ?></label>
     <?php } ?>
            	
    <input type="<?php echo $cptype; ?>" 
	<?php if(!empty($cpvalue)){ ?> value="<?php echo $cpvalue; ?>" <?php } ?> 
	<?php if(!empty($cpname)){ ?> name="data_cp[<?php echo $cpname; ?>]" <?php } ?> 
	<?php if(!empty($cpclass)){ ?> class="<?php echo $cpclass; ?>" <?php } ?> 
	<?php if(!empty($cplabelname)){ ?> id="<?php echo $cpid; ?>" <?php } ?>
    ></div>
    
    <?php
  
 	$cp_ret = ob_get_contents();
    ob_end_clean();
    return $cp_ret;
 }