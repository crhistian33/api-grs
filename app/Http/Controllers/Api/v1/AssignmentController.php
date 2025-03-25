<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignmentRequest;
use App\Http\Resources\v1\AssignmentCollection;
use App\Http\Resources\v1\AssignmentResource;
use App\Models\Assignment;
use App\Models\Company;
use App\Models\State;
use App\Models\UnitShift;
use App\Models\WorkerAssignment;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Exception;

class AssignmentController extends Controller
{
    use ApiResponse;

    protected $assignments;
    protected $assignment;
    protected array $relations = ['unitshift', 'workers', 'unitshift', 'workers'];
    protected array $fields = ['assignments.id', 'assignments.start_date', 'assignments.state', 'unitshift', 'workers'];

    public function all(?Company $company = null)
    {
        try {
            $query = Assignment::query()
                ->where('state', 1)
                ->with([
                    'unitShift.unit.customer.company',
                    'workers' => function($query) {
                        $query->wherePivotNull('deleted_at');
                    }
                ]);

            if ($company) {
                $query->whereHas('unitShift.unit.customer', function ($query) use ($company) {
                    $query->whereHas('company', function ($query) use ($company) {
                        $query->where('id', $company->id);
                    });
                });
            }

            $this->assignments = $query->orderBy('created_at', 'desc')->get();
            return new AssignmentCollection($this->assignments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrasheds(?Company $company = null) {
        try {
            $query = Assignment::query()
                ->with([
                    'unitShift.unit.customer.company',
                    'workers'
                ]);

            if ($company) {
                $query->whereHas('unitShift.unit.customer', function ($query) use ($company) {
                    $query->whereHas('company', function ($query) use ($company) {
                        $query->where('id', $company->id);
                    });
                });
            }

            $this->assignments = $query->onlyTrashed()->orderBy('created_at', 'desc')->get();
            return new AssignmentCollection($this->assignments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // public function getWorkerAssigns(Request $request) {
    //     $unit_shift_id = $request->input('unit_shift_id');
    //     $start_date = $request->input('start_date');

    //     $assignment = Assignment::activeToUnitshift($unit_shift_id)
    //         ->with(['workers', 'unitShift.shift'])
    //         ->get();

    //     $response = $assignment->map(function ($assign) use($start_date) {
    //         return [
    //             'assignment_id' => $assign->id,
    //             'workers' => $assign->workers->map(function ($worker) use ($assign, $start_date) {
    //                 $workerAssignment = $worker->workerAssignments
    //                     ->where('assignment_id', $assign->id)
    //                     ->first();
    //                 return [
    //                     'id' => $worker->id,
    //                     'worker_assignment' => $workerAssignment->id,
    //                     'name' => $worker->name,
    //                     'dni' => $worker->dni,
    //                     'state' => $this->getStateWorkerAssign(
    //                         $workerAssignment,
    //                         $assign->unitShift->shift,
    //                         $start_date),
    //                 ];
    //             }),
    //         ];
    //     });
    //     return response()->json($response);
    // }

    // private function getStateWorkerAssign($workerAssign, $shift, $start_date) {
    //     $breaks = $workerAssign->breaks->where('start_date', $start_date);
    //     if ($breaks->isNotEmpty()) {
    //         return [
    //             'id' => State::getIdByValue('X'),
    //             'shortName' => 'X',
    //         ];
    //     }

    //     $permission = $workerAssign->permissions
    //         ->where('start_date','<=', $start_date)
    //         ->where('end_date','>=', $start_date)
    //         ->first();

    //     if (isset($permission)) {
    //         $state = State::findOrFail($permission->state_id);
    //         return [
    //             'id' => $state->id,
    //             'shortName' => $state->shortName,
    //         ];
    //     }

    //     return [
    //         'id' => State::getIdByValue($shift->shortName),
    //         'shortName' => $shift->shortName,
    //     ];
    // }

    // public function getReassignment() {
    //     try {
    //         $this->assignments = Assignment::orderBy('created_at', 'desc')->with('workers')->get();
    //         return response()->json($this->assignments); //new AssignmentCollection($this->assignments);
    //     } catch (Exception $e) {
    //         return $this->handleException($e);
    //     }
    // }

    // public function getUnitShifts() {
    //     $unitshifts = Assignment::where('state', true)
    //         ->with('unitShift.unit', 'unitShift.shift')
    //         ->whereHas('unitShift')
    //         ->get()
    //         ->map(function($assignment) {
    //             return [
    //                 'assign_id' => $assignment->id,
    //                 'unit_shift_id' => $assignment->unit_shift_id,
    //                 'name' => "{$assignment->unitShift->unit->name} - {$assignment->unitShift->shift->name}",
    //                 // ->map(function ($unitshift) {
    //                 //     return [
    //                 //         'id' => $unitshift->id
    //                 //     ];
    //                 // }),
    //             ];
    //         });

    //     return response()->json($unitshifts);
    // }

    public function store(AssignmentRequest $request)
    {
        try {
            $validatedData = $request->getAllFields();
            if (!$request->has('start_date')) {
                $validatedData['start_date'] = now()->toDateString();
            }
            $assignment = Assignment::create($validatedData);

            if($request->has('workers')) {
                $workerIds = collect($request->workers)->pluck('id')->toArray();
                $assignment->workers()->attach($workerIds, ['created_at' => now()]);
            }
            $this->assignment = new AssignmentResource($assignment);
            return $this->createdResponse($this->assignment, config('messages.success.create_title'), 'La asignación '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Assignment $assignment)
    {
        try {
            return new AssignmentResource($assignment);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(AssignmentRequest $request, Assignment $assignment)
    {
        try {
            $assignment->update($request->getAllFields());
            if($request->has('workers')) {
                $workerIds = collect($request->workers)->pluck('id')->toArray();
                $assignment->workers()->sync($workerIds);
                //$assignment->workers()->wherePivotIn('worker_id', $workerIds)->detach();
            }
            $this->assignment = new AssignmentResource($assignment);
            return $this->successResponse($this->assignment, config('messages.success.update_title'), 'La asignación '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function desactivate(Assignment $assignment)
    {
        try {
            $assignment->update(['state' => false]);

            if ($assignment->workers()->exists()) {
                $workerIds = $assignment->workers()->pluck('workers.id')->toArray();

                // Usamos el modelo pivote directamente para aplicar soft delete
                WorkerAssignment::where('assignment_id', $assignment->id)
                    ->whereIn('worker_id', $workerIds)
                    ->delete(); // Esto aplicará soft delete si el modelo usa SoftDeletes
            }

            return $this->successResponse(null, 'Desactivación exitosa', 'La asignación ha sido desactivada y los trabajadores relacionados han sido eliminados.');
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(string $id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $assignment = Assignment::withTrashed()->findOrFail($id);

            if(!$delete) {
                $assignment->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'La asignación '.config('messages.success.remove_message'));
            } else {
                $assignment->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El trabajador '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $this->assignments = new AssignmentCollection($request->resources);
            $assignIds = $this->assignments->getIdsAttribute();

            $existings = Assignment::withTrashed()->whereIn('id', $assignIds)->count();

            if ($existings !== count($assignIds)) {
                return $this->errorResponse('Uno de los registros no existe.', 404);
            }

            Assignment::whereIn('id', $assignIds)->forceDelete();
            return $this->successResponse(null, config('messages.success.deleteall_title'), 'Las asignaciones '.config('messages.success.deleteall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
