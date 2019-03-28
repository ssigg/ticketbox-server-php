<?php

class CategoryActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $eventblockMerger = $this->getMockBuilder(EventblockMergerInterface::class)
            ->setMethods(['merge'])
            ->getMock();
        $this->container['eventblockMerger'] = $eventblockMerger;
    }

    public function testListCategoriesAction() {
        $action = new Actions\ListCategoriesAction($this->container);

        $request = $this->getGetRequest('/categories');
        $response = new \Slim\Http\Response;

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"name":"Category 1","color":"#000","price":2,"price_reduced":1}]',
            (string)$response->getBody());
    }

    public function testCreateCategoryAction() {
        $action = new Actions\CreateCategoryAction($this->container);

        $category = [
            'name' => 'Test name',
            'color' => '#fff',
            'price' => 10,
            'price_reduced' => 5 ];
        $request = $this->getPostRequest('/categories', $category);
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Category');

        $numberOfCategoriesBefore = count($mapper->all());
        $action($request, $response, []);
        $numberOfCategoriesAfter = count($mapper->all());
        
        $this->assertSame($numberOfCategoriesBefore + 1, $numberOfCategoriesAfter);
    }

    public function testChangeCategoryAction() {
        $action = new Actions\ChangeCategoryAction($this->container);

        $newName = "New name";
        $newPrice = 20.0;
        $newReducedPrice = 10.0;
        $data = [
            'name' => $newName,
            'price' => $newPrice,
            'price_reduced' => $newReducedPrice
        ];
        $request = $this->getPutRequest('/categories/1', $data);
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Category');
        
        $categoryBefore = $mapper->get(1);
        $this->assertNotSame($categoryBefore->name, $newName);
        $this->assertNotSame($categoryBefore->price, $newPrice);
        $this->assertNotSame($categoryBefore->price_reduced, $newReducedPrice);

        $response = $action($request, $response, [ 'id' => 1 ]);

        $categoryAfter = $mapper->get(1);
        $this->assertSame($categoryAfter->name, $newName);
        $this->assertSame($categoryAfter->price, $newPrice);
        $this->assertSame($categoryAfter->price_reduced, $newReducedPrice);

        $this->assertSame(
            '{"id":1,"name":"New name","color":"#000","price":20,"price_reduced":10}',
            (string)$response->getBody());
    }

    public function testDeleteCategoryAction() {
        $action = new Actions\DeleteCategoryAction($this->container);

        $request = $this->getDeleteRequest('/categories/1');
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Category');
        
        $numberOfCategoriesBefore = count($mapper->all());
        $action($request, $response, [ 'id' => 1 ]);
        $numberOfCategoriesAfter = count($mapper->all());
        
        $this->assertSame($numberOfCategoriesBefore - 1, $numberOfCategoriesAfter);
    }
}