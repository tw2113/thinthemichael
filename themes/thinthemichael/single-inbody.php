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
                <div class="scan-wrapper">
                    <div class="scan-previous">
                    <h2><?php esc_html_e( 'Previous Scan', 'thinthemichael' ); ?></h2>
                    <ul>
                    <?php
                        $previous = get_adjacent_post();
                        $data_prev = get_post_meta( $previous->ID );
                        echo '<li>' . date( 'm-d-Y', strtotime( $previous->post_date ) ) . '</li>';
                        foreach( $data_prev as $key => $datum ) {
                            if ( $key === '_ttm_body_fat_mass' ) {
                                continue;
                            }
                            if ( false === strpos( $key, '_ttm_' ) ) {
                                continue;
                            }

                            echo format_inbody_meta_line( $key, $datum[0] );
                        }
                    ?>
                    </ul>
                    </div>
                    <div class="scan-current">
	                <h2><?php esc_html_e( 'Current', 'thinthemichael' ); ?></h2>
                    <ul>
                    <?php
                        $data = get_post_meta( get_the_ID() );
	                    echo '<li>&nbsp;</li>';
                        foreach( $data as $key => $datum ) {
                            if ( $key === '_ttm_body_fat_mass' ) {
                                continue;
                            }
                            if ( false === strpos( $key, '_ttm_' ) ) {
                                continue;
                            }

                            echo format_inbody_meta_line( $key, $datum[0] );
                        }
                    ?>
                    </ul>
                    </div>
                </div>
                <?php thin_the_posted_in(); ?>
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
