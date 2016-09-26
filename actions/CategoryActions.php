<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListCategoriesAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Category');
        $categories = $mapper->all();
        return $response->withJson($categories, 200);
    }
}

class CreateCategoryAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Category');
        $category = $mapper->create($data);
        return $response->withJson($category, 201);
    }
}

class ChangeCategoryAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Category');

        $category = $mapper->get($args['id']);
        $category->name = $data['name'];
        $category->price = $data['price'];
        $category->price_reduced = $data['price_reduced'];
        $mapper->update($category);

        return $response->withJson($category, 200);
    }
}

class DeleteCategoryAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Category');
        $mapper->delete([ 'id' => $args['id'] ]);
        return $response->withJson(200);
    }
}