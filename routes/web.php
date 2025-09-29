<?php

use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\EmployeeAuthController;
use App\Http\Controllers\BackOffice\DashboardController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\BackOffice\DepartmentController;
use App\Http\Controllers\BackOffice\PositionController;
use App\Http\Controllers\BackOffice\EmployeeController;
use App\Http\Controllers\BackOffice\MenuController;
use App\Http\Controllers\BackOffice\RouteController;
use App\Http\Controllers\BackOffice\PlaceController;
use App\Http\Controllers\BackOffice\RoutePlaceController;
use App\Http\Controllers\BackOffice\VehicleTypeController;
use App\Http\Controllers\BackOffice\VehicleController;
use App\Http\Controllers\BackOffice\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReserveController::class, 'searchListPage']);

// Normalize trailing slashes on common app prefixes to avoid 404 on '/path/'
Route::redirect('backoffice/', 'backoffice');
Route::redirect('auth/employees/login/', 'auth/employees/login');
Route::redirect('auth/drivers/login/', 'auth/drivers/login');
Route::redirect('auth/users/login/', 'auth/users/login');


Route::middleware('guest')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('login', [UserAuthController::class, 'loginPage'])->name('login');
            Route::post('login', [UserAuthController::class, 'login']);
            Route::get('register', [UserAuthController::class, 'registerPage']);
            Route::post('register', [UserAuthController::class, 'register']);
        });
    });


    Route::prefix('auth')->group(function () {
        Route::prefix('employees')->group(function () {
            Route::get('login', [EmployeeAuthController::class, 'loginPage']);
            Route::post('login', [EmployeeAuthController::class, 'login']);
        });
        // Alias for drivers login -> re-use employee auth endpoints
        Route::prefix('drivers')->group(function () {
            Route::get('login', [EmployeeAuthController::class, 'loginPage']);
            Route::post('login', [EmployeeAuthController::class, 'login']);
        });
    });
});


Route::middleware('useremp.auth')->group(function () {
    Route::get('/auth/users/logout', [UserAuthController::class, 'logout']);
});


