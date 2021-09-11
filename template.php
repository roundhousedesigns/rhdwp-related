<li class="related-post">
	<a href="<?php the_permalink(); ?>" rel="bookmark">
		<?php
		if ( has_post_thumbnail() ) {
			echo get_the_post_thumbnail( $post->ID, 'grid', array( 'data-pin-nopin' => 'true' ) );
		} else {
			echo '<img class="related-thumb-default" src="' . RHDWP_RELATED_DIR_URL . 'img/default-thumbnail.png" alt="' . get_the_title() . '" data-pin-nopin="true">';
		}
		?>
	</a>

	<p class="related-title">
		<a class="related-link" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</p>
</li>