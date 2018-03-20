<?php
/**
 * Plugin Name: RHD Related Posts
 * Description: Simple tag-based "related posts" plugin.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 1.3
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
//add_action( 'wp_enqueue_scripts', 'rhd_related_enqueue_styles' );


/**
 * rhd_related_posts function.
 *
 * @access public
 * @param string $orderby (default: null)
 * @param mixed $days (default: null) number of days to search back
 * @param int $ppp (default: 4) posts per page
 * @PARAM int $expire (default: MONTH_IN_SECONDS) Transient expiration
 * @return void
 */
function rhd_related_posts( $orderby = null, $days = null, $ppp = 4, $expire = MONTH_IN_SECONDS )
{
	global $post;
	
	if ( false === ( $related_posts = get_transient( 'rhd_related_post_' . $post->ID ) ) ) {
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
			
			set_transient( 'rhd_related_post_' . $post->ID, $related_posts, $expire );
		}
	}
	?>
		
	<?php if ( $related_posts && $related_posts->have_posts() ) : ?>
		<div id="rhd-related-posts-container">
			<h4 class='rhd-related-posts-title'>You May Also Like...</h4>
			<div id='rhd-related-posts' class='post-grid post-container'>

			<?php while ( $related_posts->have_posts() ) : ?>
				<?php
				$related_posts->the_post();
				
				if ( has_post_thumbnail() ) {
					$thumb = get_the_post_thumbnail( get_the_ID(), 'square', array( 'alt' => get_the_title() . ' thumbnail', 'class' => 'no-pin' ) );
				} else {
					$thumb = wp_get_attachment_image( 26895, 'square', false, array( 'alt' => 'Default thumbnail', 'class' => 'no-pin' ) );
				}
				?>
	
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-grid-item square-grid-item' ); ?>>
					<div class="entry-thumbnail">
						<a class="grid-main-link" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'rhd' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">
							<?php echo $thumb; ?>
						</a>
				
						<div class="entry-header">
							<h2 class="entry-title"><a class="grid-main-link" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'rhd' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
						</div>
					</div>
				</article>
			<?php endwhile; ?>

			</div>
		</div>
		
	<?php endif;
	wp_reset_postdata();
}