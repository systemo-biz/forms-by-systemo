<?php
add_shortcode('form-s', 'form_s_shortcode' );

function form_s_shortcode( $atts ){

    if ( empty($atts) ) return;
    ob_start();
    extract( shortcode_atts( array(
        'id' => ''
    ), $atts ) );

    $post = get_post($id);

    if ( is_object($post) && $post->post_type == 'form_tmpl_s'){

        $post_id_for_taxonomy = $post->post_title;
        $GLOBALS['post_id_for_taxonomy'] = $post_id_for_taxonomy;
        $template_post_id = $post->ID;
        $GLOBALS['template_post_id'] = $template_post_id;
        echo do_shortcode($post->post_content);

    }else{
        echo 'указан неверный ID шаблона формы';
    }

    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;

}

