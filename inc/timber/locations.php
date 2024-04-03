<?php

add_filter('timber/locations', function ($paths) {
    $theme_directory = get_stylesheet_directory();

    // Example for adding other specific paths
    $paths['helper'] = [$theme_directory . '/inc/helpers'];

    return $paths;
});
