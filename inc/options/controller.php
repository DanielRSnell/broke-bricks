<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

require get_stylesheet_directory() . '/inc/options/dequeue/controller.php';

add_action('carbon_fields_register_fields', 'crb_attach_theme_options');
function crb_attach_theme_options()
{
    $container = Container::make('theme_options', __('Broke Options', 'broke-bricks'))
        ->set_page_parent('bricks'); // Assuming 'bricks' is a valid parent page slug

    // Default landing tab with header and explainer text
    $container->add_tab(__('Default', 'broke-bricks'), array(
        Field::make('html', 'crb_information_text')
            ->set_html(adminRender('options/admin.twig')),
    ));

    // Libraries Tab
    $container->add_tab(__('Libraries', 'broke-bricks'), crb_generate_library_fields());

    // Features Tab with Tailwind mode option
    $container->add_tab(__('Features', 'broke-bricks'), array(
        Field::make('html', 'crb_features_info')
            ->set_html('<p>Configure feature settings below.</p>'),

        // Tailwind Mode Option
        Field::make('separator', 'crb_tailwind_mode_divider'),
        Field::make('html', 'crb_tailwind_mode_html')
            ->set_html('<p>Enable Tailwind CSS mode to remove all default Bricks styling, optimizing for Tailwind CSS.</p>'),
        Field::make('checkbox', 'crb_tailwind_mode', __('Tailwind Mode', 'broke-bricks')),

        // Remove Bricks JS Option
        Field::make('separator', 'crb_bricks_js_divider'),
        Field::make('html', 'crb_bricks_js_html')
            ->set_html('<p>Remove the default Bricks JavaScript from the frontend for enhanced performance and custom JS solutions.</p>'),
        Field::make('checkbox', 'crb_bricks_js', __('Remove Bricks JS', 'broke-bricks')),

        // Cwicly Migration Option
        Field::make('separator', 'crb_cwicly_divider'),
        Field::make('html', 'crb_cwicly_html')
            ->set_html('<p>Enable Cwicly migration mode to assist in transitioning projects from Cwicly to Bricks.</p>'),
        Field::make('checkbox', 'crb_cwicly', __('Cwicly Migration', 'broke-bricks')),
    ));

    // Elements Tab with individual checkboxes for each element
    $elements_fields = broke_get_bricks_elements_fields();
    $container->add_tab(__('Elements', 'broke-bricks'), array_merge(array(
        Field::make('html', 'crb_elements_info')
            ->set_html('<p>Select the elements you want to disable:</p>'),
    ), $elements_fields));

}

function crb_generate_library_fields()
{
    $scripts_directory = get_stylesheet_directory() . '/assets/js';
    $subdirectories = glob($scripts_directory . '/*', GLOB_ONLYDIR);

    $fields = array();

    foreach ($subdirectories as $subdirectory) {
        $subdirectory_name = basename($subdirectory);
        $fields[] = Field::make('separator', 'crb_separator_' . $subdirectory_name, __($subdirectory_name, 'broke-bricks'));

        $script_files = glob($subdirectory . '/*.js');
        foreach ($script_files as $script_file) {
            $script_name = basename($script_file, '.js');
            $fields[] = Field::make('checkbox', 'crb_script_' . $subdirectory_name . '_' . $script_name, __($script_name, 'broke-bricks'));
        }
    }

    return $fields;
}

function adminRender($path)
{
    $context = Timber::context();

    $compile = Timber::compile($path, $context);

    return $compile;
}

function broke_get_bricks_elements_fields()
{
    $elements_dir = get_template_directory() . '/includes/elements/';
    $element_files = glob($elements_dir . '*.php');

    $fields = [];

    foreach ($element_files as $file) {
        $element_name = basename($file, '.php');
        $label = ucwords(str_replace('-', ' ', $element_name)); // Convert to a more readable format

        // Add a checkbox field for each element
        $fields[] = Field::make('checkbox', 'crb_element_' . $element_name, $label);
    }

    return $fields; // Make sure to return the fields array
}

add_filter('bricks/builder/elements', function ($elements) {
    // Retrieve all disabled elements from theme options
    $disabled_elements = []; // Initialize as empty array

    // Get the directory where element files are stored
    $elements_dir = get_template_directory() . '/includes/elements/';
    $element_files = glob($elements_dir . '*.php');

    foreach ($element_files as $file) {
        $element_name = basename($file, '.php');

        // Check if the element is disabled in theme options
        if (carbon_get_theme_option('crb_element_' . $element_name)) {
            $disabled_elements[] = $element_name;
        }
    }

    // Filter out the disabled elements
    return array_diff($elements, $disabled_elements);
});
