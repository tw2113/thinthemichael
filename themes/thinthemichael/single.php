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
				<?php
				the_content();

				thin_the_posted_in();
				?>
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
