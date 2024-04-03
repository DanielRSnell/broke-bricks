<?php

function handle_request()
{
    // Form ID is provided and not empty
    if (isset($_GET['form_id']) && $_GET['form_id'] !== '') {
        $form_id = intval($_GET['form_id']);
        $params = get_params_object(); // Ensure this function is defined and returns the expected object

        // Respond with the form handling logic
        echo broke_handle_form_request($form_id, $params);
    } else {
        // No form_id provided, check if 'template' parameter is present
        if (isset($_GET['template'])) {
            echo broke_handle_template_request();
        } else {
            // Set HTTP response code to 404 Not Found
            http_response_code(404);
        }
    }
}

// Call the main function to handle the request
handle_request();

function build_form_payload($form_id)
{
    // Fetch the form using the Gravity Forms API
    $form = GFAPI::get_form($form_id);

    if (!isset($_GET['fields'])) {

        if (is_wp_error($form) || $form === false) {
            return ['error' => 'Form not found or error fetching form with ID: ' . $form_id];
        }

        // Initialize payload with form_id
        $payload = ['form_id' => $form_id];

        // Prepare an array of all GET parameters converted to lowercase
        $getParamsLowercase = array_change_key_case($_GET, CASE_LOWER);

        // Iterate through each field in the form
        // Iterate through each field in the form
        foreach ($form['fields'] as $field) {
            // Replace spaces with underscores and convert the label to lowercase
            if (!empty($field['label'])) {
                $fieldLabelFormatted = strtolower(str_replace(' ', '_', $field['label']));
                if (array_key_exists($fieldLabelFormatted, $getParamsLowercase)) {
                    // If a match is found, add it to the payload using the field ID
                    $payload['input_' . $field['id']] = $getParamsLowercase[$fieldLabelFormatted];
                }
            }

            // Check for sub-fields (inputs) in complex fields like 'name' and 'address'
            if (isset($field['inputs']) && is_array($field['inputs'])) {
                foreach ($field['inputs'] as $input) {
                    if (!empty($input['label'])) {
                        $inputLabelFormatted = strtolower(str_replace(' ', '_', $input['label']));
                        if (array_key_exists($inputLabelFormatted, $getParamsLowercase)) {
                            $paramName = 'input_' . str_replace('.', '_', $input['id']);
                            $payload[$paramName] = $getParamsLowercase[$inputLabelFormatted];
                        }
                    }
                }
            }
        }

        return $payload;

    } else {

        header('Content-Type: application/json');

        return $form;
    }
}

function broke_handle_form_request($form_id, $params)
{
    if (GFAPI::form_id_exists($form_id)) {
        // Process and match fields, then output the result
        $payload = build_form_payload($form_id);
        $submit = submitForm($payload);

        // Check if the 'debug' parameter exists and is not set to 'true'
        if (isset($_GET['debug'])) {

            // If 'debug' is not set or it's explicitly 'true', just output the payload
            echo json_encode([
                'form_id' => $form_id,
                'payload' => $payload,
                'params' => $params,
                'submit' => $submit,

            ]);

            // if $submit.params has template, render the template
            if (isset($submit['params']['template'])) {

                // Note a tempalte can be set to a conditional confrmation statically or
                // dynamically with a hidden field that passes to a confirmation page
                if (!isset($submit['params']['post_type'])) {
                    $template = get_page_by_path($submit['params']['template'], OBJECT, 'bricks_template');
                    $execute = do_shortcode('[bricks_template id="' . $template->ID . '"]');
                } else {
                    $template = get_page_by_path($submit['params']['template'], OBJECT, $submit['params']['post_type']);
                    $execute = do_shortcode('[bricks_template id="' . $template->ID . '"]');
                }

            }

        } else {
            // Assuming submitForm($payload) is a function that processes the payload

            // if the template param exists. look for the template by slug and return the post object if found
            if (isset($_GET['template'])) {
                header('Content-Type: text/html');

                if (!isset($_GET['post_type'])) {
                    $template = get_page_by_path($_GET['template'], OBJECT, 'bricks_template');
                    $execute = do_shortcode('[bricks_template id="' . $template->ID . '"]');
                } else {
                    $template = get_page_by_path($_GET['template'], OBJECT, $_GET['post_type']);
                    $execute = do_shortcode('[bricks_template id="' . $template->ID . '"]');
                }
                if ($template) {
                    echo broke_render_template($execute);
                } else {

                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Template not found for the specified slug: ' . $_GET['template']]);
                }
            } else {
                echo json_encode(['payload' => $payload, 'submit' => $submit]);
            }

        }

    } else {
        echo json_encode(['error' => 'Form not found for the specified form ID: ' . $form_id]);
    }
}

function broke_handle_template_request()
{
    // Setting content type to HTML for template rendering
    header('Content-Type: text/html');

    $postType = isset($_GET['post_type']) ? $_GET['post_type'] : 'bricks_template';
    $template = get_page_by_path($_GET['template'], OBJECT, $postType);

    if (!empty($template)) {
        $execute = do_shortcode('[bricks_template id="' . $template->ID . '"]');
        echo broke_render_template($execute);
    } else {
        // Fallback or error handling if the template is not found
        echo 'Template not found.';
    }

}

function broke_render_template($execute)
{
    $context = Timber::context();
    // You can use the global context filter to add data to the payload here.

    $compile = Timber::compile_string($execute, $context);

    return $compile;
}
