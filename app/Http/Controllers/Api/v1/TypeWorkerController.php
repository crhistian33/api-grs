<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TypeWorkerRequest;
use App\Http\Resources\v1\TypeWorkerCollection;
use App\Http\Resources\v1\TypeWorkerResource;
use App\Models\TypeWorker;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class TypeWorkerController extends Controller
{
    use ApiResponse;

    protected $typeworkers;
    protected $typeworker;
    protected array $relations = ['createdBy', 'updatedBy'];
    protected array $fields = ['id', 'name', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $this->typeworkers = TypeWorker::with($this->relations)
                ->select($this->fields)
                ->get();
            return new TypeWorkerCollection($this->typeworkers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed() {
        try {
            $this->typeworkers = TypeWorker::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed()
                ->get();
            return new TypeWorkerCollection($this->typeworkers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(TypeWorkerRequest $request)
    {
        try {
            $typeworker = TypeWorker::create($request->getAllFields());
            $this->typeworker = new TypeWorkerResource($typeworker);
            return $this->createdResponse($this->typeworker, config('messages.success.create_title'), 'El tipo de trabajador '.config('messages.success.create_message'));
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
            $typeWorker->update($request->getAllFields());
            $this->typeworker = new TypeWorkerResource($typeWorker);
            return $this->successResponse($this->typeworker, config('messages.success.update_title'), 'El tipo de trabajador '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $typeWorker = TypeWorker::withTrashed()->findOrFail($id);

            $typeWorker->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'El tipo de trabajador '.config("messages.success.{$messageKey}_message"));
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $typeWorkers = new TypeWorkerCollection($request->resources);
            $typeWorkersIds = $typeWorkers->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = TypeWorker::destroyAll($typeWorkersIds, $force);

            $data = $active
                ? TypeWorker::with($this->relations)->select($this->fields)->get()
                : TypeWorker::with($this->relations)->select($this->fields)->onlyTrashed()->get();

            return $this->successResponse(
                $data,
                $result['title'],
                $result['message']
            );
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $typeworker = TypeWorker::onlyTrashed()->findOrFail($id);
            $typeworker->restore();
            $this->typeworker = new TypeWorkerResource($typeworker);
            return $this->successResponse(
                $this->typeworker,
                config('messages.success.restore_title'),
                'El tipo de trabajador '.config('messages.success.restore_message')
            );
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $typeWorkers = new TypeWorkerCollection($request->resources);
                $typeWorkersIds = $typeWorkers->getIdsAttribute();
                TypeWorker::whereIn('id', $typeWorkersIds)->restore();

                $this->typeworkers = TypeWorker::whereIn('id', $typeWorkersIds)->get();

                return $this->successResponse(
                    $this->typeworkers,
                    config('messages.success.restoreall_title'),
                    'Los tipos de trabajadores '.config('messages.success.restoreall_message')
                );
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
