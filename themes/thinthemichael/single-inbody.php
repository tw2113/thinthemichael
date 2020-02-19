<?php

get_header();
?>
<div class="row-fluid">
	<div class="span8">

		<?php
		if ( have_posts() ) {
			while( have_posts() ) {
				the_post();
				?>
			<article>
				<header>
					<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<p><?php thin_the_posted_on(); ?></p>
				</header>
                <ul>
				<?php
				$data = get_post_meta( get_the_ID() );

				foreach( $data as $key => $datum ) {
				    if ( $key === '_ttm_body_fat_mass' ) {
				        continue;
                    }
				    if ( false === strpos( $key, '_ttm_' ) ) {
				        continue;
                    }

				    echo format_inbody_meta_line( $key, $datum[0] );
                }

				thin_the_posted_in();
				?>
                </ul>
			</article>
			<?php
			}
		}
		?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
