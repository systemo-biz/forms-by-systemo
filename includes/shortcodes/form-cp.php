<?php
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
		'class' => '',
		'id' => '',
	), $cp_atts, 'form-cp' ));
  	if($email_to){
  		$meta_emails=get_post_meta($GLOBALS['template_post_id'],'emails',true);
  		if(!empty($meta_emails)){
  			$email_to=$meta_emails;
  		}
  		else{
  			$email_to = get_bloginfo('admin_email');
  		}
	}
  	$spam_protect_html = get_spam_protect_html($spam_protect);
 	ob_start();
 	?>

	<div class="form_wrapper_external_cp" style='<?php echo $style; ?>'>
		<form method="<?php echo $method; ?>" class="s_form <?php echo $class;?>" <?php if($id){echo "id='$id'";}?>>
			<?php echo do_shortcode($content); ?>
			<input type="hidden" value="<?php echo $name_form ?>" name="meta_data_form_cp[name_form]">
			<input type="hidden" value="<?php echo $email_to ?>" name="meta_data_form_cp[email_to]">
			<?php if (defined ("forms_tmpl_include") && forms_tmpl_include == 1):?>
				<input type="hidden" value="<?php echo $GLOBALS['post_id_for_taxonomy'] ?>" name="meta_data_form_cp[parent_post_id]">
				<input type="hidden" value="<?php echo $GLOBALS['template_post_id']; ?>" name="meta_data_form_cp[template_post_id]">
			<?php endif;?>
			<?php echo $spam_protect_html; ?>
		</form>
	</div>
    
	<?php
	$html = ob_get_contents();
    ob_end_clean();
	return $html;
 }