Route::middleware('employee.auth')->group(function () {

    Route::get('auth/employees/logout', [EmployeeAuthController::class, 'logout']);

    Route::prefix('backoffice')->group(function () {


        Route::get('/', [DashboardController::class, 'dashboardPage'])->middleware('position.menu:dashboard');
       

        Route::prefix('users')->middleware('position.menu:user_manage')->group(function () {
            Route::get('/', [UserController::class, 'usersPage']);
            Route::get('/data', [UserController::class, 'data']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });

        Route::prefix('departments')->middleware('position.menu:department_position_manage')->group(function () {
            Route::get('/list', [DepartmentController::class, 'list']);
            Route::get('/data', [DepartmentController::class, 'data']);
            Route::get('/{id}', [DepartmentController::class, 'show']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::put('/{id}', [DepartmentController::class, 'update']);
            Route::delete('/{id}', [DepartmentController::class, 'destroy']);
        });

         Route::view('/org', 'backoffice.org')->middleware('position.menu:department_position_manage');

         
        Route::prefix('positions')->middleware('position.menu:department_position_manage')->group(function () {
            Route::get('/list', [PositionController::class, 'list']);
            Route::get('/data', [PositionController::class, 'data']);
            Route::get('/{id}', [PositionController::class, 'show']);
            Route::post('/', [PositionController::class, 'store']);
            Route::put('/{id}', [PositionController::class, 'update']);
            Route::delete('/{id}', [PositionController::class, 'destroy']);
        });

        Route::prefix('employees')->middleware('position.menu:employee_manage')->group(function () {
            Route::view('/', 'backoffice.employees');
            Route::get('/data', [EmployeeController::class, 'data']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
            Route::post('/', [EmployeeController::class, 'store']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);
        });

        // Menus
        Route::prefix('menus')->middleware('position.menu:menu_manage')->group(function () {
            Route::view('/', 'backoffice.menus');
            Route::get('/data', [MenuController::class, 'data']);
            Route::get('/all', [MenuController::class, 'listAll']);
            Route::get('/{id}', [MenuController::class, 'show']);
            Route::post('/', [MenuController::class, 'store']);
            Route::put('/{id}', [MenuController::class, 'update']);
            Route::delete('/{id}', [MenuController::class, 'destroy']);
        });

        // Position access to menus
        Route::get('positions/{positionId}/menus', [MenuController::class, 'positionAccess'])->middleware('position.menu:menu_manage');
        Route::post('positions/{positionId}/menus', [MenuController::class, 'savePositionAccess'])->middleware('position.menu:menu_manage');

        // Routes & Places combined page
        Route::view('/routes-places', 'backoffice.routes_places')->middleware('position.menu:routes_places_manage');

        // Vehicles & Types combined page

        // Routes CRUD
        Route::prefix('routes')->middleware('position.menu:routes_places_manage')->group(function () {
            Route::get('/data', [RouteController::class, 'data']);
            Route::get('/{id}', [RouteController::class, 'show']);
            Route::post('/', [RouteController::class, 'store']);
            Route::put('/{id}', [RouteController::class, 'update']);
            Route::delete('/{id}', [RouteController::class, 'destroy']);

   
            Route::get('/{route}/route-places-page', function ($route) {
                return view('backoffice.route_places');
            });

            // Route Places under specific route
            Route::get('/{route}/route-places', [RoutePlaceController::class, 'data']);
            Route::post('/{route}/route-places', [RoutePlaceController::class, 'store']);
            Route::put('/{route}/route-places/{route_place}', [RoutePlaceController::class, 'update']);
            Route::delete('/{route}/route-places/{route_place}', [RoutePlaceController::class, 'destroy']);
            Route::post('/{route}/route-places/reorder', [RoutePlaceController::class, 'reorder']);
        });

        // Places CRUD
        Route::prefix('places')->middleware('position.menu:routes_places_manage')->group(function () {
            Route::get('/list', [PlaceController::class, 'list']);
            Route::get('/data', [PlaceController::class, 'data']);
            Route::get('/{id}', [PlaceController::class, 'show']);
            Route::post('/', [PlaceController::class, 'store']);
            Route::put('/{id}', [PlaceController::class, 'update']);
            Route::delete('/{id}', [PlaceController::class, 'destroy']);
        });

        // Vehicle Types CRUD
        Route::prefix('vehicle-types')->middleware('position.menu:vehicle_vehicle_type_manage')->group(function () {

            Route::get('/list', [VehicleTypeController::class, 'list']);
            Route::get('/data', [VehicleTypeController::class, 'data']);
            Route::get('/{id}', [VehicleTypeController::class, 'show']);
            Route::post('/', [VehicleTypeController::class, 'store']);
            Route::put('/{id}', [VehicleTypeController::class, 'update']);
            Route::delete('/{id}', [VehicleTypeController::class, 'destroy']);
        });

        // Vehicles CRUD
        Route::prefix('vehicles')->middleware('position.menu:vehicle_vehicle_type_manage')->group(function () {
            Route::view('/', 'backoffice.vehicles')->middleware('position.menu:vehicle_vehicle_type_manage');
            Route::get('/data', [VehicleController::class, 'data']);
            Route::get('/{id}', [VehicleController::class, 'show']);
            Route::post('/', [VehicleController::class, 'store']);
            Route::put('/{id}', [VehicleController::class, 'update']);
            Route::delete('/{id}', [VehicleController::class, 'destroy']);
        });

        // Trips CRUD
        Route::prefix('trips')->middleware('position.menu:trips_manage')->group(function () {
            Route::view('/', 'backoffice.trips');
            Route::get('/data', [TripController::class, 'data']);
            Route::get('/{id}', [TripController::class, 'show']);
            Route::post('/', [TripController::class, 'store']);
            Route::put('/{id}', [TripController::class, 'update']);
            Route::delete('/{id}', [TripController::class, 'destroy']);
            // Init lists for dropdowns
            Route::get('/init', [TripController::class, 'init']);
        });
    });
});
