<?php

class ComponentRenderer
{

    private $functions = [];

    public function getComponent($name, $data = [], $type = 'lc_block')
    {
        // Construct the shortcode with properly escaped attributes
        $shortcode = '[lc_get_post post_type="' . esc_attr($type) . '" slug="' . esc_attr($name) . '"]';
        $template = do_shortcode($shortcode);

        // Check if the shortcode returns a non-empty result
        if (!empty($template)) {
            // Prepare the context for Timber
            $context = $data;

            // Compile the string with Timber, using the provided context
            $compiledString = Timber::compile_string($template, $context);
            echo $compiledString;
        } else {
            // If the shortcode doesn't return a valid template, output false for flagging
            echo false;
        }
    }

    public function getLoop($name, $data, $type = 'lc_block')
    {
        // Construct the shortcode with properly escaped attributes
        $shortcode = '[lc_get_post post_type="' . esc_attr($type) . '" slug="' . esc_attr($name) . '"]';
        $template = do_shortcode($shortcode);

        // Check if the shortcode returns a non-empty result
        if (!empty($template)) {
            // Prepare the context for Timber
            $context = [];
            $context['field'] = $data;

            // Compile the string with Timber, using the provided context
            $compiledString = Timber::compile_string($template, $context);
            echo $compiledString;
        } else {
            // If the shortcode doesn't return a valid template, output false for flagging
            echo false;
        }
    }

    public function getPartial($name, $data = [], $type = 'lc_block')
    {
        // Construct the shortcode with properly escaped attributes
        $shortcode = '[lc_get_post post_type="' . esc_attr($type) . '" slug="' . esc_attr($name) . '"]';
        $template = do_shortcode($shortcode);

        // Check if the shortcode returns a non-empty result
        if (!empty($template)) {
            // Prepare the context for Timber
            $context = $data;

            // Compile the string with Timber, using the provided context
            $compiledString = Timber::compile_string($template, $context);
            echo $compiledString;
        } else {
            // If the shortcode doesn't return a valid template, output false for flagging
            echo false;
        }
    }

    public function getForm($name, $type = 'lc_block')
    {
        // Construct the shortcode with properly escaped attributes
        $shortcode = '[lc_get_post post_type="' . esc_attr($type) . '" slug="' . esc_attr($name) . '"]';
        $template = do_shortcode($shortcode);

        $context = Timber::get_context();
        $context['post'] = get_page_by_path($name, OBJECT, $type);

        // Check if the shortcode returns a non-empty result
        if (!empty($template)) {

            // Compile the string with Timber, using the provided context
            $compiledString = Timber::compile_string($template, $context);
            echo $compiledString;
        } else {
            // If the shortcode doesn't return a valid template, output a placeholder message
            echo 'Component not found';
        }
    }

    public function addFunction($name, $callback)
    {
        $this->functions[$name] = $callback;
    }

    public function registerTwigFunctions()
    {
        add_filter('timber/twig/functions', function ($twigFunctions) {
            foreach ($this->functions as $functionName => $callback) {
                $twigFunctions[$functionName] = [
                    'callable' => [$this, $callback],
                ];
            }
            return $twigFunctions;
        });
    }
}

// Usage
$componentRenderer = new ComponentRenderer();

// Register functions using addFunction method
$componentRenderer->addFunction('component', 'getComponent');
$componentRenderer->addFunction('form', 'getForm');
$componentRenderer->addFunction('loop', 'getLoop');

// Add more functions as needed

$componentRenderer->registerTwigFunctions();
