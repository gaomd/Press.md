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

/**
 * Unescape `"` char escaped by Markdown processor, modified from do_shortcode()
 *
 * @see do_shortcode()
 * @param $content
 * @return string
 */
function pressdown_shortcode_unescape($content)
{
    global $shortcode_tags;

    if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
        return $content;
    }

    $tagregexp = join('|', array_map('preg_quote', array_keys($shortcode_tags)));

    // @formatter:off
    $pattern =
        '/'
        . '('                                   // 1: The shortcode
        .     '\\['                             // Opening bracket
        .     "($tagregexp)"                    // 2: Shortcode name
        .     '(?![\\w-])'                      // Not followed by word character or hyphen
                                                // Unroll the loop: Inside the opening shortcode tag
        .     '[^\\]\\/]*'                      // Not a closing bracket or forward slash
        .     '(?:'
        .         '\\/(?!\\])'                  // A forward slash not followed by a closing bracket
        .         '[^\\]\\/]*'                  // Not a closing bracket or forward slash
        .     ')*?'
        .     '(?:'
        .         '\\/\\]'                      // Self closing tag and closing bracket
        .     '|'
        .         '\\]'                         // Closing bracket
        .         '(?:'                         // Unroll the loop: Optionally, anything between the opening and closing shortcode tags
        .             '[^\\[]*+'                // Not an opening bracket
        .             '(?:'
        .                 '\\[(?!\\/\\2\\])'    // An opening bracket not followed by the closing shortcode tag
        .                 '[^\\[]*+'            // Not an opening bracket
        .             ')*+'
        .             '\\[\\/\\2\\]'            // Closing shortcode tag
        .         ')?'
        .     ')'
        . ')'
        . '/s';
    // @formatter:on

    return str_replace('&quot;', '"', preg_replace($pattern, '$1', $content));
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

add_filter('the_content', 'pressdown_render_the_content', 9);
add_filter('the_content', 'pressdown_shortcode_unescape', 9);
remove_filter('the_content', 'wptexturize');
