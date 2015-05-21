<?php
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
        'post_status'   => 'publish',
		'post_content' => print_r($data_form, true),
		'post_author' => 1,
		);
	// Вставляем данные в БД
	$post_id = wp_insert_post( $cp_post );
	//Метка отправки на почту
	add_post_meta($post_id, 'email_send', '1');
	// Присваиваем id поста-шаблона формы как термин таксономии текущему посту-сообщению
	if (defined ("forms_tmpl_include") && forms_tmpl_include == 1) {
		$parent_post_name = strval($_REQUEST['meta_data_form_cp']['parent_post_id']);
		wp_set_object_terms($post_id, $parent_post_name, 'form_tag_s', true);
		$template_post_id= intval($_REQUEST['meta_data_form_cp']['template_post_id']);
	}
	//Записываем меты
	foreach($meta_data_form as $key => $value):
		add_post_meta($post_id, 'meta_' . $key, $value);
	endforeach;
    
	$content_data = '';
	//Шаблон уведомления
	$notice_template_data=get_post_meta($template_post_id,'notice_template', true);
	foreach($data_form as $key => $value):
		add_post_meta($post_id, $key, $value);
	    if(empty($notice_template_data)){
	    	$content_data .= "
			<div>
				<div><strong>" . get_post_meta($post_id, 'meta_'.$key, true) . "</strong></div>".
				"<div>" . get_post_meta($post_id, $key, true) . "</div>
			</div>
			<hr/>";
	    }
	endforeach;
    //Заполнение по шаблону уведомления
    if(preg_match_all('|\[\[(.+)\]\]|isU',$notice_template_data,$arr)){
		for ($i=0; $i < count($arr[0]) ; $i++) { 
			$notice_template_data=str_replace($arr[0][$i], get_post_meta($post_id,$arr[1][$i],true), $notice_template_data);
		}
	}
	$content_data.=$notice_template_data;

	$post_data = array(
		'ID' => $post_id, 
		'post_content' => $content_data,
		);
	wp_update_post( $post_data );
}