<?php

function custom_partial_template_redirect()
{
    // Parse the URL and obtain the path component.
    $requested_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Define the target path/route.
    $form_route = '/form'; // Adjust if your WordPress installation is in a subdirectory

    // Construct the query string from the current request
    $query_string = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

    // Check if the current request is for the "/form" route or if the 'partial' query parameter is set.
    if ($requested_path === $form_route || isset($_GET['partial'])) {
        // Specify the path to the template file within the theme or child theme.
        $template = get_stylesheet_directory() . '/inc/forms/redirect/template.php';

        // Check if the template file exists.
        if (file_exists($template)) {
            // If you wanted to redirect:
            // wp_redirect(get_stylesheet_directory_uri() . '/inc/forms/redirect/template.php' . $query_string);
            // exit;

            // Since you're including, ensure query parameters are available to the template
            include $template;
            exit;
        }
    }
}
add_action('template_redirect', 'custom_partial_template_redirect');
