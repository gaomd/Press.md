<?php

/*
 * Plugin Name: Press.md
 */

require('vendor/autoload.php');

define('PRESS_MD_MAIN_FILE', __FILE__);

function press_md_inject_assets()
{
    wp_enqueue_style('press-md-style', plugins_url('assets/css/application.min.css', PRESS_MD_MAIN_FILE));
    wp_enqueue_script('press-md-script', plugins_url('assets/js/main.min.js', PRESS_MD_MAIN_FILE), [], false, true);
}

function press_md_render_the_content($content)
{
    $converter = new \League\CommonMark\CommonMarkConverter();

    return $converter->convertToHtml($content);
}

add_action('admin_print_scripts-post.php', 'press_md_inject_assets');
add_action('admin_print_scripts-post-new.php', 'press_md_inject_assets');
add_filter('user_can_richedit', '__return_false');

add_filter('the_content', 'press_md_render_the_content', 1);
