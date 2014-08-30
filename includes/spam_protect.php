<?php

/*
Получает код для защиты формы от спама

Если произошел клик по кнопке, то считаем что это человек и заполняем скрытое поле.

Затем функция проверит наличие этого поля и если оно есть, то пропускаем, иначе помечаем как спам.
*/
function get_spam_protect_html($type_spam_protect){

	if(empty($type_spam_protect)) return;

	ob_start();
	?>
	<input type="hidden" class="spam_protect" name="meta_data_form_cp[spam_protect]">
	<script type="text/javascript">
		(function($) {
			$("input[type='submit']").click(function () {
      			$('.spam_protect').val('hs');
    		});
		})(jQuery);
	</script>
	<?php
	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

add_action('added_post_meta', 'check_spam_form_cp', 10, 4);
add_action('updated_post_meta', 'check_spam_form_cp', 10, 4);
function check_spam_form_cp($mid, $object_id, $meta_key, $meta_value) {
	
	//проверяем чтобы это была мета которая содержит отметку о спаме
	if($meta_key != 'meta_spam_protect') return;

	if($meta_value != 'hs') {
		wp_trash_post($object_id);
	}

}