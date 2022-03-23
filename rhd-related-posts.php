<?php
/**
 * Plugin Name: RHDWP Related Posts
 * Description: Enabled related posts in a lovely grid/row.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 2.0-beta
 *
 * @package RHD
 */

define( 'RHD_RELATED_POSTS_VERSION', '2.0-beta' );
define( 'RHD_RELATED_POSTS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Block.
 */
require_once RHD_RELATED_POSTS_DIR . 'block/block.php';

/**
 * Enqueue styles.
 *
 * @access public
 * @return void
 */
function rhd_related_posts_enqueue_styles() {
	wp_enqueue_style( 'rhd-related', RHD_RELATED_POSTS_DIR . '/rhd-related-posts.css', array(), RHD_RELATED_POSTS_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhd_related_posts_enqueue_styles' );
add_action( 'admin_enqueue_scripts', 'rhd_related_posts_enqueue_styles' );
