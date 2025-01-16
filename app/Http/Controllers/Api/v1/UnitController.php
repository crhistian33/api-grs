<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\v1\UnitCollection;
use App\Http\Resources\v1\UnitResource;
use App\Http\Resources\v1\UnitShiftCollection;
use App\Models\Unit;
use App\Models\UnitShift;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class UnitController extends Controller
{
    Use ApiResponse;

    protected $units;

    public function index()
    {
        try {
            $this->units = Unit::all();
            return new UnitCollection($this->units);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getUnitShifts() {
        $unitshitfs = UnitShift::all();
        return new UnitShiftCollection($unitshitfs);
    }

    public function getDeleted() {
        try {
            $this->units = Unit::onlyTrashed()->get();
            return new UnitCollection($this->units);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(UnitRequest $request)
    {
        try {
            $unit = Unit::create($request->validated());
            return $this->createdResponse($unit, config('messages.success.create_title'), 'La unidad '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Unit $unit)
    {
        try {
            return new UnitResource($unit);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        try {
            $unit->update($request->validated());
            return $this->successResponse($unit, config('messages.success.update_title'), 'La unidad '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $unit = Unit::withTrashed()->findOrFail($id);
            if(!$delete) {
                $unit->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'La unidad '.config('messages.success.remove_message'));
            } else {
                $unit->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'La unidad '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $unit = Unit::onlyTrashed()->findOrFail($id);
            $unit->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'La unidad '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $units = new UnitCollection($request->resources);
            $unitIds = $units->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingUnit = Unit::withTrashed()->whereIn('id', $unitIds)->count();

            if ($existingUnit !== count($unitIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingUnit, 404);
            }

            if(!$delete) {
                Unit::whereIn('id', $unitIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Las unidades '.config('messages.success.removeall_message'));
            }
            else {
                Unit::whereIn('id', $unitIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Las unidades '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $units = new UnitCollection($request->resources);
            $unitIds = $units->getIdsAttribute();
            Unit::whereIn('id', $unitIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Las unidades '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
