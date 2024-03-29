<?php
/*
 * Template Name: Charts
 */

$inbody = new \thinthemichael\inbody();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'flot', get_stylesheet_directory_uri() . '/assets/js/flot/jquery.flot.js', [ 'jquery' ] );
wp_enqueue_script( 'flot-axislabels', get_stylesheet_directory_uri() . '/assets/js/jquery.flot.axislabels.js', [ 'jquery', 'flot' ] );
wp_enqueue_script( 'flot-time', get_stylesheet_directory_uri() . '/assets/js/flot/jquery.flot.time.js', [ 'jquery', 'flot' ] );
wp_localize_script( 'flot', 'inbody_data', $inbody->get_data() );

get_header();
?>
<div class="row-fluid">
	<div class="span12">

		<?php
		if ( have_posts() ) {
			while( have_posts() ) {
				the_post();
				echo '<h1>' . get_the_title() . '</h1>';

				the_content();
			}
		}

        $data = $inbody->get_data();
		?>
        Scans by year:
        <?php
            $year_strings = [];
            foreach ( $data['years'] as $year => $count ) {
                $year_strings[] = $year . ': <strong>' . $count . '</strong>';
            }
            echo implode( ', ', $year_strings );
        ?>
        <hr/>
        <br/>
        Data Style:
        <a href="<?php echo esc_url( get_permalink() ); ?>">Charts</a> - <a href="<?php echo esc_url( add_query_arg( [ 'style' => 'table' ], get_permalink() ) ); ?>">Table</a>

        <?php if ( ! empty( $_GET['style'] ) && 'table' === $_GET['style'] ) {
            $scans = $inbody->get_scans();
            $keys = [
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
                    foreach( $scans as $date => $metrics ) {
                        echo '<tr>';
                        echo '<td>' . $date . '</td>';
                        foreach( $metrics as $metric ) {
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
        <?php } ?>
    </div>
</div>
<style>
.chart {
	height: 500px;
	width: 100%;
}
</style>
<script>
(function($) {
	function plotdata(event, pos, item) {
		if(item){
			var weight, date, tip;
			weight = item.datapoint[1];
			date = new Date( item.datapoint[0] ).toISOString().substring(0, 10);
			tip = weight+' on '+date;
			$("#tooltip").html(tip)
					.css({top: pos.pageY+5, left: pos.pageX+5})
					.fadeIn(200);
		} else {
			$("#tooltip").hide();
		}
	}
	var options = {
		xaxes: [{
			mode: "time",
			position: 'bottom',
			timeformat: "%Y-%m-%d",
			timezone: "timezone",
		}],
		series: {
			lines: {
				show: true,
			},
			points: {
				show: true,
			},
			color: 'rgb(255,0,0)',
		},
		yaxes: [{
			position: 'left',
			axisLabel: '',
		}],
		grid: {
			hoverable: true,
		},
        legend: {
            show: false
        }
	};
	options.yaxes[0].axisLabel = 'Dry Lean Mass(lbs)';
	$.plot("#dry_lean_mass", [ { label: "Dry Lean Mass", data: inbody_data.dry_lean_mass } ], options );
	$('#dry_lean_mass').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Body Fat Mass(lbs)';
	$.plot("#body_fat_mass", [ { label: "Body Fat Mass", data: inbody_data.body_fat_mass } ], options );
	$('#body_fat_mass').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Fat Mass Control(lbs)';
	$.plot("#fat_mass_control", [ { label: "Fat Mass Control", data: inbody_data.fat_mass_control } ], options );
	$('#fat_mass_control').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Basal Metabolic Rate';
	$.plot("#basal_metabolic_rate", [ { label: "Basal Metabolic Rate", data: inbody_data.basal_metabolic_rate } ], options );
	$('#basal_metabolic_rate').bind('plothover', plotdata );

    options.yaxes[0].axisLabel = 'Visceral Body Fat';
    $.plot("#visceral_fat", [ { label: "Visceral Body Fat", data: inbody_data.visceral_fat } ], options );
    $('#visceral_fat').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Body Weight(lbs)';
	$.plot("#body_weight", [ { label: "Body Weight", data: inbody_data.body_weight } ], options );
	$('#body_weight').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Skeletal Muscle Mass(lbs)';
	$.plot("#smm", [ { label: "Skeletal Muscle Mass", data: inbody_data.smm } ], options );
	$('#smm').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Body Mass Index (kg/m^2)';
	$.plot("#bmi", [ { label: "Body Mass Index", data: inbody_data.bmi } ], options );
	$('#bmi').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Percent Body Fat';
	$.plot("#pbf", [ { label: "Percent Body Fat", data: inbody_data.pbf } ], options );
	$('#pbf').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Right Arm Weight(lbs)';
	$.plot("#right_arm", [ { label: "Right Arm", data: inbody_data.right_arm } ], options );
	$('#right_arm').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Left Arm Weight(lbs)';
	$.plot("#left_arm", [ { label: "Left Arm", data: inbody_data.left_arm } ], options );
	$('#left_arm').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Trunk(lbs)';
	$.plot("#trunk", [ { label: "Trunk", data: inbody_data.trunk } ], options );
	$('#trunk').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Right Leg Weight(lbs)';
	$.plot("#right_leg", [ { label: "Right Leg", data: inbody_data.right_leg } ], options );
	$('#right_leg').bind('plothover', plotdata );

	options.yaxes[0].axisLabel = 'Left Leg Weight(lbs)';
	$.plot("#left_leg", [ { label: "Left Leg", data: inbody_data.left_leg } ], options );
	$('#left_leg').bind('plothover', plotdata );

	$("<div id='tooltip'></div>").css({
		position: "absolute",
		display: "none",
		border: "1px solid #fdd",
		padding: "2px",
		"background-color": "#fee",
        fontSize: '20px'
	}).appendTo("body");
})(jQuery);
</script>
<?php
get_footer();
