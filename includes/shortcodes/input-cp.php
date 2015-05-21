<?php
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