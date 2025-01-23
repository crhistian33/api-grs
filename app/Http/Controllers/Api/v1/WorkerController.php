<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerRequest;
use App\Http\Resources\v1\AssignmentResource;
use App\Http\Resources\v1\ShiftResource;
use App\Http\Resources\v1\UnitResource;
use App\Http\Resources\v1\UnitShiftResource;
use App\Http\Resources\v1\WorkerCollection;
use App\Http\Resources\v1\WorkerResource;
use App\Models\Assignment;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    use ApiResponse;

    protected $workers;

    public function index()
    {
        try {
            $this->workers = Worker::all();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->workers = Worker::onlyTrashed()->get();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // public function getWorkersReassigns() {
    //     try {
    //         $workers = Worker::whereHas('assignments', function ($query) {
    //             $query->where('state', true);
    //         })
    //         ->get()
    //         ->map(function ($worker) {
    //             return [
    //                 'id' => $worker -> id,
    //                 'name' => $worker->name,
    //                 'dni' => $worker->dni,
    //                 'birth_date' => $worker->birth_date,
    //                 'assignments' => AssignmentResource::collection($worker->assignments)
    //             ];
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'data' => $workers
    //         ]);
    //     } catch (Exception $e) {
    //         return $this->handleException($e);
    //     }
    // }

    public function store(WorkerRequest $request)
    {
        try {
            $worker = Worker::create($request->validated());
            return $this->createdResponse($worker, config('messages.success.create_title'), 'El trabajador '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Worker $worker)
    {
        try {
            return new WorkerResource($worker);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(WorkerRequest $request, Worker $worker)
    {
        try {
            $worker->update($request->validated());
            return $this->successResponse($worker, config('messages.success.update_title'), 'El trabajador '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $worker = Worker::withTrashed()->findOrFail($id);
            if($worker->assignments()->exists()) {
                return $this->errorResponse('No se puede eliminar el trabajador porque está asignado a una unidad turno', 422);
            }
            if(!$delete) {
                $worker->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'El trabajador '.config('messages.success.remove_message'));
            } else {
                $worker->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El trabajador '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $worker = Worker::onlyTrashed()->findOrFail($id);
            $worker->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'El trabajador '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $workers = new WorkerCollection($request->resources);
            $workersIds = $workers->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingWorkers = Worker::withTrashed()->whereIn('id', $workersIds)->count();

            if ($existingWorkers !== count($workersIds)) {
                return $this->errorResponse('Uno de los registros no existe.', 404);
            }

            if(!$delete) {
                Worker::whereIn('id', $workersIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Los trabajadores '.config('messages.success.removeall_message'));
            }
            else {
                Worker::whereIn('id', $workersIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Los trabajadores '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $workers = new WorkerCollection($request->resources);
            $workersIds = $workers->getIdsAttribute();
            Worker::whereIn('id', $workersIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Los trabajadores '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getAllMain() {
        try {
            $this->workers = Worker::type('Titular')->get();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getUnassigned() {
        try {
            $this->workers = Worker::type('Titular')->unassigned()->get();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getWorkersEdit(Assignment $assignment) {
        try {
            $workers = Worker::type('Titular')->unassigned()
                ->orWhereHas('assignments', function ($q) use ($assignment) {
                    $q->where('assignments.id', $assignment->id);
                })
                ->get();
            return new WorkerCollection($workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
