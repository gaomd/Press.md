<?php

/*
 * Plugin Name: Pressdown
 */

defined('ABSPATH') or die();

require('vendor/autoload.php');

define('PRESSDOWN_MAIN_FILE', __FILE__);

function pressdown_inject_assets()
{
    wp_enqueue_style('pressdown-style', plugins_url('assets/css/application.min.css', PRESSDOWN_MAIN_FILE));
    wp_enqueue_script('pressdown-script', plugins_url('assets/js/main.min.js', PRESSDOWN_MAIN_FILE), [], false, true);
}

function pressdown_render_the_content($content)
{
    $converter = new \League\CommonMark\CommonMarkConverter();

    return $converter->convertToHtml($content);
}

add_action('admin_print_scripts-post.php', 'pressdown_inject_assets');
add_action('admin_print_scripts-post-new.php', 'pressdown_inject_assets');
add_filter('user_can_richedit', '__return_false');
add_filter('image_send_to_editor', function ($html, $id, $caption, $title, $align, $url, $size, $alt) {
    list($img_src, $width, $height) = image_downsize($id, $size);

    $markdown = "![{$alt}]({$img_src})";

    if ($url) {
        $markdown = "[{$markdown}]($url)";
    }

    return $markdown;
}, 100, 8);

add_filter('the_content', 'pressdown_render_the_content', 1);
