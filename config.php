<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

/**
 * Default item widths for all builders
 *
 * It is better to use fw_ext_builder_get_item_width() function to retrieve the item widths
 * because it has a filter and users will be able to customize the widths for a specific builder
 *
 * @see fw_ext_builder_get_item_width()
 * @since 1.2.0
 *
 * old $cfg['default_item_widths'] https://github.com/ThemeFuse/Unyson-Builder-Extension/issues/8
 * https://github.com/ThemeFuse/Unyson-Builder-Extension/blob/v1.1.17/config.php#L13
 */
$cfg['grid.columns'] = array(
	'1_12' => array(
		'title'          => '1/12',
		'backend_class'  => 'fw-col-sm-1',
		'frontend_class' => 'fw-col-12 fw-col-sm-1',
	),
	'1_6' => array(
		'title'          => '1/6',
		'backend_class'  => 'fw-col-sm-2',
		'frontend_class' => 'fw-col-12 fw-col-sm-2',
	),
	'1_5' => array(
		'title'          => '1/5',
		'backend_class'  => 'fw-col-sm-15',
		'frontend_class' => 'fw-col-12 fw-col-sm-15',
	),
	'1_4' => array(
		'title'          => '1/4',
		'backend_class'  => 'fw-col-sm-3',
		'frontend_class' => 'fw-col-12 fw-col-sm-3',
	),
	'1_3' => array(
		'title'          => '1/3',
		'backend_class'  => 'fw-col-sm-4',
		'frontend_class' => 'fw-col-12 fw-col-sm-4',
	),
	'5_12' => array(
		'title'          => '5/12',
		'backend_class'  => 'fw-col-sm-5',
		'frontend_class' => 'fw-col-12 fw-col-sm-5',
	),
	'1_2' => array(
		'title'          => '1/2',
		'backend_class'  => 'fw-col-sm-6',
		'frontend_class' => 'fw-col-12 fw-col-sm-6',
	),
	'7_12' => array(
		'title'          => '7/12',
		'backend_class'  => 'fw-col-sm-7',
		'frontend_class' => 'fw-col-12 fw-col-sm-7',
	),
	'2_3' => array(
		'title'          => '2/3',
		'backend_class'  => 'fw-col-sm-8',
		'frontend_class' => 'fw-col-12 fw-col-sm-8',
	),
	'3_4' => array(
		'title'          => '3/4',
		'backend_class'  => 'fw-col-sm-9',
		'frontend_class' => 'fw-col-12 fw-col-sm-9',
	),
	'5_6' => array(
		'title'          => '5/6',
		'backend_class'  => 'fw-col-sm-10',
		'frontend_class' => 'fw-col-12 fw-col-sm-10',
	),
	'11_12' => array(
		'title'          => '11/12',
		'backend_class'  => 'fw-col-sm-11',
		'frontend_class' => 'fw-col-12 fw-col-sm-11',
	),
	'1_1' => array(
		'title'          => '1/1',
		'backend_class'  => 'fw-col-sm-12',
		'frontend_class' => 'fw-col-12',
	),
	'col' => array(
		'title'          => __( 'Auto Column', 'fw' ),
		'backend_class'  => 'fw-col',
		'frontend_class' => 'fw-col',
	),
);

/**
 * @since 1.2.0
 */
$cfg['grid.row.class'] = 'fw-row';

/**
 * @deprecated since 1.2.0
 * if this is empty fw_ext_builder_get_item_width() will use $cfg['grid.columns']
 */
$cfg['default_item_widths'] = false;
