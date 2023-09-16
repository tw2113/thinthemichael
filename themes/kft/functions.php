<?php

namespace kft;

/**
 * Enqueue the style.css file.
 * @since 1.0.0
 */
function kft_styles() {
	wp_enqueue_style(
		'fse-style',
		get_stylesheet_uri(),
		[],
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\kft_styles' );

function kft_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\kft_setup' );

function kft_footer() {
	if ( ! is_page( 'charts' ) ) { ?>
		<script>
			let quotes = [
				'"Let\'s go row."',
				'"Come with me if you want to lift"',
				'"Gonna watch any football tonight?"',
				'"Let\'s go do lunges"',
				'"Keep your core tight"'
			];
			let random = quotes[Math.floor(Math.random() * quotes.length)];
			document.querySelector('#quotecontainer').innerHTML = random;
		</script>
	<?php
	}
}
add_action( 'wp_footer', __NAMESPACE__ . '\kft_footer' );

function kft_tracking() {
?>
	<!-- Matomo -->
	<script type="text/javascript">
		var _paq = window._paq || [];
		/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
		_paq.push(['trackPageView']);
		_paq.push(['enableLinkTracking']);
		(function () {
			var u = "//trexthepirate.com/traffic/";
			_paq.push(['setTrackerUrl', u + 'matomo.php']);
			_paq.push(['setSiteId', '2']);
			var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
			g.type = 'text/javascript';
			g.async = true;
			g.defer = true;
			g.src = u + 'matomo.js';
			s.parentNode.insertBefore(g, s);
		})();
	</script>
	<!-- End Matomo Code -->
<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\kft_tracking' );

function kft_inbody() {
	if ( ! is_page( 'charts' ) ) {
		return;
	}

	$inbody = new \thinthemichael\inbody();
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'flot', get_stylesheet_directory_uri() . '/assets/js/flot/jquery.flot.js', [ 'jquery' ] );
	wp_enqueue_script( 'flot-axislabels', get_stylesheet_directory_uri() . '/assets/js/jquery.flot.axislabels.js', [
		'jquery',
		'flot'
	] );
	wp_enqueue_script( 'flot-time', get_stylesheet_directory_uri() . '/assets/js/flot/jquery.flot.time.js', [
		'jquery',
		'flot'
	] );
	wp_localize_script( 'flot', 'inbody_data', $inbody->get_data() );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\kft_inbody' );

function kft_noindex_sidebar_page() {
	if ( ! is_page( 'sidebar' ) ) {
		return;
	}
?>
<meta name="robots" content="noindex">
<?php
}
add_action( 'wp_head', __NAMESPACE__ . '\kft_noindex_sidebar_page' );

function kft_charts_header() {
	if ( ! is_page( 'charts' ) ) {
		return;
	}
?>
	<style>
		.chart {
			height: 500px;
			width: 100%;
		}
	</style>
<?php
}
add_action( 'wp_head', __NAMESPACE__ . '\kft_charts_header' );

function kft_charts_footer() {
	if ( ! is_page( 'charts' ) ) {
		return;
	}
	?>
	<script>
		(function ($) {
			function plotdata(event, pos, item) {
				if (item) {
					var weight, date, tip;
					weight = item.datapoint[1];
					date = new Date(item.datapoint[0]).toISOString().substring(0, 10);
					tip = weight + ' on ' + date;
					$("#tooltip").html(tip)
						.css({top: pos.pageY + 5, left: pos.pageX + 5})
						.fadeIn(200);
				} else {
					$("#tooltip").hide();
				}
			}

			var options = {
				xaxes : [{
					mode      : "time",
					position  : 'bottom',
					timeformat: "%Y-%m-%d",
					timezone  : "timezone",
				}],
				series: {
					lines : {
						show: true,
					},
					points: {
						show: true,
					},
					color : 'rgb(255,0,0)',
				},
				yaxes : [{
					position : 'left',
					axisLabel: '',
				}],
				grid  : {
					hoverable: true,
				},
				legend: {
					show: false
				}
			};
			options.yaxes[0].axisLabel = 'Dry Lean Mass(lbs)';
			$.plot("#dry_lean_mass", [{label: "Dry Lean Mass", data: inbody_data.dry_lean_mass}], options);
			$('#dry_lean_mass').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Body Fat Mass(lbs)';
			$.plot("#body_fat_mass", [{label: "Body Fat Mass", data: inbody_data.body_fat_mass}], options);
			$('#body_fat_mass').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Fat Mass Control(lbs)';
			$.plot("#fat_mass_control", [{label: "Fat Mass Control", data: inbody_data.fat_mass_control}], options);
			$('#fat_mass_control').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Basal Metabolic Rate';
			$.plot("#basal_metabolic_rate", [{
				label: "Basal Metabolic Rate",
				data : inbody_data.basal_metabolic_rate
			}], options);
			$('#basal_metabolic_rate').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Visceral Body Fat';
			$.plot("#visceral_fat", [{label: "Visceral Body Fat", data: inbody_data.visceral_fat}], options);
			$('#visceral_fat').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Body Weight(lbs)';
			$.plot("#body_weight", [{label: "Body Weight", data: inbody_data.body_weight}], options);
			$('#body_weight').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Skeletal Muscle Mass(lbs)';
			$.plot("#smm", [{label: "Skeletal Muscle Mass", data: inbody_data.smm}], options);
			$('#smm').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Body Mass Index (kg/m^2)';
			$.plot("#bmi", [{label: "Body Mass Index", data: inbody_data.bmi}], options);
			$('#bmi').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Percent Body Fat';
			$.plot("#pbf", [{label: "Percent Body Fat", data: inbody_data.pbf}], options);
			$('#pbf').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Right Arm Weight(lbs)';
			$.plot("#right_arm", [{label: "Right Arm", data: inbody_data.right_arm}], options);
			$('#right_arm').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Left Arm Weight(lbs)';
			$.plot("#left_arm", [{label: "Left Arm", data: inbody_data.left_arm}], options);
			$('#left_arm').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Trunk(lbs)';
			$.plot("#trunk", [{label: "Trunk", data: inbody_data.trunk}], options);
			$('#trunk').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Right Leg Weight(lbs)';
			$.plot("#right_leg", [{label: "Right Leg", data: inbody_data.right_leg}], options);
			$('#right_leg').bind('plothover', plotdata);

			options.yaxes[0].axisLabel = 'Left Leg Weight(lbs)';
			$.plot("#left_leg", [{label: "Left Leg", data: inbody_data.left_leg}], options);
			$('#left_leg').bind('plothover', plotdata);

			$("<div id='tooltip'></div>").css({
				position          : "absolute",
				display           : "none",
				border            : "1px solid #fdd",
				padding           : "2px",
				"background-color": "#fee",
				fontSize          : '20px'
			}).appendTo("body");
		})(jQuery);
	</script>
	<?php
}

add_action( 'wp_footer', __NAMESPACE__ . '\kft_charts_footer' );

function kft_includes() {
	require_once get_stylesheet_directory() . '/inc/shortcodes.php';
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\kft_includes' );

function sidebar() {
	$page = get_page_by_path( 'sidebar' );

	if ( ! empty( $page ) ) {
		return $page->post_content;
	}

	return '';
}
add_shortcode( 'sidebar', __NAMESPACE__ . '\sidebar' );

function block_editor_full_width() {
?>
<style>
    .editor-styles-wrapper .wp-block-post-content {
        max-width: 90%;
        width: 90%;
        margin: 0 auto;
    }
</style>
<?php
}
add_action('admin_head', __NAMESPACE__ . '\block_editor_full_width');