<?php
/**
 * Plugin Name: RHDWP Related Posts
 * Description: Simple "related posts" plugin.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.6
 */

define( 'RHDWP_REL_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'RHDWP_REL_DIR', plugin_dir_path( __FILE__ ) );
define( 'RHDWP_RELATED_CACHE_PREFIX', 'rhdwp_related_posts_' );

/**
 * Enqueue styles and scripts
 *
 * @access public
 * @return void
 */
function rhdwp_related_enqueue_styles() {
	wp_enqueue_style( 'rhdwp-related-css', RHDWP_REL_DIR_URL . 'rhdwp-related.css', null, null, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhdwp_related_enqueue_styles' );

/**
 * Main output function
 *
 * @access public
 * @param string $orderby (default: 'rand') Ordering
 * @param mixed $days (default: null) Date range
 * @param int $ppp (default: 4) Posts per page
 * @param string $text (default: "You May Also Like...") Display heading text
 * @param int $expire (default: DAY_IN_SECONDS) Transient expiration
 * @return void
 */
function rhdwp_related_posts( $orderby = 'rand', $days = null, $ppp = 4, $text = "You May Also Like...", $expire = DAY_IN_SECONDS ) {
	global $post;

	// Sanitize $text
	$text = strip_tags( __( $text, 'rhdwp' ) );

	// Check to see if a transient has been set for this post, and if not, retrieve the data and set one.
	if ( false === ( $related_posts = get_transient( RHDWP_RELATED_CACHE_PREFIX . $post->ID ) ) ) {
		$tags = wp_get_post_tags( $post->ID );

		$tag_arr = '';

		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$tag_arr .= $tag->slug . ',';
			}
			$args = array(
				'tag'            => $tag_arr,
				'posts_per_page' => $ppp,
				'post__not_in'   => array( $post->ID ),
				'orderby'        => $orderby,
			);

			if ( $days ) {
				$range              = date( 'Y-m-d', strtotime( "-{$days} days" ) );
				$args['date_query'] = array( array( 'after' => $range ) );
			}

			$related_posts = new WP_Query( $args );
			set_transient( RHDWP_RELATED_CACHE_PREFIX . $post->ID, $related_posts, $expire );
		}
	}

	if ( $related_posts && $related_posts->have_posts() ):
		printf(
			'<div class="rhdwp-related-posts-container"><h4 class="rhdwp-related-posts-title">%s</h4><ul class="rhdwp-related-posts">',
			__( $text, 'rhdwp' )
		);

		while ( $related_posts->have_posts() ): $related_posts->the_post();
			if ( locate_template( 'rhdwp-related.php' ) ) {
				get_template_part( 'rhdwp-related' );
			} else {
				include RHDWP_REL_DIR . 'template.php';
			}
		endwhile;

		printf( '</ul></div>' );
	endif;
	wp_reset_postdata();
}

/**
 * Inserts the Related Posts block after post content.
 *
 * @param string $content
 * @return string The html output
 */
function rhdwp_related_posts_content_hook( $content ) {
	if ( get_post_type() !== 'post' ) {
		return $content;
	}

	ob_start();
	rhdwp_related_posts( 'rand', null, 4, "Related Posts" );

	return $content . ob_get_clean();
}
// add_action( 'the_content', 'rhdwp_related_posts_content_hook' );

/**
 * Deletes all RHDWP Related trasients.
 *
 * @param string $post_type The post type to query.
 * @return void
 */
function rhdwp_related_posts_flush_cache( $post_id, $post, $update ) {
	$posts = get_posts( array(
		'post_type'      => $post->post_type,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	foreach ( $posts as $post ) {
		$cache_key = RHDWP_RELATED_CACHE_PREFIX . $post->ID;
		if ( get_transient( $cache_key ) ) {
			delete_transient( $cache_key );
		}
	}
}
add_action( 'save_post', 'rhdwp_related_posts_flush_cache', 10, 3 );