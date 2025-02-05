<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\WorkerController as WorkerV1;
use App\Http\Controllers\Api\v1\CompanyController as CompanyV1;
use App\Http\Controllers\Api\v1\TypeWorkerController as TypeWorkerV1;
use App\Http\Controllers\Api\v1\CenterController as CenterV1;
use App\Http\Controllers\Api\v1\CustomerController as CustomerV1;
use App\Http\Controllers\Api\v1\UnitController as UnitV1;
use App\Http\Controllers\Api\v1\ShiftController as ShiftV1;
use App\Http\Controllers\Api\v1\AssignmentController as AssignmentV1;
use App\Http\Controllers\Api\v1\WorkerAssignController as WorkerAssignV1;
use App\Http\Controllers\Api\v1\CountController as CountV1;
use App\Http\Controllers\Api\v1\UnitShiftController as UnitShiftV1;
use App\Http\Controllers\Api\v1\AssistController as AssistV1;
use App\Http\Controllers\Api\v1\AuthController as AuthV1;
use App\Http\Controllers\Api\v1\UserController as UserV1;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('v1/auth/login', [AuthV1::class, 'login']);
Route::post('v1/auth/refresh', [AuthV1::class, 'refreshToken']);
Route::get('v1/auth/logout', [AuthV1::class, 'logout']);

Route::get('/v1/assignments/verified/{unit_shift}', [AssignmentV1::class, 'verifiedUnit']);
Route::get('/v1/assignments/deletes', [AssignmentV1::class, 'getDeleted']);
Route::get('/v1/assignments/reassignments', [AssignmentV1::class, 'getReassignment']);
Route::post('/v1/assignments/destroyes', [AssignmentV1::class, 'destroyAll']);
Route::get('/v1/assignments/unitshifts', [AssignmentV1::class, 'getUnitShifts']);

Route::get('/v1/workers/deletes', [WorkerV1::class, 'getDeleted']);
Route::get('/v1/workers/restore/{worker}', [WorkerV1::class, 'restore']);
Route::post('/v1/workers/destroyes', [WorkerV1::class, 'destroyAll']);
Route::post('/v1/workers/restores', [WorkerV1::class, 'restoreAll']);
Route::get('/v1/workers/unassigneds', [WorkerV1::class, 'getUnassigned']);
Route::get('/v1/workers/titulars', [WorkerV1::class, 'getAllMain']);
Route::get('/v1/workers/assigns/{assignment}', [WorkerV1::class, 'getWorkersEdit']);
// Route::get('/v1/workers/reassigns', [WorkerV1::class, 'getWorkersReassigns']);

Route::get('/v1/type_workers/deletes', [TypeWorkerV1::class, 'getDeleted']);
Route::get('/v1/type_workers/restore/{type_worker}', [TypeWorkerV1::class, 'restore']);
Route::post('/v1/type_workers/destroyes', [TypeWorkerV1::class, 'destroyAll']);
Route::post('/v1/type_workers/restores', [TypeWorkerV1::class, 'restoreAll']);

Route::get('/v1/centers/deletes', [CenterV1::class, 'getDeleted']);
Route::get('/v1/centers/restore/{center}', [CenterV1::class, 'restore']);
Route::post('/v1/centers/destroyes', [CenterV1::class, 'destroyAll']);
Route::post('/v1/centers/restores', [CenterV1::class, 'restoreAll']);

Route::get('/v1/companies/deletes', [CompanyV1::class, 'getDeleted']);
Route::get('/v1/companies/restore/{company}', [CompanyV1::class, 'restore']);
Route::post('/v1/companies/destroyes', [CompanyV1::class, 'destroyAll']);
Route::post('/v1/companies/restores', [CompanyV1::class, 'restoreAll']);

Route::get('/v1/customers/deletes', [CustomerV1::class, 'getDeleted']);
Route::get('/v1/customers/restore/{customer}', [CustomerV1::class, 'restore']);
Route::post('/v1/customers/destroyes', [CustomerV1::class, 'destroyAll']);
Route::post('/v1/customers/restores', [CustomerV1::class, 'restoreAll']);

Route::get('/v1/shifts/deletes', [ShiftV1::class, 'getDeleted']);
Route::get('/v1/shifts/restore/{shift}', [ShiftV1::class, 'restore']);
Route::post('/v1/shifts/destroyes', [ShiftV1::class, 'destroyAll']);
Route::post('/v1/shifts/restores', [ShiftV1::class, 'restoreAll']);

Route::get('/v1/units/deletes', [UnitV1::class, 'getDeleted']);
Route::get('/v1/units/restore/{unit}', [UnitV1::class, 'restore']);
Route::post('/v1/units/destroyes', [UnitV1::class, 'destroyAll']);
Route::post('/v1/units/restores', [UnitV1::class, 'restoreAll']);
// Route::get('/v1/units/unitshifts', [UnitV1::class, 'getUnitShifts']);

Route::get('/v1/unitshifts/getwithassigns', [UnitShiftV1::class, 'getWithAssigns']);
Route::get('/v1/unitshifts/verified/{unitshift}/{assignment?}', [UnitShiftV1::class, 'verifiedAssignment']);

Route::post('/v1/worker_assignments/destroyes', [WorkerAssignV1::class, 'destroyAll']);

Route::get('/v1/counts', [CountV1::class, 'getcounts']);

Route::get('/v1/users/profile', [UserV1::class, 'getProfile']);

Route::apiResources([
    '/v1/workers' => WorkerV1::class,
    '/v1/worker_assignments' => WorkerAssignV1::class,
    'v1/companies' => CompanyV1::class,
    'v1/type_workers' => TypeWorkerV1::class,
    'v1/centers' => CenterV1::class,
    'v1/customers' => CustomerV1::class,
    'v1/units' => UnitV1::class,
    'v1/shifts' => ShiftV1::class,
    'v1/assignments' => AssignmentV1::class,
    'v1/unitshifts' => UnitShiftV1::class,
    'v1/assists' => AssistV1::class,
]);
