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
				the_excerpt();

				thin_the_posted_in();
				?>
			</article>
			<?php
			}
		}
		?>

		<nav class="pagination">
			<span class="prev">
				<?php previous_posts_link(); ?>
			</span>
			<span class="next">
				<?php next_posts_link(); ?>
			</span>
		</nav>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
