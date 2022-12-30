<?php
/*
 * Plugin Name: ThinTheMichael InBody
 * Plugin URI: https://trexthepirate.com/thinthemichael
 * Description: InBody data collecting
 * Version: 1.0.0
 * Author: Michael Beckwith
 * Author URI: http://michaelbox.net
 * License: WTFPL
 */

namespace thinthemichael;

/**
 * Class for querying our InBody Composition test data.
 *
 * @since 1.0.0
 */
class inbody {

	private $args = [];

	private $inbody;

	public function __construct() {
		$this->args = [
			'post_type'      => 'inbody',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];
		$this->inbody = new \WP_Query( $this->args );
	}

	public function get_fields() {
		return [
			'_ttm_total_body_water',
			'_ttm_dry_lean_mass',
			'_ttm_body_fat_mass_control',
			'_ttm_body_fat_mass',
			'_ttm_lean_body_mass_control',
			'_ttm_basal_metabolic_rate',
			'_ttm_visceral_fat',
			'_ttm_muscle_analysis_weight',
			'_ttm_muscle_analysis_smm',
			'_ttm_obesity_analysis_bmi',
			'_ttm_obesity_analysis_pbf',
			'_ttm_right_arm',
			'_ttm_left_arm',
			'_ttm_trunk',
			'_ttm_right_leg',
			'_ttm_left_leg'
		];
	}

	public function get_data(): array {
		$data = [];

		$data['body_water']           = $this->get_body_water();
		$data['dry_lean_mass']        = $this->get_dry_lean_mass();
		$data['body_fat_mass']        = $this->get_body_fat_mass();
		$data['fat_mass_control']     = $this->get_fat_mass_control();
		$data['basal_metabolic_rate'] = $this->get_basal_metabolic_rate();
		$data['visceral_fat']         = $this->get_visceral_fat();
		$data['body_weight']          = $this->get_weight();
		$data['smm']                  = $this->get_smm();
		$data['bmi']                  = $this->get_bmi();
		$data['pbf']                  = $this->get_pbf();
		$data['right_arm']            = $this->get_right_arm();
		$data['left_arm']             = $this->get_left_arm();
		$data['trunk']                = $this->get_trunk();
		$data['right_leg']            = $this->get_right_leg();
		$data['left_leg']             = $this->get_left_leg();
		$data['years']                = $this->get_years();

		return $data;
	}

	private function get_years() {
		if ( ! $this->inbody->have_posts() ) {
			return '';
		}

		$years = [];

		foreach ( $this->inbody->posts as $post ) {
			$years[] = date( 'Y', strtotime( $post->post_date . ' midnight' ) * 1000 );
		}

		return array_count_values( $years );
	}

	private function get_metric( $metric ) {
		if ( ! $this->inbody->have_posts() ) {
			return '';
		}

		$key = '_ttm_' . $metric;
		$metrics = [];

		foreach( $this->inbody->posts as $post ) {
			$date = ( strtotime( $post->post_date . ' midnight' ) * 1000 );
			$metric = get_post_meta( $post->ID, $key, true );
			if ( ! empty( $metric ) ) {
				$metrics[] = [ $date, $metric ];
			}
		}
		return $metrics;
	}

	private function get_body_water() {
		return $this->get_metric( 'total_body_water' );
	}
	private function get_dry_lean_mass() {
		return $this->get_metric( 'dry_lean_mass' );
	}
	private function get_body_fat_mass() {
		return $this->get_metric( 'body_fat_mass' );
	}
	private function get_fat_mass_control() {
		return $this->get_metric( 'body_fat_mass_control' );
	}
	private function get_basal_metabolic_rate() {
		return $this->get_metric( 'basal_metabolic_rate' );
	}
	private function get_visceral_fat() {
		return $this->get_metric( 'visceral_fat' );
	}
	private function get_weight() {
		return $this->get_metric( 'muscle_analysis_weight' );
	}
	private function get_smm() {
		return $this->get_metric( 'muscle_analysis_smm' );
	}
	private function get_bmi() {
		return $this->get_metric( 'obesity_analysis_bmi' );
	}
	private function get_pbf() {
		return $this->get_metric( 'obesity_analysis_pbf' );
	}
	private function get_right_arm() {
		return $this->get_metric( 'right_arm' );
	}
	private function get_left_arm() {
		return $this->get_metric( 'left_arm' );
	}
	private function get_trunk() {
		return $this->get_metric( 'trunk' );
	}
	private function get_right_leg() {
		return $this->get_metric( 'right_leg' );
	}
	private function get_left_leg() {
		return $this->get_metric( 'left_leg' );
	}
}

