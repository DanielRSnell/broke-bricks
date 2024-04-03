<?php

function get_acf_query_args_from_post()
{
    global $post;

    // Ensure ACF function exists
    if (!function_exists('have_rows') || empty($post)) {
        return [];
    }

    $args = []; // Initialize the array to hold our WP_Query args
    $timber_key = ''; // Initialize timber_key

    // Check if we have the flexible content field 'query_parameters'
    if (have_rows('query_parameters', $post->ID)) {
        while (have_rows('query_parameters', $post->ID)) {
            the_row();

            // Since your ACF fields are within a flexible content layout named 'query_params'
            // we need to ensure we are getting data from the correct layout
            if (get_row_layout() == 'query_params') {
                // Assuming your subfields such as post_type, posts_per_page, etc., are directly within this layout
                // Fetch all subfields for the layout as an associative array
                $rowData = get_row(true);

                // Assuming 'timber_key' is a field within each 'query_params' layout
                // It should be fetched and stored separately since it doesn't belong to WP_Query args
                if (isset($rowData['timber_key'])) {
                    $timber_key = $rowData['timber_key'];
                    unset($rowData['timber_key']); // Remove it from args since it's not a WP_Query argument
                }

                // Remove ACF specific keys
                unset($rowData['acf_fc_layout']);

                // The remaining rowData should now only contain relevant WP_Query args
                $args = array_merge($args, $rowData); // Merge the current row's args into our main args array
            }
        }
    }

    // Return both the arguments and the timber_key
    return ['args' => $args, 'timber_key' => $timber_key];
}

add_filter('timber/context', function ($context) {
    $acf_query_details = get_acf_query_args_from_post();

    if (!empty($acf_query_details['args']) && !empty($acf_query_details['timber_key'])) {
        $context[$acf_query_details['timber_key']] = get_posts($acf_query_details['args']);
    }

    return $context;
});
