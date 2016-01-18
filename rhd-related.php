/**
 * Plugin Name: RHD Related Posts
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.0
 */

function rhd_related_posts() {
	global $post;
	$tags = wp_get_post_tags( $post->ID );
	if ( $tags ) {
		foreach( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag' => $tag_arr,
			'numberposts' => 4,
			'post__not_in' => array( $post->ID )
		);
		$related_posts = get_posts( $args );
		if ( $related_posts ) {
			$output = "<div id='rhd-related-posts-container'>\n"
					. "<h4 class='rhd-related-posts-title'>Related Posts</h4>\n"
					. "<ul id='rhd-related-posts'>\n";

					foreach ( $related_posts as $post ) : setup_postdata( $GLOBALS['post'] =& $post );
						$title = get_the_title();
						$permalink = get_the_permalink();

						$output .= "<li class='related-post'>\n"
								. "<a class='related-link' href='$permalink' rel='bookmark'>\n";

						if ( has_post_thumbnail() ) {
							$output .= get_the_post_thumbnail( $post->ID, 'square-thumb' );
						} else {
							$output .= "<img class='related-thumb-default' src='" . RHD_IMG_DIR . "/default-thumbnail.jpg' alt='$title'>\n";
						}

						$output .= "<p class='related-title'>$title</p>\n"
								. "</a>\n</li>\n";
					endforeach;
			$output .= "</ul>\n";

			echo $output;
		}
	}
	wp_reset_postdata();
}
