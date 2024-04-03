<?php

class BrokeScriptsEnqueuer
{
    private $JS_BASE_PATH;
    private $JS_BASE_URL;
    private $alpineCorePath = 'alpine/core.js'; // Relative path to the AlpineJS core script

    public function __construct()
    {
        $this->JS_BASE_PATH = get_stylesheet_directory() . '/assets/js/';
        $this->JS_BASE_URL = get_stylesheet_directory_uri() . '/assets/js/';
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), PHP_INT_MAX); // Use high priority to ensure this runs last
        add_action('init', array($this, 'add_bricks_filter')); // Add this line

    }

    public function enqueue_scripts()
    {
        $subdirectories = glob($this->JS_BASE_PATH . '*', GLOB_ONLYDIR);
        foreach ($subdirectories as $subdirectory) {
            $subdirectory_name = basename($subdirectory);
            $script_files = glob($subdirectory . '/*.js');
            foreach ($script_files as $script_file) {
                $script_name = basename($script_file, '.js');
                $relative_script_path = $subdirectory_name . '/' . $script_name . '.js';

                // Skip the AlpineJS core script in this loop
                if ($relative_script_path == $this->alpineCorePath) {
                    continue;
                }

                $option_name = 'crb_script_' . $subdirectory_name . '_' . $script_name;
                if (carbon_get_theme_option($option_name)) {
                    wp_enqueue_script(
                        $subdirectory_name . '-' . $script_name,
                        $this->JS_BASE_URL . $relative_script_path,
                        array(), // dependencies
                        false, // version
                        true// in_footer
                    );
                }
            }
        }

        // Explicitly enqueue the AlpineJS core script last
        $this->enqueue_alpine_core();
    }

    private function enqueue_alpine_core()
    {
        $alpine_core_option_name = 'crb_script_' . str_replace('/', '_', trim($this->alpineCorePath, '.js'));
        if (carbon_get_theme_option($alpine_core_option_name)) {
            wp_enqueue_script(
                'alpine-core',
                $this->JS_BASE_URL . $this->alpineCorePath,
                array(), // dependencies
                false, // version
                true// in_footer
            );
        }
    }

    // Method to check if a specific script is enabled
    public function add_bricks_filter()
    {
        if ($this->is_script_enabled('alpine/core.js')) {
            add_filter('bricks/content/attributes', function ($attributes) {
                // Check if 'x-data' attribute already exists
                if (isset($attributes['x-data'])) {
                    // If 'x-data' is already set, append 'app()' to its value
                    $attributes['x-data'] .= ' broke()';
                } else {
                    // If 'x-data' is not set, initialize it with 'broke()'
                    $attributes['x-data'] = 'broke()';
                }

                // Check if 'x-init' attribute already exists
                if (isset($attributes['x-init'])) {
                    // If 'x-init' is already set, append 'init()' to its value
                    $attributes['x-init'] .= ' init()';
                } else {
                    // If 'x-init' is not set, initialize it with 'init()'
                    $attributes['x-init'] = 'init()';
                }

                return $attributes;
            });
        }
    }

    public function is_script_enabled($script_relative_path)
    {
        list($directory, $filename) = explode('/', $script_relative_path);
        $option_name = 'crb_script_' . $directory . '_' . basename($filename, '.js');
        return carbon_get_theme_option($option_name);
    }
}

// Instantiate the script enqueuer
$brokestrap = new BrokeScriptsEnqueuer();

function broke_is_script_enabled($script_relative_path)
{
    global $brokestrap;
    return $brokestrap->is_script_enabled($script_relative_path);
}
