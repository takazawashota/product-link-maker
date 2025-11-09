<?php
// This file is generated. Do not modify it manually.
return array(
	'rakuten' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/rakuten',
		'version' => '0.1.0',
		'title' => 'Rakuten',
		'category' => 'widgets',
		'icon' => 'smiley',
		'description' => 'Example block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'product-link-maker',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'no' => array(
				'type' => 'string',
				'default' => ''
			),
			'kw' => array(
				'type' => 'string',
				'default' => ''
			),
			'shop' => array(
				'type' => 'string',
				'default' => ''
			),
			'search' => array(
				'type' => 'string',
				'default' => ''
			),
			'title' => array(
				'type' => 'string',
				'default' => ''
			),
			'price' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showShop' => array(
				'type' => 'boolean',
				'default' => true
			),
			'desc' => array(
				'type' => 'string',
				'default' => ''
			),
			'imageUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'showAmazon' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showRakuten' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showYahoo' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showMercari' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showDmm' => array(
				'type' => 'boolean',
				'default' => true
			),
			'customButtonsBefore' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'customButtonsAfter' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		)
	)
);
