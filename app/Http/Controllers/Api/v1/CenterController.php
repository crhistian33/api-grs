<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Http\Resources\v1\CenterCollection;
use App\Http\Resources\v1\CenterResource;
use App\Models\Center;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class CenterController extends Controller
{
    use ApiResponse;

    protected $centers;
    protected $center;
    protected array $relations = ['createdBy', 'updatedBy'];
    protected array $fields = ['id', 'code', 'name', 'mount', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $this->centers = Center::with($this->relations)
                ->select($this->fields)
                ->get();
            return new CenterCollection($this->centers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed() {
        try {
            $this->centers = Center::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed()
                ->get();
            return new CenterCollection($this->centers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CenterRequest $request)
    {
        try {
            $center = Center::create($request->getAllFields());
            $this->center = new CenterResource($center);
            return $this->createdResponse($this->center, config('messages.success.create_title'), 'El centro de costo '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Center $center)
    {
        try {
            return new CenterResource($center);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(CenterRequest $request, Center $center)
    {
        try {
            $center->update($request->getAllFields());
            $this->center = new CenterResource($center);
            return $this->successResponse($this->center, config('messages.success.update_title'), 'El centro de costo '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $center = Center::withTrashed()->findOrFail($id);

            $center->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'El centro de costo '.config("messages.success.{$messageKey}_message"));
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
            $centers = new CenterCollection($request->resources);
            $centerIds = $centers->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Center::destroyAll($centerIds, $force);

            $data = $active
                ? Center::with($this->relations)->select($this->fields)->get()
                : Center::with($this->relations)->select($this->fields)->onlyTrashed()->get();

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
            $center = Center::onlyTrashed()->findOrFail($id);
            $center->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'El centro de costo '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }


    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $centers = new CenterCollection($request->resources);
                $centerIds = $centers->getIdsAttribute();
                Center::whereIn('id', $centerIds)->restore();

                $this->centers = Center::whereIn('id', $centerIds)->get();

                return $this->successResponse($this->centers, config('messages.success.restoreall_title'), 'Los centros de costo '.config('messages.success.restoreall_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
