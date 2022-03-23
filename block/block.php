<?php
/**
 * Plugin Name:       related-posts
 * Description:       Example static block scaffolded with Create Block tool.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block
 *
 * @package           rhd
 */

/**
 * Register block.
 *
 * @return void
 */
function rhd_related_posts_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'rhd_related_posts_block_render',
		)
	);
}
add_action( 'init', 'rhd_related_posts_block_init' );

/**
 * Renders the related posts area.
 *
 * @access public
 * @param array  $attributes The block attributes.
 * @var string     $attributes['orderby'] Orderby attribute.
 * @var int        $attributes['lookback'] The max number of days in which to look back.
 * @var int        $attributes['count'] Number of posts to show.
 * @var string     $attributes['text'] Heading text.
 * @var string     $attributes['imageSize'] The post image size.
 * @param string $content The block content.
 * @return string|false The block output, or false.
 */
function rhd_related_posts_block_render( $attributes, $content ) {
	global $post;
	$is_admin = defined( 'REST_REQUEST' ) && REST_REQUEST;

	// Check to see if a transient has been set for this post, and if not, retrieve the data and set one.
	$related_posts = get_transient( 'rhd_related_posts_' . $post->ID );

	if ( false === ( $related_posts ) || $is_admin ) {
		$tags = wp_get_post_tags( $post->ID );

		if ( ! $tags ) {
			// TODO Set a secondary criteria?
			return false;
		}

		$tag_arr = '';

		foreach ( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag'            => $tag_arr,
			'posts_per_page' => $attributes['count'],
			'post__not_in'   => array( $post->ID ),
			'orderby'        => $attributes['orderby'],
		);

		if ( $attributes['lookback'] && 0 !== $attributes['lookback'] ) {
			$range              = gmdate( 'Y-m-d', strtotime( "-{$attributes['lookback']} days" ) );
			$args['date_query'] = array( array( 'after' => $range ) );
		}

		$related_posts = new WP_Query( $args );

		set_transient( 'rhd_related_posts_' . $post->ID, $related_posts, HOUR_IN_SECONDS );
	}

	$html = '';

	if ( $related_posts && $related_posts->have_posts() ) {
		$html .= sprintf(
			'<div class="wp-block-rhd-related-posts rhd-related-posts-container"><h4 class="rhd-related-posts-title">%s</h4><ul class="rhd-related-posts">',
			wp_strip_all_tags( $attributes['text'] )
		);

		while ( $related_posts->have_posts() ) {
			$related_posts->the_post();
			$_related_image_size  = $attributes['imageSize'];
			$_related_image_count = $attributes['count'];

			ob_start();
			include RHD_RELATED_POSTS_DIR . 'templates/item.php';
			$html .= ob_get_clean();
		}

		$html .= '</ul></div>';
	}

	wp_reset_postdata();

	return $html;
}
