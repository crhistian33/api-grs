<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerAssignmentRequest;
use App\Http\Resources\v1\WorkerAssignmentCollection;
use App\Http\Resources\v1\WorkerAssignmentResource;
use App\Models\Company;
use App\Models\WorkerAssignment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class WorkerAssignController extends Controller
{
    use ApiResponse;

    protected $workerassignments;
    protected $workerassignment;

    public function all(?Company $company = null) {
        $query = WorkerAssignment::whereHas('assignment', function($query) {
            return $query->where('state', true);
        });

        if ($company) {
            $query->whereHas('worker', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            });
        }

        $this->workerassignments = $query->get();
        return new WorkerAssignmentCollection($this->workerassignments);
    }

    // public function getWorkersAssigns(WorkerAssignsRequest $request) {
    //     $unit_shift_id = $request->input('unit_shift_id');
    //     $today = $request->input('today');

    //     $workerAssigns = WorkerAssignment::activeToUnitshift($unit_shift_id)
    //         ->get();

    //     $response = $workerAssigns->map(function ($workerAssign) use($today) {
    //         return [
    //             'id' => $workerAssign->id,
    //             'worker' => [
    //                     'name' => $workerAssign->worker->name,
    //                     'dni' => $workerAssign->worker->dni
    //             ],
    //             'state' => $this->getStateWorkerAssign(
    //                 $workerAssign,
    //                 $workerAssign->assignment->unitShift->shift,
    //                 $today
    //             )
    //         ];
    //     });

    //     return response()->json([
    //         'data' => $response
    //     ]);
    // }

    // private function getStateWorkerAssign($workerAssign, $shift, $today) {
    //     $breaks = $workerAssign->breaks->where('start_date', $today);
    //     if ($breaks->isNotEmpty()) {
    //         return [
    //             'id' => State::getIdByValue('X'),
    //             'shortName' => 'X',
    //         ];
    //     }

    //     $permission = $workerAssign->permissions
    //         ->where('start_date','<=', $today)
    //         ->where('end_date','>=', $today)
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

    public function update(WorkerAssignmentRequest $request, WorkerAssignment $workerAssignment) {
        try {
            //$workerAssignment->update($request->validated());
            $workerAssignment->delete();
            $entity = WorkerAssignment::create($request->validated());
            $this->workerassignment = new WorkerAssignmentResource($entity);
            return $this->successResponse($this->workerassignment, config('messages.success.reassign_title'), config('messages.success.reassign_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(WorkerAssignment $worker_assignment)
    {
        try {
            $worker_assignment->delete();
            return $this->successResponse(null, config('messages.success.delete_title'), 'El trabajador '.config('messages.success.delete_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $workers = new WorkerAssignmentCollection($request->resources);
            $workerAssignsIds = $workers->getIdsAttribute();

            $existingWorkers = WorkerAssignment::whereIn('id', $workerAssignsIds)->count();

            if ($existingWorkers !== count($workerAssignsIds)) {
                return $this->errorResponse('Uno de los registros no existe.', 404);
            }

            WorkerAssignment::whereIn('id', $workerAssignsIds)->delete();
            return $this->successResponse(null, config('messages.success.deleteall_title'), 'Las asignaciones '.config('messages.success.deleteall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
