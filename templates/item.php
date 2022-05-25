<?php
/**
 * Related Posts item.
 *
 * @uses $_related_image_size
 * @uses $_related_image_count
 * @package RHD
 */

// $style = sprintf( 'width: %d%%;', (float) 100 / $_related_image_count );
?>

<li class="related-post">
	<a href="<?php the_permalink(); ?>" rel="bookmark">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $_related_image_size, array( 'class' => 'nopin' ) );
		} else {
			printf( '<img class="related-thumb-default" src="%1$s" alt="Featured image for %2$s">', esc_url( RHD_RELATED_POSTS_DIR . 'img/default-thumbnail.png' ), esc_html( get_the_title() ) );
		}
		?>
	</a>

	<p class="related-title">
		<a class="related-link" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</p>
</li>
