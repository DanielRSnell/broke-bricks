<?php

/**
 * Add text strings to builder
 */
add_filter('bricks/builder/i18n', function ($i18n) {
    // For element category 'custom'
    $i18n['custom'] = esc_html__('Custom', 'bricks');

    return $i18n;
});

function custom_dequeue_bricks_assets_on_preview()
{
    $tailwind_mode = carbon_get_theme_option('crb_tailwind_mode');

    if ($tailwind_mode && isset($_GET['brickspreview'])) {
        wp_deregister_style('bricks-frontend');
        // wp_deregister_style('bricks-builder');
        wp_deregister_style('bricks-default-content');
        wp_deregister_style('bricks-element-posts');
        wp_deregister_style('bricks-isotope');
        wp_deregister_style('bricks-element-post-author');
        wp_deregister_style('bricks-element-post-comments');
        wp_deregister_style('bricks-element-post-navigation');
        wp_deregister_style('bricks-element-post-sharing');
        wp_deregister_style('bricks-element-post-taxonomy');
        wp_deregister_style('bricks-element-related-posts');
        wp_deregister_style('bricks-404');
        wp_deregister_style('wp-block-library');
        wp_deregister_style('classic-theme-styles');
        wp_deregister_style('global-styles');

        wp_enqueue_style('custom-builder-styles', get_stylesheet_directory_uri() . '/assets/css/builder/styles.css');
    }
}
add_action('wp_enqueue_scripts', 'custom_dequeue_bricks_assets_on_preview', 100);

function remove_bricks_inline_styles_on_condition()
{
    $tailwind_mode = carbon_get_theme_option('crb_tailwind_mode');

    if ($tailwind_mode && isset($_GET['brickspreview']) && (!isset($_GET['bricksrun']) || $_GET['bricksrun'] == 'true')) {
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll('style[id*="bricks"]').forEach(function(styleTag) {
                    styleTag.remove();
                });
            });
        </script>
        <?php
}
}
add_action('wp_footer', 'remove_bricks_inline_styles_on_condition');

function custom_dequeue_styles_except_bricksrun()
{
    $tailwind_mode = carbon_get_theme_option('crb_tailwind_mode');

    if ($tailwind_mode && !isset($_GET['bricks'])) {
        $style_handles = [
            'bricks-frontend',
            // 'bricks-builder',
            'bricks-default-content',
            'bricks-element-posts',
            'bricks-isotope',
            'bricks-element-post-author',
            'bricks-element-post-comments',
            'bricks-element-post-navigation',
            'bricks-element-post-sharing',
            'bricks-element-post-taxonomy',
            'bricks-element-related-posts',
            'bricks-404',
            'wp-block-library',
            'classic-theme-styles',
            'global-styles',
            'bricks-admin',
        ];

        foreach ($style_handles as $handle) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }
}
add_action('wp_enqueue_scripts', 'custom_dequeue_styles_except_bricksrun', 100);

function custom_dequeue_bricks_scripts()
{
    // Check if 'bricks=run' is present in the URL
    if (isset($_GET['bricks']) && $_GET['bricks'] == 'run') {
        // If 'bricks=run' is found, do not dequeue or deregister the script
        return;
    }

    // Otherwise, check the theme option to see if the script should be dequeued
    $remove_bricks_js = carbon_get_theme_option('crb_bricks_js');

    if ($remove_bricks_js) {
        wp_dequeue_script('bricks-scripts');
        wp_deregister_script('bricks-scripts');
    }
}
add_action('wp_enqueue_scripts', 'custom_dequeue_bricks_scripts', 100);
