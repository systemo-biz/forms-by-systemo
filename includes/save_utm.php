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
		<input type="hidden" name="s_options_form[s_utm_m]" value=""> <!-- массив обязательно должен быть определен, а чек бокс не передаст ничего если он не отмечен -->
		<label><input type="checkbox" name="s_options_form[s_utm_m]" value="1" <?php checked( get_post_meta($post->ID, 's_utm_m', 1), 1 )?> /> Записывать UTM метки?</label>
	</p>

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

//запись меток в cookie
add_action('init', 'set_utm_tocookie');
function set_utm_tocookie($get_s) {
	foreach ($_GET as $key => $value) {
		$utm_s = strpos($key, 'utm');
		$gclid_s = strpos($key, 'gclid');
		if (!($utm_s === false)) {
		    setcookie( $key, $value, time()+7*24*60*60, COOKIEPATH, COOKIE_DOMAIN );
		}
		if (!($gclid_s === false)) {
		    setcookie( $key, $value, time()+7*24*60*60, COOKIEPATH, COOKIE_DOMAIN );
		}
	}
	return;
}
//записываем utm метки в мету поста если это включенно в настройках шаблона
add_action('save_post', 'set_meta_utm_s_to_massage');
function set_meta_utm_s_to_massage($post_id){
	//проверка на тип поста
	if (!(get_post_type( $post_id ) == 'message_cp')) return;
	//узнаем ID формы
	$template_post_id = get_post_meta($post_id, 'meta_template_post_id', 1);
	//записываем utm метки в мету поста если это включенно в настройках шаблона
	if (get_post_meta($template_post_id, 's_utm_m', 1) == 1) {
		set_meta_utm_s($_COOKIE, $post_id);
	}
	return;
}

// функция записи UTM меток в мету поста
function set_meta_utm_s($cookie_s, $post_id) {
	foreach ($cookie_s as $key => $value) {
		$utm_s = strpos($key, 'utm');
		$gclid_s = strpos($key, 'gclid');
		if (!($utm_s === false)) {
			/// хук для меток
			$s_value_utm = apply_filters( 's_value_utm', $value, $key );
		    add_post_meta($post_id, 'meta_' . $key, $s_value_utm);
		}
		if (!($gclid_s === false)) {
			// хук для gclid
			$s_value_gclid = apply_filters( 's_value_gclid', $value );
		    add_post_meta($post_id, 'meta_' . $key, $s_value_gclid);
		}
	}
	return;
}