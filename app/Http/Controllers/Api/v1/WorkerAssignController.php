<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerAssignmentRequest;
use App\Http\Resources\v1\WorkerAssignmentCollection;
use App\Models\WorkerAssignment;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class WorkerAssignController extends Controller
{
    use ApiResponse;

    protected $workerassignments;

    public function index() {
        $this->workerassignments = WorkerAssignment::whereHas('assignment', function($query) {
                return $query->where('state', true);
            })
            ->get();
        return new WorkerAssignmentCollection($this->workerassignments);
    }

    public function update(WorkerAssignmentRequest $request, WorkerAssignment $workerAssignment) {
        try {
            $workerAssignment->update($request->validated());
            return $this->successResponse($workerAssignment, config('messages.success.reassign_title'), config('messages.success.reassign_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(WorkerAssignment $worker_assignment)
    {
        try {
            $worker_assignment->forceDelete();
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

            WorkerAssignment::whereIn('id', $workerAssignsIds)->forceDelete();
            return $this->successResponse(null, config('messages.success.deleteall_title'), 'Las asignaciones '.config('messages.success.deleteall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
