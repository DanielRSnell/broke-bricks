<?php
// Load Composer dependencies
require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Carbon_Fields;
use Timber\Timber;

class BrokeTheme
{
    private $INC_DIR;
    private $ASSET_DIR;

    public function __construct()
    {
        $this->INC_DIR = get_stylesheet_directory() . '/inc/';
        $this->ASSET_DIR = get_stylesheet_directory() . '/assets/';
    }

    public function init()
    {
        add_action('after_setup_theme', [$this, 'crb_load']);
        Timber::init();
        $this->load_dependencies();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('init', [$this, 'register_custom_elements'], 11);
        add_filter('bricks/builder/i18n', [$this, 'add_text_strings_to_builder']);
    }

    public function crb_load()
    {
        Carbon_Fields::boot();
    }

    private function load_dependencies()
    {
        require $this->INC_DIR . '/options/controller.php';
        require $this->INC_DIR . '/timber/controller.php';
        require $this->ASSET_DIR . 'js/controller.php';

    }

    public function enqueue_scripts()
    {
        if (!bricks_is_builder_main()) {
            wp_enqueue_style('bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime(get_stylesheet_directory() . '/style.css'));
        }
    }

    public function register_custom_elements()
    {
        $element_files = [
            __DIR__ . '/elements/title.php',
        ];
        foreach ($element_files as $file) {
            \Bricks\Elements::register_element($file);
        }
    }

    public function add_text_strings_to_builder($i18n)
    {
        $i18n['custom'] = esc_html__('Custom', 'bricks');
        return $i18n;
    }
}

$theme = new BrokeTheme();
$theme->init();
