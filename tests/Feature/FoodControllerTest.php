<?php

namespace Tests\Unit\Controllers\API;

use App\Http\Controllers\API\FoodController;
use App\Http\Resources\Collections\FoodCollection;
use App\Http\Resources\FoodResource;
use App\Models\Food;
use App\Models\User;
use App\Repositories\FoodRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class FoodControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $foodRepositoryMock;
    protected $foodController;

    public function setUp(): void
    {
        parent::setUp();
        $this->foodRepositoryMock = Mockery::mock(FoodRepository::class);
        $this->foodController = new FoodController($this->foodRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test creating a new food item successfully
     * Acceptance Criteria 1
     */
    public function testStore()
    {
        $foodData = [
            'name' => 'อาหารทดสอบ',
            'price' => 100,
            'status' => 'AVAILABLE',
            'category' => 'MAIN COURSE',
            'description' => 'รายละเอียดอาหารทดสอบ',
            'image_url' => 'test-image.jpg'
        ];

        $request = Mockery::mock(Request::class);

        $request->shouldReceive('input')
            ->with('name')
            ->andReturn($foodData['name']);
        $request->shouldReceive('input')
            ->with('price')
            ->andReturn($foodData['price']);
        $request->shouldReceive('input')
            ->with('status')
            ->andReturn($foodData['status']);
        $request->shouldReceive('input')
            ->with('category')
            ->andReturn($foodData['category']);
        $request->shouldReceive('input')
            ->with('description')
            ->andReturn($foodData['description']);
        $request->shouldReceive('input')
            ->with('image_url')
            ->andReturn($foodData['image_url']);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('create', Food::class)
            ->andReturn(true);

        $expectedFood = new Food();
        $expectedFood->setTable('food');
        $expectedFood->setConnection('mysql');
        $expectedFood->forceFill(array_merge($foodData, [
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]));

        $foodMock = Mockery::mock('alias:App\Models\Food');
        $foodMock->shouldReceive('create')
            ->once()
            ->with($foodData)
            ->andReturn($expectedFood);

        $response = $this->foodController->store($request);

        $this->assertInstanceOf(FoodResource::class, $response);
        $this->assertEquals($expectedFood->toArray(), $response->resource->toArray());
    }

    /**
     * Test unauthorized user cannot create food
     * Additional Acceptance Criteria
     */
    public function testUnauthorizedUserCannotCreateFood()
    {
        $foodData = [
            'name' => 'อาหารทดสอบ',
            'price' => 100,
            'status' => 'AVAILABLE',
            'category' => 'MAIN COURSE',
            'description' => 'รายละเอียดอาหารทดสอบ',
            'image_url' => 'test-image.jpg'
        ];

        $request = Mockery::mock(Request::class);

        $request->shouldReceive('input')
            ->with('name')
            ->andReturn($foodData['name']);
        $request->shouldReceive('input')
            ->with('price')
            ->andReturn($foodData['price']);
        $request->shouldReceive('input')
            ->with('status')
            ->andReturn($foodData['status']);
        $request->shouldReceive('input')
            ->with('category')
            ->andReturn($foodData['category']);
        $request->shouldReceive('input')
            ->with('description')
            ->andReturn($foodData['description']);
        $request->shouldReceive('input')
            ->with('image_url')
            ->andReturn($foodData['image_url']);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('create', Food::class)
            ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.'));

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $this->foodController->store($request);
    }

    /**
     * Test getting all foods
     * Acceptance Criteria 2.1
     */
    public function testIndex()
    {
        $foods = new \Illuminate\Database\Eloquent\Collection([
            new Food(['id' => 1, 'name' => 'อาหารที่ 1', 'price' => 100, 'status' => 'AVAILABLE', 'category' => 'MAIN COURSE']),
            new Food(['id' => 2, 'name' => 'อาหารที่ 2', 'price' => 200, 'status' => 'UNAVAILABLE', 'category' => 'DESSERT'])
        ]);

        $this->foodRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($foods);

        $response = $this->foodController->index();

        $this->assertInstanceOf(FoodCollection::class, $response);
        $this->assertCount(2, $response->resource);
        $this->assertContainsOnlyInstancesOf(Food::class, $response->resource);
    }

    /**
     * Test getting a specific food
     * Acceptance Criteria 2.2
     */
    public function testShow()
    {
        $food = new Food([
            'id' => 1,
            'name' => 'อาหารทดสอบ',
            'price' => 100,
            'status' => 'AVAILABLE',
            'category' => 'MAIN COURSE'
        ]);

        $response = $this->foodController->show($food);

        $this->assertInstanceOf(FoodResource::class, $response);
        $this->assertEquals($food->toArray(), $response->resource->toArray());
    }

    /**
     * Test updating a food item
     * Acceptance Criteria 3
     */
    public function testUpdate()
    {
        $foodId = 1;
        $updateData = [
            'name' => 'อาหารที่อัปเดต',
            'price' => 200,
            'status' => 'UNAVAILABLE',
            'category' => 'DESSERT',
            'description' => 'รายละเอียดอาหารที่อัปเดต',
            'image_url' => 'updated-image.jpg'
        ];

        $food = Mockery::mock(Food::class);
        $food->shouldReceive('getAttribute')->with('id')->andReturn($foodId);

        $request = Mockery::mock(Request::class);

        $request->shouldReceive('get')
            ->with('name')
            ->andReturn($updateData['name']);
        $request->shouldReceive('get')
            ->with('price')
            ->andReturn($updateData['price']);
        $request->shouldReceive('get')
            ->with('status')
            ->andReturn($updateData['status']);
        $request->shouldReceive('get')
            ->with('category')
            ->andReturn($updateData['category']);
        $request->shouldReceive('get')
            ->with('description')
            ->andReturn($updateData['description']);
        $request->shouldReceive('get')
            ->with('image_url')
            ->andReturn($updateData['image_url']);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', Food::class)
            ->andReturn(true);

        $this->foodRepositoryMock->shouldReceive('update')
            ->once()
            ->with($updateData, $foodId)
            ->andReturn(true);

        $food->shouldReceive('refresh')
            ->once()
            ->andReturn($food);

        $response = $this->foodController->update($request, $food);

        $this->assertInstanceOf(FoodResource::class, $response);
    }

    /**
     * Test unauthorized user cannot update food
     * Additional Acceptance Criteria
     */
    public function testUnauthorizedUserCannotUpdateFood()
    {
        $foodId = 1;
        $updateData = [
            'name' => 'อาหารที่อัปเดต',
            'price' => 200,
            'status' => 'UNAVAILABLE',
            'category' => 'DESSERT',
            'description' => 'รายละเอียดอาหารที่อัปเดต',
            'image_url' => 'updated-image.jpg'
        ];

        $food = Mockery::mock(Food::class);
        $food->shouldReceive('getAttribute')->with('id')->andReturn($foodId);

        $request = Mockery::mock(Request::class);

        $request->shouldReceive('get')
            ->with('name')
            ->andReturn($updateData['name']);
        $request->shouldReceive('get')
            ->with('price')
            ->andReturn($updateData['price']);
        $request->shouldReceive('get')
            ->with('status')
            ->andReturn($updateData['status']);
        $request->shouldReceive('get')
            ->with('category')
            ->andReturn($updateData['category']);
        $request->shouldReceive('get')
            ->with('description')
            ->andReturn($updateData['description']);
        $request->shouldReceive('get')
            ->with('image_url')
            ->andReturn($updateData['image_url']);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', Food::class)
            ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.'));

        $this->foodRepositoryMock->shouldReceive('update')
            ->never();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $this->foodController->update($request, $food);
    }
}
