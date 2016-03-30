<?php
/* ниже представлен код добавления метабокса с настройками к шаблону формы,
код может принимать любое колличество настроек,
нужно записывать параметры в массив s_options_form
Виталий Макс vm@casepress.org 15.03.2016 */
////////////// BEGIN ////////////////////////
// подключаем функцию активации мета блока (s_option_fields)
add_action('add_meta_boxes', 's_option_fields', 1);

function s_option_fields() {
	add_meta_box( 'option_fields', 'Настройки', 'option_fields_box_func', 'form_tmpl_s', 'normal', 'high'  );
}

// код блока
function option_fields_box_func( $post ){
	?>
	<p>
		<input type="hidden" name="s_options_form[s_utm_m]" value=""> <!-- массив обязательно должен быть определен, а чек бокс не передаст ничего если он не отмечен, для этого и нужно скрытое пустое поле -->
		<label><input type="checkbox" name="s_options_form[s_utm_m]" value="1" <?php checked( get_post_meta($post->ID, 's_utm_m', 1), 1 )?> /> Записывать UTM метки?</label>
	</p>
	<p>
		<input type="hidden" name="s_options_form[s_ajax]" value=""> <!-- массив обязательно должен быть определен, а чек бокс не передаст ничего если он не отмечен, для этого и нужно скрытое пустое поле -->
		<label><input type="checkbox" name="s_options_form[s_ajax]" value="1" <?php checked( get_post_meta($post->ID, 's_ajax', 1), 1 )?> /> Использовать Ajax?</label>
	</p>
	<p>
		<label>Сообщение о успешной отправке формы (Для Ajax):<input type="text" name="s_options_form[s_ajax_confirm]" value="<?php echo get_post_meta($post->ID, 's_ajax_confirm', 1); ?>" style="width:50%" /></label>
	</p>
	<!-- Здесь при необходимости добавляем новые опции. Имя поля должно быть формата s_options_form[название_метаполя] на этом все, новая опция будет записанна в нужную мету -->
	<input type="hidden" name="option_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}

// включаем обновление полей при сохранении
add_action('save_post', 's_option_fields_update', 0);

/* Сохраняем данные, при сохранении поста */
function s_option_fields_update( $post_id ){
	if ( !wp_verify_nonce($_POST['option_fields_nonce'], __FILE__) ) return false; // проверка
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // если это автосохранение
	if ( !current_user_can('edit_post', $post_id) ) return false; // если юзер не имеет право редактировать запись

	if( !isset($_POST['s_options_form']) ) return false; // если данных нет

	// Все ОК! Теперь, нужно сохранить/удалить данные
	$_POST['s_options_form'] = array_map('trim', $_POST['s_options_form']);
	foreach( $_POST['s_options_form'] as $key=>$value ){
		if( empty($value) ){
			delete_post_meta($post_id, $key); // удаляем поле если значение пустое
			continue;
		}

		update_post_meta($post_id, $key, $value); // записываем новое значение
	}
	return $post_id;
}
/////////////////////////////// END////////////////////////