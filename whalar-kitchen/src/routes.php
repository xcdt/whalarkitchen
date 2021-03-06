<?php

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    $app->post(
        '/search/', function (Request $request, Response $response, array $args) {
            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->Search($request, $response);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
            'base' => [
                'url' => $baseUrl,
                'results' => $results
            ],
            ];

            return $this->view->render($response, 'home.twig', $vars);
        }
    )->setName('search');

    //Get recipe by id

    $app->get(
        '/recipe/[{name}]', function (Request $request, Response $response, array $args) {
            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->GetRecipe($request, $response);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
            'base' => [
                'url' => $baseUrl,
                'results' => $results
            ],
            ];

            return $this->view->render($response, 'recipe.twig', $vars);
        }
    )->setName('recipe');

    //Delete recipe

    $app->post(
        '/delete', function (Request $request, Response $response, array $args) {
            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->DeleteRecipe($request, $response);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
            'base' => [
                'url' => $baseUrl,
                'results' => $results
            ],
            ];

            return $this->view->render($response, 'result.twig', $vars);
        }
    )->setName('delete');

    //Add recipe: this should be post, but for the sake of our example I'll code it so the recipe can be added by simpling typing base_url/add

    $app->get(
        '/add', function (Request $request, Response $response, array $args) {
            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->CreateRecipe($request, $response);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
                'base' => [
                    'url' => $baseUrl,
                    'results' => $results
                ],
            ];

            return $this->view->render($response, 'result.twig', $vars);
        }
    )->setName('add');

    //Update recipe

    $app->post(
        '/update', function (Request $request, Response $response, array $args) {
            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->UpdateRecipe($request, $response);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
            'base' => [
                'url' => $baseUrl,
                'results' => $results
            ],
            ];

            return $this->view->render($response, 'result.twig', $vars);
        }
    )->setName('update');

    // Home route

    $app->get(
        '/', function (Request $request, Response $response, $args) {

            $controller = new \Kitchen\Controllers\Cookbook($this);
            $results = $controller->Search($request, $response, 1);
            $uri = $request->getUri();
            $baseUrl = $uri->getHost();
            $vars = [
            'page' => [
                'title' => 'Welcome to the awesome Whalar cookbook!.',
            ],
            'base' => [
                'url' => $baseUrl,
                'results' => $results
            ],
            ];
            return $this->view->render($response, 'home.twig', $vars);

        }
    )->setName('home');


