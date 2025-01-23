<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerAssignmentRequest;
use App\Http\Resources\v1\WorkerAssignmentCollection;
use App\Models\WorkerAssignment;
use Illuminate\Support\Facades\DB;
use Exception;

class WorkerAssignController extends Controller
{
    protected $workerassigns;

    public function index() {
        $this->workerassigns = WorkerAssignment::whereHas('assignment', function($query) {
            return $query->where('state', true);
        })->get();
        return new WorkerAssignmentCollection($this->workerassigns);
    }

    public function update(WorkerAssignmentRequest $request) {
        try {
            $workerAssignment = WorkerAssignment::where('worker_id', $request->worker_id)
            ->where('assignment_id', $request->current_assignment_id)
            ->firstOrFail();

            DB::transaction(function () use ($workerAssignment, $request) {
                $workerAssignment->update([
                    'assignment_id' => $request->new_assignment_id
                ]);
            });
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
