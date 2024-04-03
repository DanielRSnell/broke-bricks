<?php
class GravityFormsSubmitFunction
{
    private $functions = [];

    public function submitForm($params = [])
    {
        if (!class_exists('GFAPI')) {
            return new WP_Error('gf_not_active', 'Gravity Forms plugin is not active');
        }

        if (!isset($params['form_id'])) {
            return new WP_Error('form_id_missing', 'The form_id parameter is missing');
        }

        $form_id = $params['form_id'];
        $submission = [];

        foreach ($params as $key => $value) {
            if (strpos($key, 'input_') === 0) {
                $input_id = $key;
                $submission[$input_id] = $value;
            }
        }

        $result = GFAPI::submit_form($form_id, $submission);

        if (is_wp_error($result)) {
            return [
                'is_valid' => false,
                'validation_messages' => $result->get_error_messages(),
            ];
        }

        $redirect_params = [];
        if (isset($result['confirmation_redirect'])) {
            $redirect_url = $result['confirmation_redirect'];
            $parsed_url = parse_url($redirect_url);

            // Check if there is a query part in the URL
            if (isset($parsed_url['query'])) {
                // Parse the query string into an associative array
                parse_str($parsed_url['query'], $redirect_params);

                // Iterate over the array to remove any leading '?' from keys
                foreach ($redirect_params as $key => $value) {
                    // If the key starts with '?', create a new key without '?' and remove the old one
                    if (strpos($key, '?') === 0) {
                        $newKey = ltrim($key, '?');
                        $redirect_params[$newKey] = $value;
                        unset($redirect_params[$key]);
                    }
                }
            }
        }

        return [
            'is_valid' => $result['is_valid'],
            'validation_messages' => $result['validation_messages'] ?? [],
            'page_number' => $result['page_number'] ?? null,
            'source_page_number' => $result['source_page_number'] ?? null,
            'confirmation_message' => $result['confirmation_message'] ?? null,
            'confirmation_type' => $result['confirmation_type'] ?? null,
            'confirmation_redirect' => $result['confirmation_redirect'] ?? null,
            'entry_id' => $result['entry_id'] ?? null,
            'resume_token' => $result['resume_token'] ?? null,
            'sent' => [
                'form_id' => $form_id,
                'submission' => $submission,
            ],
            'params' => $redirect_params,
        ];
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
$gravityFormsSubmitFunction = new GravityFormsSubmitFunction();

// Create an instance of the GravityFormsSubmitFunction class
$GLOBALS['gravityFormsSubmitFunction'] = new GravityFormsSubmitFunction();

// Register the submitForm function as a global function
function submitForm($params = [])
{
    if (isset($GLOBALS['gravityFormsSubmitFunction'])) {
        return $GLOBALS['gravityFormsSubmitFunction']->submitForm($params);
    } else {
        return new WP_Error('gravity_forms_submit_function_not_initialized', 'The GravityFormsSubmitFunction instance is not initialized.');
    }
}

// Register the submitForm function using addFunction method
$gravityFormsSubmitFunction->addFunction('submit_form', 'submitForm');

$gravityFormsSubmitFunction->registerTwigFunctions();
