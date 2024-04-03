<?php

/* Add new query type control to query options */
add_filter('bricks/setup/control_options', 'bl_setup_query_controls');
function bl_setup_query_controls($control_options)
{

    /* Adding a new option in the dropdown */
    $control_options['queryTypes']['my_new_query_type'] = esc_html__('Broke Example Query');

    return $control_options;

};

/* Run new query if option selected */
add_filter('bricks/query/run', 'bl_maybe_run_new_query', 10, 2);
function bl_maybe_run_new_query($results, $query_obj)
{

    if ($query_obj->object_type !== 'my_new_query_type') {
        return $results;
    }

    /* If option is selected, run our new query */
    if ($query_obj->object_type === 'my_new_query_type') {
        $results = run_new_query();
    }

    return $results;

};

/* Setup post data for posts */
add_filter('bricks/query/loop_object', 'bl_setup_post_data', 10, 3);
function bl_setup_post_data($loop_object, $loop_key, $query_obj)
{

    if ($query_obj->object_type !== 'my_new_query_type') {
        return $loop_object;
    }

    global $post;
    $post = get_post($loop_object);
    setup_postdata($post);

    return [
        [
            'string' => 'This is a string',
            'array' => ['This is an array'],
            'object' => (object) ['This is an object'],
        ],
    ];

};

/* Return results from our custom WP Query arguments */
function run_new_query()
{

    /* Add all of your WP_Query arguments here */

    $args = [
        'post_type' => 'post',
        'orderby' => 'rand',
        'posts_per_page' => '1',
    ];

    $posts_query = new WP_Query($args);

    return $posts_query->posts;

};
