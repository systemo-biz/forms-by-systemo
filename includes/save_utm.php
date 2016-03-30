<?php
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