<?php

add_action('wp_enqueue_scripts', function () {
    if (carbon_get_theme_option('crb_cwicly')) {
        // Enqueue the CSS file
        wp_enqueue_style('cwicly-core-css', get_stylesheet_directory_uri() . '/inc/options/cwicly/core.css', array(), null);

        // Enqueue the JS file
        wp_enqueue_script('cwicly-core-js', get_stylesheet_directory_uri() . '/inc/options/cwicly/core.js', array(), null, true);
    }
});
