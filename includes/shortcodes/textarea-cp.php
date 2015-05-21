<?php
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
 