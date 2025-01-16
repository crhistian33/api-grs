<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TypeWorkerRequest;
use App\Http\Resources\v1\TypeWorkerCollection;
use App\Http\Resources\v1\TypeWorkerResource;
use App\Models\TypeWorker;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class TypeWorkerController extends Controller
{
    use ApiResponse;

    protected $typeworkers;

    public function index()
    {
        try {
            $this->typeworkers = TypeWorker::all();
            return new TypeWorkerCollection($this->typeworkers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->typeworkers = TypeWorker::onlyTrashed()->get();
            return new TypeWorkerCollection($this->typeworkers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(TypeWorkerRequest $request)
    {
        try {
            $typeworker = TypeWorker::create($request->validated());
            return $this->createdResponse($typeworker, config('messages.success.create_title'), 'El tipo de trabajador '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(TypeWorker $type_worker)
    {
        try {
            return new TypeWorkerResource($type_worker);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(TypeWorkerRequest $request, TypeWorker $typeWorker)
    {
        try {
            $typeWorker->update($request->validated());
            return $this->successResponse($typeWorker, config('messages.success.update_title'), 'El tipo de trabajador '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $typeWorker = TypeWorker::withTrashed()->findOrFail($id);
            if(!$delete) {
                $typeWorker->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'El tipo de trabajador '.config('messages.success.remove_message'));
            } else {
                $typeWorker->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El tipo de trabajador '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $typeWorker = TypeWorker::onlyTrashed()->findOrFail($id);
            $typeWorker->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'El tipo de trabajador '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $typeWorkers = new TypeWorkerCollection($request->resources);
            $typeWorkersIds = $typeWorkers->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingTypeWorkers = TypeWorker::withTrashed()->whereIn('id', $typeWorkersIds)->count();

            if ($existingTypeWorkers !== count($typeWorkersIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingTypeWorkers, 404);
            }

            if(!$delete) {
                TypeWorker::whereIn('id', $typeWorkersIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Los tipos de trabajadores '.config('messages.success.removeall_message'));
            }
            else {
                TypeWorker::whereIn('id', $typeWorkersIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Los tipos de trabajadores '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $typeWorkers = new TypeWorkerCollection($request->resources);
            $typeWorkersIds = $typeWorkers->getIdsAttribute();
            TypeWorker::whereIn('id', $typeWorkersIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Los tipos de trabajadores '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
