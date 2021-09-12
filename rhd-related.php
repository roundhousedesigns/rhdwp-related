<?php
/**
 * Plugin Name: RHD Related Posts
 * Description: Simple "related posts" plugin.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.7
 *
 * @package RHD
 */

define( 'RHD_RELATED_VERSION', '1.7' );
define( 'RHD_RELATED_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'RHD_RELATED_DIR', plugin_dir_path( __FILE__ ) );
define( 'RHD_RELATED_CACHE_PREFIX', 'rhd_related_posts_' );

/**
 * Enqueue styles and scripts
 *
 * @access public
 * @return void
 */
function rhd_related_enqueue_styles() {
	wp_enqueue_style( 'rhd-related-css', RHD_RELATED_DIR_URL . 'rhd-related.css', null, RHD_RELATED_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhd_related_enqueue_styles' );

/**
 * Main output function
 *
 * @access public
 * @param int    $id Post id. Defaults to post in the loop.
 * @param string $taxonomy (default: 'post_tag') The taxonomy to use as the comparision basis.
 * @param string $orderby (default: 'rand') Ordering.
 * @param mixed  $days (default: null) Date range.
 * @param int    $ppp (default: 4) Posts per page.
 * @param string $text (default: "You May Also Like...") Display heading text.
 * @param int    $expire (default: DAY_IN_SECONDS) Transient expiration.
 * @return void
 */
function rhd_related_posts( $id = null, $taxonomy = 'post_tag', $orderby = 'rand', $days = null, $ppp = 4, $text = 'You May Also Like...', $expire = DAY_IN_SECONDS ) {
	$id = $id ? $id : get_the_id();

	// Check to see if a transient has been set for this post, and if not, retrieve the data and set one.
	$related_posts = get_transient( RHD_RELATED_CACHE_PREFIX . $id );
	if ( false === $related_posts ) {
		$terms      = wp_get_post_terms( $id, $taxonomy );
		$term_slugs = array_map(
			function ( $term ) {
				return $term->term_id;
			},
			$terms
		);

		if ( $terms ) {
			$args = array(
				'tax_query'      => array(  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_slugs,
					),
				),
				'posts_per_page' => $ppp,
				'post__not_in'   => array( $id ),
				'orderby'        => $orderby,
			);

			if ( $days ) {
				$range              = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
				$args['date_query'] = array( array( 'after' => $range ) );
			}

			$related_posts = new WP_Query( $args );
			set_transient( RHD_RELATED_CACHE_PREFIX . $id, $related_posts, $expire );
		}
	}

	if ( $related_posts && $related_posts->have_posts() ) :
		printf(
			'<div class="rhd-related-posts-container"><h4 class="rhd-related-posts-title">%s</h4><ul class="rhd-related-posts">',
			esc_textarea( $text, 'rhd' )
		);

		while ( $related_posts->have_posts() ) {
			$related_posts->the_post();
			if ( locate_template( 'rhd-related.php' ) ) {
				get_template_part( 'rhd-related' );
			} else {
				include RHD_RELATED_DIR . 'template.php';
			}
		}

		printf( '</ul></div>' );
	endif;
	wp_reset_postdata();
}

/**
 * Inserts the Related Posts block after post content.
 *
 * @param string $content The post content.
 * @return string The html output.
 */
function rhd_related_posts_content_hook( $content ) {
	if ( get_post_type() !== 'post' ) {
		return $content;
	}

	ob_start();
	rhd_related_posts( 'rand', null, 4, 'Related Posts' );

	return $content . ob_get_clean();
}
// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar,Squiz.PHP.CommentedOutCode.Found
// add_action( 'the_content', 'rhd_related_posts_content_hook' );

/**
 * Deletes all RHD Related trasients.
 *
 * @param int     $id The post ID.
 * @param WP_Post $post The post object.
 * @param boolean $update Whether this is an existing post being updated.
 * @return void
 */
function rhd_related_posts_flush_cache( $id, $post, $update ) {
	$posts = get_posts(
		array(
			'post_type'      => $post->post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);

	foreach ( $posts as $post ) {
		$cache_key = RHD_RELATED_CACHE_PREFIX . $post->ID;
		if ( get_transient( $cache_key ) ) {
			delete_transient( $cache_key );
		}
	}
}
add_action( 'save_post', 'rhd_related_posts_flush_cache', 10, 3 );
