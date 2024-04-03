<?php

require get_stylesheet_directory() . '/inc/forms/redirect/controller.php';

add_filter('gform_disable_form_theme_css', '__return_true');

add_filter('bricks/frontend/render_data', function ($content, $post, $area) {
    // Patterns and replacements for adjusting quotes next to Twig syntax
    $patterns = ['/\"\{\{/', '/\}\}\"/', '/\"\{\%/', '/\%\}\"/'];
    $replacements = ['{{', '}}', '{%', '%}'];

    // Apply replacements to content
    $content = preg_replace($patterns, $replacements, $content);

    $context = Timber::context();
    $context['area'] = $area;

    // Compile the content as a Twig template with the context
    $content = Timber::compile_string($content, $context);

    return $content;
}, 10, 3);

function convertJSON($data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return $json;
}
