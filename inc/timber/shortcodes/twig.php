<?php

function twig_shortcode($atts, $content = null)
{
    global $post;

    $context = Timber::context();
    $context['attributes'] = $atts;
    $context['post'] = $post;
    $context['state'] = $context;

    // Compile the content as a Twig template
    $compiled_content = Timber::compile_string($content, $context);

    return $compiled_content;

}

add_shortcode('twig', 'twig_shortcode');
