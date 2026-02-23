<?php
// This file is generated. Do not modify it manually.
return array(
	'rakuten' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'product-link-maker/rakuten',
		'version' => '0.1.0',
		'title' => '楽天商品リンク',
		'category' => 'product-link-maker',
		'icon' => 'cart',
		'description' => '楽天商品のアフィリエイトリンクを表示するブロックです。',
		'example' => array(
			'attributes' => array(
				'id' => 'book:11830886',
				'kw' => 'サンプル商品',
				'title' => 'サンプル商品タイトル',
				'price' => '1,980',
				'imageUrl' => 'https://placehold.co/300x300/e8e8e8/666?text=Product+Image',
				'desc' => '商品の説明文をここに入力できます',
				'shopName' => '楽天ブックス',
				'showShop' => true,
				'showRakuten' => true,
				'showAmazon' => true,
				'showYahoo' => true,
				'showMercari' => true,
				'showDmm' => true
			)
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
			'showImage' => array(
				'type' => 'boolean',
				'default' => true
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
