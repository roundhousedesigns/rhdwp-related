<?php
/**
 * RHDWP Related posts template.
 * 
 * @uses $args['size']
 */

?>

<li class="related-post">
	<a href="<?php the_permalink(); ?>" rel="bookmark">
		<?php
		if ( has_post_thumbnail() ) {
			echo get_the_post_thumbnail( $post->ID, $args['size'], array( 'data-pin-nopin' => 'true' ) );
		} else {
			echo '<img class="related-thumb-default" src="' . RHDWP_REL_DIR_URL . 'img/default-thumbnail.png" alt="' . get_the_title() . '" data-pin-nopin="true">';
		}
		?>
		<p class="related-title"><?php the_title(); ?></p>
	</a>
</li>