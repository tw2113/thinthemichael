<?php
/*
 * Plugin Name: ThinTheMichael CMB2
 * Plugin URI: https://trexthepirate.com/thinthemichael
 * Description: CMB2 setup
 * Version: 1.0.0
 * Author: Michael Beckwith
 * Author URI: http://michaelbox.net
 * License: GPLv2
 */



add_action( 'cmb2_admin_init', function() {

	$prefix = '_ttm_';
	$cmb = new_cmb2_box( [
		'id'           => $prefix . 'inbody',
		'title'        => 'InBody Data',
		'object_types' => array( 'inbody' ),
		'context'      => 'normal',
		'priority'     => 'default',
		'show_names'   => true,
	] );

	$cmb->add_field( [
		'name'             => 'InBody Test Source',
		'type'             => 'select',
		'id'               => $prefix . 'test_source',
		'show_option_none' => true,
		'options'          => [
			'completenutrition' => 'Complete Nutrition',
			'sciencenutrition'  => 'Science Nutrition',
			'completefitness'   => 'Complete Fitness',
		]
	] );

	$cmb->add_field( [
		'name' => 'Body Composition Analysis',
		'type' => 'title',
		'id'   => 'body_composition_analysis',
	] );

	$cmb->add_field( [
		'name' => 'Total Body Water',
		'id'   => $prefix . 'total_body_water',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Dry Lean Mass',
		'id'   => $prefix . 'dry_lean_mass',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Body Fat Mass',
		'id'   => $prefix . 'body_fat_mass',
		'type' => 'text_medium'
	] );

	/**
	 *
	 */
	$cmb->add_field( [
		'name' => 'Body Fat - Lean Body Mass Control',
		'type' => 'title',
		'id'   => 'lean_body_mass_control',
		'desc' => 'Upper right corner',
	] );

	$cmb->add_field( [
		'name' => 'Body Fat Mass Control',
		'id'   => $prefix . 'body_fat_mass_control',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Basal Metabolic Rate',
		'id'   => $prefix . 'basal_metabolic_rate',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Visceral Fat Level',
		'id'   => $prefix . 'visceral_fat',
		'type' => 'text_medium'
	] );

	/**
	 *
	 */
	$cmb->add_field( [
		'name' => 'Muscle-Fat Analysis',
		'type' => 'title',
		'id'   => 'muscle-fat-analysis',
	] );

	$cmb->add_field( [
		'name' => 'Weight',
		'id'   => $prefix . 'muscle_analysis_weight',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'SMM',
		'id'   => $prefix . 'muscle_analysis_smm',
		'type' => 'text_medium'
	] );

	/**
	 *
	 */
	$cmb->add_field( [
		'name' => 'Obesity Analysis',
		'type' => 'title',
		'id'   => 'obesity-analysis',
	] );

	$cmb->add_field( [
		'name' => 'BMI',
		'id'   => $prefix . 'obesity_analysis_bmi',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'PBF',
		'id'   => $prefix . 'obesity_analysis_pbf',
		'type' => 'text_medium'
	] );

	/**
	 *
	 */
	$cmb->add_field( [
		'name' => 'Segmental Lean Analysis',
		'type' => 'title',
		'id'   => 'segmental-lean-analysis',
	] );

	$cmb->add_field( [
		'name' => 'Right Arm',
		'id'   => $prefix . 'right_arm',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Left Arm',
		'id'   => $prefix . 'left_arm',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Trunk',
		'id'   => $prefix . 'trunk',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Right Leg',
		'id'   => $prefix . 'right_leg',
		'type' => 'text_medium'
	] );

	$cmb->add_field( [
		'name' => 'Left Leg',
		'id'   => $prefix . 'left_leg',
		'type' => 'text_medium'
	] );
} );

add_filter( 'manage_edit-inbody_columns', function( $columns ) {

	// Want to leave these at the end, unset for the moment.
	$date = $columns['date'];
	unset( $columns['date'] );

    $columns['inbody_weight'] = 'Weight (lbs)';
    $columns['inbody_smm']    = 'SMM (lbs)';
    $columns['inbody_pbf']    = 'PBF (%)';
    $columns['test_source']   = 'Source';

    // Reattach our date column.
    $columns['date'] = $date;

    return $columns;
} );

add_action( 'manage_inbody_posts_custom_column', function( $column_name, $post_id ) {
	if ( $column_name === 'inbody_weight' ) {
		echo get_post_meta( $post_id, '_ttm_muscle_analysis_weight', true );
	}
	if ( $column_name === 'inbody_smm' ) {
		echo get_post_meta( $post_id, '_ttm_muscle_analysis_smm', true );
	}
	if ( $column_name === 'inbody_pbf' ) {
		echo get_post_meta( $post_id, '_ttm_obesity_analysis_pbf', true );
	}
	if ( $column_name === 'test_source' ) {
		echo get_post_meta( $post_id, '_ttm_test_source', true );
	}

}, 1, 2);

function format_inbody_meta_line( $key_field, $field_value ) {
	$key_field_trimmed = str_replace( [ '_ttm_', '_' ], ['', ' ' ], $key_field );
	$key_field_clean   = ucwords( $key_field_trimmed );

	return sprintf(
		'<li><strong>%s</strong>: %s</li>',
		$key_field_clean,
		$field_value
	);
}