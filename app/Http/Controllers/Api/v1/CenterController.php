<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Http\Resources\v1\CenterCollection;
use App\Http\Resources\v1\CenterResource;
use App\Models\Center;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class CenterController extends Controller
{
    use ApiResponse;

    protected $centers;

    public function index()
    {
        try {
            $this->centers = Center::all();
        return new CenterCollection($this->centers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->centers = Center::onlyTrashed()->get();
            return new CenterCollection($this->centers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CenterRequest $request)
    {
        try {
            $center = Center::create($request->validated());
            return $this->createdResponse($center, config('messages.success.create_title'), 'El centro de costo '.config('messages.success.create_message'));
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
            $center->update($request->validated());
            return $this->successResponse($center, config('messages.success.update_title'), 'El centro de costo '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $center = Center::withTrashed()->findOrFail($id);
            if(!$delete) {
                $center->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'El centro de costo '.config('messages.success.remove_message'));
            } else {
                $center->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El centro de costo '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
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

    public function destroyAll(Request $request)
    {
        try {
            $centers = new CenterCollection($request->resources);
            $centerIds = $centers->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingCenter = Center::withTrashed()->whereIn('id', $centerIds)->count();

            if ($existingCenter !== count($centerIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingCenter, 404);
            }

            if(!$delete) {
                Center::whereIn('id', $centerIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Los centros de costo '.config('messages.success.removeall_message'));
            }
            else {
                Center::whereIn('id', $centerIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Los centros de costo '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $centers = new CenterCollection($request->resources);
            $centerIds = $centers->getIdsAttribute();
            Center::whereIn('id', $centerIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Los centros de costo '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
