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
