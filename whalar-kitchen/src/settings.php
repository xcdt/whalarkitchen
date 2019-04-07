<?php
return [
	'settings' => [
		'displayErrorDetails' => true, // set to false in production

        'view' => [
            'path' => __DIR__ . '/resources/views',
            'twig' => [
                'cache' => false ]
            ],

		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],
		'penModel' => [
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],

		//Cookbook Settings
		'elasticsearch' => [
			'host' => 'elasticsearch',
			'port' => '9200',
			'scheme' => 'http',
			'user' => '',
			'pass' => ''
		],
	],
];
