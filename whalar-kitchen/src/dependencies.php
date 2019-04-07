<?php
// DIC configuration

$container = $app->getContainer();



// view renderer
$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['penModel'] = new Kitchen\Factories\PenModelFactory();

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

// Twig view dependency
$container['view'] = function ($c) {

    $cf = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($cf['path'], $cf['twig']);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->router,
        $c->request->getUri()
    ));

    return $view;
};

// elasticsearch
$container['elasticsearch'] = function ($c) {
	$settings = $c->get('settings')['elasticsearch'];
	return new Kitchen\Models\Cookbook($settings);
};
