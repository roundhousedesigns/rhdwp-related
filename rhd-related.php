<?php
/**
 * Plugin Name: RHD Related Posts
 * Description: Simple "related posts" plugin.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.2
 */

define( 'RHD_REL_DIR', plugin_dir_url(__FILE__) );


/**
 * rhd_related_enqueue_styles function.
 *
 * @access public
 * @return void
 */
function rhd_related_enqueue_styles()
{
	wp_enqueue_style( 'rhd-related', RHD_REL_DIR . '/rhd-related.css', null, 1.0, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhd_related_enqueue_styles' );


/**
 * rhd_related_posts function.
 *
 * @access public
 * @param string $orderby (default: 'rand')
 * @param mixed $days (default: null) number of days to search back
 * @return void
 */
function rhd_related_posts( $orderby = 'rand', $days = null )
{
	global $post;
	$tags = wp_get_post_tags( $post->ID );

	if ( $tags ) {
		foreach( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag' => $tag_arr,
			'numberposts' => 4,
			'post__not_in' => array( $post->ID ),
			'orderby' => $orderby
		);

		if ( $days ) {
			$range = date( 'Y-m-d', strtotime( "-{$days} days" ) );
			$args['date_query'] = array( array( 'after' => $range ) );
		}

		$related_posts = get_posts( $args );
		if ( $related_posts ) {
			$output = "<div id='rhd-related-posts-container'>\n"
					. "<h4 class='rhd-related-posts-title'>You May Also Like...</h4>\n"
					. "<ul id='rhd-related-posts'>\n";

			foreach ( $related_posts as $post ) : setup_postdata( $GLOBALS['post'] =& $post );
				$title = get_the_title();
				$permalink = get_the_permalink();

				$output .= "<li class='related-post'>\n"
						. "<a class='related-link' href='$permalink' rel='bookmark'>\n";

				if ( has_post_thumbnail() ) {
					$output .= get_the_post_thumbnail( $post->ID, 'square' );
				} else {
					$output .= "<img class='related-thumb-default' src='" . RHD_REL_DIR . "img/default-thumbnail.png' alt='$title'>\n";
				}

				$output .= "<p class='related-title'>$title</p>\n"
						. "</a>\n</li>\n";
			endforeach;

			$output .= "</ul>\n</div>";

			echo $output;
		}
	}
	wp_reset_postdata();
}
?>
