<?php
/**
 * Plugin Name: Related Posts
 * Description: Display a linked collection of related posts.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.6
 *
 * @package RHD
 */

// TODO Refactor to remove output buffering.
// TODO alley-OOP.

define( 'RHDWP_REL_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'RHDWP_REL_DIR', plugin_dir_path( __FILE__ ) );
define( 'RHDWP_RELATED_CACHE_PREFIX', 'rhdwp_related_posts_' );
define( 'RHDWP_RELATED_VERSION', '1.6' );

/**
 * Enqueue styles and scripts
 *
 * @access public
 * @return void
 */
function rhdwp_related_enqueue_styles() {
	wp_enqueue_style( 'rhdwp-related-css', RHDWP_REL_DIR_URL . 'rhdwp-related.css', null, RHDWP_RELATED_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhdwp_related_enqueue_styles' );

/**
 * Main output function
 *
 * @access public
 * @param string $tax (default: 'tag') The taxonomy to use for comparison.
 * @param string $orderby (default: 'rand') Ordering.
 * @param mixed  $days (default: null) Date range.
 * @param int    $ppp (default: 4) Posts per page.
 * @param string $text (default: "You May Also Like...") Display heading text.
 * @param int    $expire (default: DAY_IN_SECONDS) Transient expiration.
 * @param string $size (default: medium) Thumbnail size.
 * @return void
 */
function rhdwp_related_posts( $tax = 'tag', $orderby = 'rand', $days = null, $ppp = 4, $text = 'You May Also Like...', $expire = DAY_IN_SECONDS, $size = 'medium' ) {
	global $post;

	if ( 'tag' !== $tax && 'cat' !== $tax ) {
		return;
	}

	// Sanitize.
	$text = wp_strip_all_tags( $text );

	// Check to see if a transient has been set for this post, and if not, retrieve the data and set one.
	$related_posts = get_transient( RHDWP_RELATED_CACHE_PREFIX . $post->ID );
	if ( false === $related_posts ) {
		if ( 'tag' === $tax ) {
			$terms = wp_get_post_tags( $post->ID );
		} elseif ( 'cat' === $tax ) {
			$terms = wp_get_post_categories( $post->ID );
		}

		$term_arr = array();

		if ( $terms ) {
			foreach ( $terms as $term ) {
				$term_arr[] = $term;
			}

			$args = array(
				'posts_per_page' => $ppp,
				'post__not_in'   => array( $post->ID ),
				'orderby'        => $orderby,
			);

			if ( 'tag' === $tax ) {
				$args['tag'] = $term_arr;
			} elseif ( 'cat' === $tax ) {
				$args['cat'] = $term_arr;
			}

			if ( $days ) {
				$range              = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
				$args['date_query'] = array( array( 'after' => $range ) );
			}

			$related_posts = new WP_Query( $args );
			set_transient( RHDWP_RELATED_CACHE_PREFIX . $post->ID, $related_posts, $expire );
		}
	}

	if ( $related_posts && $related_posts->have_posts() ) :
		printf(
			'<div class="rhdwp-related-posts-container"><h4 class="rhdwp-related-posts-title">%s</h4><ul class="rhdwp-related-posts">',
			esc_html( $text )
		);

		// phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
		while ( $related_posts->have_posts() ) : $related_posts->the_post();
			if ( locate_template( 'rhdwp-related.php' ) ) {
				// Theme override present.
				get_template_part( 'rhdwp-related', null, array( 'size' => $size ) );
			} else {
				// Set up template vars.
				$args = array( 'size' => $size );

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
 * @param string $content The post content.
 * @return string The html output
 */
function rhdwp_related_posts_content_hook( $content ) {
	if ( get_post_type() !== 'post' || ! is_single() ) {
		return $content;
	}

	ob_start();
	rhdwp_related_posts( 'cat', 'rand', null, 4, 'Related Posts', DAY_IN_SECONDS, 'thumbnail' );

	return $content . ob_get_clean();
}
add_action( 'the_content', 'rhdwp_related_posts_content_hook' );

/**
 * Deletes trasients for single posts.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post The post type to query.
 * @param bool    $update Whether this is a post being updated.
 * @return void
 */
function rhdwp_related_posts_delete_post_from_cache( $post_id, $post, $update ) {
	$posts = get_posts(
		array(
			'post_type'      => $post->post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);

	foreach ( $posts as $post ) {
		$cache_key = RHDWP_RELATED_CACHE_PREFIX . $post_id;
		if ( get_transient( $cache_key ) ) {
			delete_transient( $cache_key );
		}
	}
}
add_action( 'save_post', 'rhdwp_related_posts_delete_post_from_cache', 10, 3 );
