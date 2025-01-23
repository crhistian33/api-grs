<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignmentRequest;
use App\Http\Resources\v1\AssignmentCollection;
use App\Http\Resources\v1\AssignmentResource;
use App\Models\Assignment;
use App\Models\UnitShift;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Exception;

class AssignmentController extends Controller
{
    use ApiResponse;

    protected $assignments;

    public function index()
    {
        try {
            $this->assignments = Assignment::orderBy('created_at', 'desc')->get();
            return new AssignmentCollection($this->assignments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->assignments = Assignment::onlyTrashed()->orderBy('created_at', 'desc')->get();
            return new AssignmentCollection($this->assignments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getReassignment() {
        try {
            $this->assignments = Assignment::orderBy('created_at', 'desc')->with('workers')->get();
            return response()->json($this->assignments); //new AssignmentCollection($this->assignments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getUnitShifts() {
        $unitshifts = Assignment::where('state', true)
            ->with('unitShift.unit', 'unitShift.shift')
            ->whereHas('unitShift')
            ->get()
            ->map(function($assignment) {
                return [
                    'assign_id' => $assignment->id,
                    'unit_shift_id' => $assignment->unit_shift_id,
                    'name' => "{$assignment->unitShift->unit->name} - {$assignment->unitShift->shift->name}",
                    // ->map(function ($unitshift) {
                    //     return [
                    //         'id' => $unitshift->id
                    //     ];
                    // }),
                ];
            });

        return response()->json($unitshifts);
    }

    public function store(AssignmentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (!$request->has('start_date')) {
                $validatedData['start_date'] = now()->toDateString();
            }
            $assignment = Assignment::create($validatedData);
            $workerIds = collect($request->workers)->pluck('id')->toArray();
            $assignment->workers()->attach($workerIds);
            return $this->createdResponse($assignment, config('messages.success.create_title'), 'La asignación '.config('messages.success.create_message'));
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
            }
            return $this->successResponse($assignment, config('messages.success.update_title'), 'La asignación '.config('messages.success.update_message'));
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
