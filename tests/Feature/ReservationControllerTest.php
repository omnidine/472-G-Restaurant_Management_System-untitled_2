<?php

namespace Tests\Unit\Controllers\API;

use App\Http\Controllers\API\ReservationController;
use App\Http\Resources\Collections\ReservationCollection;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $reservationRepositoryMock;
    protected $userRepositoryMock;
    protected $reservationController;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepositoryMock = Mockery::mock(ReservationRepository::class);
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->reservationController = new ReservationController(
            $this->reservationRepositoryMock,
            $this->userRepositoryMock
        );

        // Setup Gate facade mock
        Gate::shouldReceive('authorize')->andReturn(true)->byDefault();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Reservation Creation Tests (Acceptance Criteria 1)
    // -------------------------------------------------------------------------

    /**
     * Test creating a new reservation successfully
     * Acceptance Criteria 1
     */
    public function testStore()
    {
        // Arrange
        $userId = 1;
        $tableId = 1;
        $appointmentTime = '2025-04-01 19:00:00';

        $requestData = [
            'user_id' => $userId,
            'table_id' => $tableId,
            'appointment_time' => $appointmentTime,
        ];

        $request = new Request($requestData);

        $expectedReservation = new Reservation([
            'user_id' => $userId,
            'table_id' => $tableId,
            'appointment_time' => $appointmentTime,
            'status' => 'PENDING',
        ]);

        // Mock authorization
        Gate::shouldReceive('authorize')
            ->once()
            ->with('create', Reservation::class)
            ->andReturn(true);

        // Mock repository create method
        $this->reservationRepositoryMock->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $userId,
                'table_id' => $tableId,
                'appointment_time' => $appointmentTime,
                'status' => 'PENDING',
            ])
            ->andReturn($expectedReservation);

        // Act
        $response = $this->reservationController->store($request);

        // Assert
        $this->assertInstanceOf(ReservationResource::class, $response);
        $this->assertEquals($expectedReservation, $response->resource);
        $this->assertEquals('PENDING', $expectedReservation->status);
    }

    /**
     * Test unauthorized user cannot create reservation
     * Additional Acceptance Criteria
     */
    public function testUnauthorizedUserCannotCreateReservation()
    {
        // Arrange
        $userId = 1;
        $tableId = 1;
        $appointmentTime = '2025-04-01 19:00:00';

        $requestData = [
            'user_id' => $userId,
            'table_id' => $tableId,
            'appointment_time' => $appointmentTime,
        ];

        $request = new Request($requestData);

        // Mock authorization to throw exception
        $exception = new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
        Gate::shouldReceive('authorize')
            ->once()
            ->with('create', Reservation::class)
            ->andThrow($exception);

        // Expect exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        // Act
        $this->reservationController->store($request);
    }

    // -------------------------------------------------------------------------
    // Reservation Viewing Tests (Acceptance Criteria 2)
    // -------------------------------------------------------------------------

    /**
     * Test getting all reservations
     * Acceptance Criteria 2.1
     */
    public function testIndex()
    {
        // Arrange
        $reservations = new \Illuminate\Database\Eloquent\Collection([
            new Reservation(['id' => 1, 'user_id' => 1, 'table_id' => 1, 'appointment_time' => '2025-04-01 19:00:00', 'status' => 'PENDING']),
            new Reservation(['id' => 2, 'user_id' => 2, 'table_id' => 2, 'appointment_time' => '2025-04-02 20:00:00', 'status' => 'CONFIRMED'])
        ]);

        // Mock authorization
        Gate::shouldReceive('authorize')
            ->once()
            ->with('viewAny', Reservation::class)
            ->andReturn(true);

        // Mock repository getAll method
        $this->reservationRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($reservations);

        // Act
        $response = $this->reservationController->index();

        // Assert
        $this->assertInstanceOf(ReservationCollection::class, $response);
        // Don't directly compare objects, just verify we have the correct collection
        $this->assertCount(2, $response->resource);
        $this->assertContainsOnlyInstancesOf(Reservation::class, $response->resource);
    }

    /**
     * Test getting a specific reservation
     * Acceptance Criteria 2.2
     */
    public function testShow()
    {
        // Arrange
        $reservation = new Reservation([
            'id' => 1,
            'user_id' => 1,
            'table_id' => 1,
            'appointment_time' => '2025-04-01 19:00:00',
            'status' => 'PENDING'
        ]);

        // Mock authorization
        Gate::shouldReceive('authorize')
            ->once()
            ->with('view', $reservation)
            ->andReturn(true);

        // Act
        $response = $this->reservationController->show($reservation);

        // Assert
        $this->assertInstanceOf(ReservationResource::class, $response);
        $this->assertEquals($reservation, $response->resource);
    }

    /**
     * Test getting reservations for a specific user when user exists
     * Acceptance Criteria 2.3
     */
    public function testGetReservationsByUserWhenUserExists()
    {
        // Arrange
        $userId = 1;

        $reservations = new \Illuminate\Database\Eloquent\Collection([
            new Reservation(['id' => 1, 'user_id' => $userId, 'table_id' => 1, 'appointment_time' => '2025-04-01 19:00:00', 'status' => 'PENDING']),
            new Reservation(['id' => 2, 'user_id' => $userId, 'table_id' => 2, 'appointment_time' => '2025-04-02 20:00:00', 'status' => 'CONFIRMED'])
        ]);

        // Mock user repository
        $this->userRepositoryMock->shouldReceive('isExists')
            ->once()
            ->with($userId)
            ->andReturn(true);

        // Mock reservation repository
        $this->reservationRepositoryMock->shouldReceive('findByUserId')
            ->once()
            ->with($userId)
            ->andReturn($reservations);

        // Mock authorization for each reservation
        Gate::shouldReceive('authorize')
            ->times($reservations->count())
            ->with('view', Mockery::type(Reservation::class))
            ->andReturn(true);

        // Act
        $response = $this->reservationController->getReservationsByUser($userId);

        // Assert
        $this->assertInstanceOf(ReservationCollection::class, $response);
        // Don't directly compare objects, just verify we have the correct collection
        $this->assertCount(2, $response->resource);
        $this->assertContainsOnlyInstancesOf(Reservation::class, $response->resource);
    }

    /**
     * Test getting reservations for a user that doesn't exist
     * Acceptance Criteria 2.3
     */
    public function testGetReservationsByUserWhenUserDoesNotExist()
    {
        // Arrange
        $userId = 999; // Non-existent user ID

        // Mock user repository
        $this->userRepositoryMock->shouldReceive('isExists')
            ->once()
            ->with($userId)
            ->andReturn(false);

        // Act
        $response = $this->reservationController->getReservationsByUser($userId);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['message' => 'User not found'], json_decode($response->getContent(), true));
    }

    // -------------------------------------------------------------------------
    // Reservation Update Tests (Acceptance Criteria 3)
    // -------------------------------------------------------------------------

    /**
     * Test updating a reservation status
     * Acceptance Criteria 3
     */
    public function testUpdate()
    {
        // Arrange
        $reservationId = 1;
        $newStatus = 'CONFIRMED';

        $reservation = Mockery::mock(Reservation::class);
        $reservation->shouldReceive('getAttribute')->with('id')->andReturn($reservationId);

        $updatedReservation = new Reservation([
            'id' => $reservationId,
            'user_id' => 1,
            'table_id' => 1,
            'appointment_time' => '2025-04-01 19:00:00',
            'status' => $newStatus
        ]);

        $request = new Request(['status' => $newStatus]);

        // Mock authorization
        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $reservation)
            ->andReturn(true);

        // Mock repository update method
        $this->reservationRepositoryMock->shouldReceive('update')
            ->once()
            ->with(['status' => $newStatus], $reservationId)
            ->andReturn(true);

        // Mock reservation refresh
        $reservation->shouldReceive('refresh')
            ->once()
            ->andReturn($updatedReservation);

        // Act
        $response = $this->reservationController->update($request, $reservation);

        // Assert
        $this->assertInstanceOf(ReservationResource::class, $response);
        $this->assertEquals($updatedReservation, $response->resource);
    }

    /**
     * Test unauthorized user cannot update reservation
     * Additional Acceptance Criteria
     */
    public function testUnauthorizedUserCannotUpdateReservation()
    {
        // Arrange
        $reservationId = 1;
        $newStatus = 'CONFIRMED';

        $reservation = Mockery::mock(Reservation::class);
        $reservation->shouldReceive('getAttribute')->with('id')->andReturn($reservationId);

        $request = new Request(['status' => $newStatus]);

        // Mock authorization to throw exception
        $exception = new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $reservation)
            ->andThrow($exception);

        // Expect exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        // Act
        $this->reservationController->update($request, $reservation);
    }

    /**
     * Test non-owner cannot update reservation
     * Additional Acceptance Criteria
     */
    public function testNonOwnerCannotUpdateReservation()
    {
        // Arrange
        $reservationId = 1;
        $newStatus = 'CONFIRMED';
        $nonOwnerUserId = 999; // Different from the reservation owner

        // Create a reservation owned by user 1
        $reservation = Mockery::mock(Reservation::class);
        $reservation->shouldReceive('getAttribute')->with('id')->andReturn($reservationId);
        $reservation->shouldReceive('getAttribute')->with('user_id')->andReturn(1);

        $request = new Request(['status' => $newStatus]);

        // Mock authorization to throw exception for non-owner
        $exception = new \Illuminate\Auth\Access\AuthorizationException('You do not own this reservation.');
        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $reservation)
            ->andThrow($exception);

        // Expect exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('You do not own this reservation.');

        // Act
        $this->reservationController->update($request, $reservation);
    }
}
