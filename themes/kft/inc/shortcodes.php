<?php
namespace kft;

function inbody_output() {

	ob_start();
	$inbody = new \thinthemichael\inbody();
	$data   = $inbody->get_data();
	?>
	Scans by year:
	<?php
	$year_strings = [];
	foreach ( $data['years'] as $year => $count ) {
		$year_strings[] = $year . ': <strong>' . $count . '</strong>';
	}
	echo implode( ', ', $year_strings );
	?>
	<hr />
	<br />
	Data Style:
	<a href="<?php echo esc_url( get_permalink() ); ?>">Charts</a> -
	<a href="<?php echo esc_url( add_query_arg( [ 'style' => 'table' ], get_permalink() ) ); ?>">Table</a>

	<?php if ( ! empty( $_GET['style'] ) && 'table' === $_GET['style'] ) {
		$scans = $inbody->get_scans();
		$keys  = [
			'muscle_analysis_weight',
			'muscle_analysis_smm',
			'obesity_analysis_pbf',
			'dry_lean_mass',
			'body_fat_mass',
			'body_fat_mass_control',
			'basal_metabolic_rate',
			'visceral_fat',
			'obesity_analysis_bmi',
			'right_arm',
			'left_arm',
			'trunk',
			'right_leg',
			'left_leg',
		];
		?>
		<table class="scan-data">
			<thead>
			<tr>
				<th>Date</th>
				<th>Weight</th>
				<th>SMM</th>
				<th>PBF</th>
				<th>Dry Lean</th>
				<th>Body Fat</th>
				<th>Fat Mass Control</th>
				<th>BMR</th>
				<th>Visceral</th>
				<th>BMI</th>
				<th>R Arm</th>
				<th>L Arm</th>
				<th>Trunk</th>
				<th>R Leg</th>
				<th>L Leg</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Date</th>
				<th>Weight</th>
				<th>Skel Muscle Mass</th>
				<th>% Body Fat</th>
				<th>Dry Lean Mass</th>
				<th>Body Fat Mass</th>
				<th>Fat Mass Control</th>
				<th>BMR</th>
				<th>Visceral</th>
				<th>BMI</th>
				<th>R Arm</th>
				<th>L Arm</th>
				<th>Trunk</th>
				<th>R Leg</th>
				<th>L Leg</th>
			</tr>
			</tfoot>
			<tbody>
			<?php
			foreach ( $scans as $date => $metrics ) {
				echo '<tr>';
				echo '<td>' . $date . '</td>';
				foreach ( $metrics as $metric ) {
					echo '<td>' . $metric . '</td>';
				}
				echo '</tr>';
			}
			?>
			</tbody>

		</table>
	<?php } else {
		?>
		<h2>Body Weight</h2>
		<div id="body_weight" class="chart"></div>
		<h2>Skeletal Muscle Mass</h2>
		<div id="smm" class="chart"></div>
		<h2>Percent Body Fat</h2>
		<div id="pbf" class="chart"></div>
		<h2>Dry Lean Mass</h2>
		<div id="dry_lean_mass" class="chart"></div>
		<h2>Body Fat Mass</h2>
		<div id="body_fat_mass" class="chart"></div>
		<h2>Fat Mass Control</h2>
		<p>The more negative the number, the more they're recommending to lose. Want to be as close to zero as can be.</p>
		<div id="fat_mass_control" class="chart"></div>
		<h2>Basal Metabolic Rate</h2>
		<p>The higher the number, the more calories my body will burn even when sedentary.</p>
		<div id="basal_metabolic_rate" class="chart"></div>
		<h2>Visceral Body Fat</h2>
		<p>Not all body scans had a visceral fat value.</p>
		<div id="visceral_fat" class="chart"></div>
		<h2>Body Mass Index</h2>
		<div id="bmi" class="chart"></div>
		<h2>Individual body segments</h2>
		<h3>Right arm</h3>
		<div id="right_arm" class="chart"></div>
		<h3>Left arm</h3>
		<div id="left_arm" class="chart"></div>
		<h3>Trunk/Core</h3>
		<div id="trunk" class="chart"></div>
		<h3>Right leg</h3>
		<div id="right_leg" class="chart"></div>
		<h3>Left leg</h3>
		<div id="left_leg" class="chart"></div>
	<?php }
	return ob_get_clean();
}
add_shortcode( 'inbody', __NAMESPACE__ . '\inbody_output' );

function single_inbody_output() {
	ob_start();
	?>
	<div class="scan-wrapper">
		<div class="scan-previous">
			<h2><?php esc_html_e( 'Previous Scan', 'thinthemichael' ); ?></h2>
			<ul>
				<?php
				$previous  = get_adjacent_post();
				$data_prev = get_post_meta( $previous->ID );
				echo '<li>' . date( 'm-d-Y', strtotime( $previous->post_date ) ) . '</li>';
				foreach ( $data_prev as $key => $datum ) {
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
				foreach ( $data as $key => $datum ) {
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
<?php
	return ob_get_clean();
}
add_shortcode( 'single-inbody', __NAMESPACE__ . '\single_inbody_output' );
