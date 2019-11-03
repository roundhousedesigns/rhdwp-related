<?php
/**
 * Plugin Name: RHDWP Related Posts
 * Description: Simple "related posts" plugin.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.5
 */

define( 'RHDWP_REL_DIR', plugin_dir_url( __FILE__ ) );


/**
 * Enqueue styles and scripts
 *
 * @access public
 * @return void
 */
function rhdwp_related_enqueue_styles() {
	wp_enqueue_style( 'rhdwp-related-css', RHDWP_REL_DIR . '/rhdwp-related-posts.css', null, null, 'all' );
}
add_action( 'wp_enqueue_scripts', 'rhdwp_related_enqueue_styles' );


/**
 * Main output function
 * 
 * @access public
 * @param string $orderby (default: 'rand')
 * @param mixed $days (default: null)
 * @param int $ppp (default: 4)
 * @param string $text (default: "You May Also Like...")
 * @param int $expire (default: MONTH_IN_SECONDS) Transient expiration
 * @return void
 */
function rhdwp_related_posts( $orderby = 'rand', $days = null, $ppp = 4, $text = "You May Also Like...", $expire = MONTH_IN_SECONDS ) {
	global $post;
	
	// Check to see if a transient has been set for this post, and if not, retrieve the data and set one.
	if ( false === ( $related_posts = get_transient( 'rhdwp_related_posts_' . $post->ID ) ) ) {
		$tags = wp_get_post_tags( $post->ID );
	
		$tag_arr = '';
	
		if ( $tags ) {
			foreach( $tags as $tag ) {
				$tag_arr .= $tag->slug . ',';
			}
			$args = array(
				'tag' => $tag_arr,
				'posts_per_page' => $ppp,
				'post__not_in' => array( $post->ID ),
				'orderby' => $orderby
			);
	
			if ( $days ) {
				$range = date( 'Y-m-d', strtotime( "-{$days} days" ) );
				$args['date_query'] = array( array( 'after' => $range ) );
			}

			$related_posts = new WP_Query( $args );
			set_transient( 'rhdwp_related_posts_' . $post->ID, $related_posts, $expire );
		}
	}
	?>	
	
	<?php if ( $related_posts && $related_posts->have_posts() ) : ?>
		<div class="rhdwp-related-posts-container">
			<h4 class="rhdwp-related-posts-title"><?php echo $text; ?></h4>
			<ul class="rhdwp-related-posts">

				<?php while( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
					<li class="related-post">
						<a href="<?php the_permalink(); ?>" rel="bookmark">
							<?php
							if ( has_post_thumbnail() ) {
								echo get_the_post_thumbnail( $post->ID, 'square' );
							} else {
								echo '<img class="related-thumb-default" src="' . RHDWP_REL_DIR . 'img/default-thumbnail.png" alt="' . get_the_title() . '" data-pin-nopin="true">';
							}
							?>
						</a>
		
						<p class="related-title">
							<a class="related-link" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
						</p>
					</li>
				<?php endwhile; ?>

			</ul>
		</div>
	<?php endif; ?>
	<?php wp_reset_postdata();
}