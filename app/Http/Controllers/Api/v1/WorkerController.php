<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerRequest;
use App\Http\Resources\v1\WorkerBasicCollection;
use App\Http\Resources\v1\WorkerCollection;
use App\Http\Resources\v1\WorkerResource;
use App\Models\Assignment;
use App\Models\Company;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerController extends Controller
{
    use ApiResponse;

    protected $workers;
    protected $worker;
    protected array $relations = ['typeworker', 'company', 'createdBy', 'updatedBy'];
    protected array $fields = ['id', 'name', 'dni', 'birth_date', 'type_worker_id', 'company_id', 'created_by', 'updated_by'];
    protected array $basic_fields = ['id', 'name', 'dni', 'birth_Date'];

    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    public function all(?Company $company = null)
    {
        try {
            $query = Worker::with($this->relations)
                ->select($this->fields);

            if($company){
                $query->where('company_id', $company->id);
            }

            $this->workers = $query->get();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed(?Company $company = null) {
        try {
            $query = Worker::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed();

            if($company)
                $query->where('company_id', $company->id);

            $this->workers = $query->get();

            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTitulars(?Company $company = null) {
        try {
            $query = Worker::type('Titular')
                ->with($this->relations)
                ->select($this->fields);

            if($company)
                $query->where('company_id', $company->id);

            $this->workers = $query->get();
            return new WorkerCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getUnassigned(Request $request) {
        try {
            $query = Worker::type('Titular')->unassigned();

            if($request->has('company_id')) {
                $companyId = $request->input('company_id');
                $query->where('company_id', $companyId);
            }

            if ($request->has('assignment_id')) {
                $assignmentId = $request->input('assignment_id');
                $query->orWhereHas('assignments', function ($q) use ($assignmentId) {
                    $q->where('assignments.id', $assignmentId);
                });
            }

            $this->workers = $query->select($this->basic_fields)->get();

            return new WorkerBasicCollection($this->workers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(WorkerRequest $request)
    {
        try {
            $workerStore = Worker::create($request->getAllFields());
            $this->worker = new WorkerResource($workerStore);
            return $this->createdResponse($this->worker, config('messages.success.create_title'), 'El trabajador '.config('messages.success.create_message'));
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
            $worker->update($request->getAllFields());
            $this->worker = new WorkerResource($worker);
            return $this->successResponse($this->worker, config('messages.success.update_title'), 'El trabajador '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $worker = Worker::withTrashed()->findOrFail($id);

            $worker->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(
                null,
                config("messages.success.{$messageKey}_title"),
                'El trabajador '.config("messages.success.{$messageKey}_message")
            );
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request, ?Company $company = null)
    {
        try {
            $workersRequest = new WorkerCollection($request->resources);
            $workersIds = $workersRequest->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Worker::destroyAll($workersIds, $force);

            $query = $active
                ? Worker::with($this->relations)->select($this->fields)
                : Worker::with($this->relations)->select($this->fields)->onlyTrashed();

            if($company)
                $query->where('company_id', $company->id);

            $data = $query->get();

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
            $worker = Worker::onlyTrashed()->findOrFail($id);
            $worker->restore();
            $this->worker = new WorkerResource($worker);
            return $this->successResponse(
                $this->worker,
                config('messages.success.restore_title'),
                'El trabajador '.config('messages.success.restore_message')
            );
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $workers = new WorkerCollection($request->resources);
                $workersIds = $workers->getIdsAttribute();
                Worker::whereIn('id', $workersIds)->restore();

                $this->workers = Worker::with(['typeworker', 'company'])->whereIn('id', $workersIds)->get();

                return $this->successResponse(
                    $this->workers,
                    config('messages.success.restoreall_title'),
                    'Los trabajadores '.config('messages.success.restoreall_message')
                );
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
