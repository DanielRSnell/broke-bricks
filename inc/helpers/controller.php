<?php

add_action('rest_api_init', function () {
    register_rest_route('timberstrap/v1', '/debugger/(?P<id>\d+)', array(
        'methods' => 'GET', // Allows both GET and POST
        'callback' => 'timberstrap_debugger_endpoint',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param); // Validate the ID is numeric
                },
                'required' => true,
            ),
        ),
    ));
});

function timberstrap_debugger_endpoint($request)
{
    // Assuming $request['id'] contains the ID you want to use.
    $id = $request['id'];
    $change = $request['change'];

    $context = Timber::context();

    $post = get_post($id);
    if (!$post) {
        // If the post is not found, send a 404 status header and terminate.
        status_header(404);
        die('Post not found.');
    }

    $context['post'] = $post;
    $context['fields'] = get_fields($id);
    $context['state'] = $context;

    // No need to set content type header manually since we're outputting HTML directly.
    // Timber::render() will output the HTML directly to the browser.
    // Ensure there's no output before this point.
    Timber::render('@helper/fixed.html', $context);

    // Terminate execution to ensure this is the only output.
    exit;
}
