<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Http\Resources\v1\ShiftCollection;
use App\Http\Resources\v1\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class ShiftController extends Controller
{
    use ApiResponse;

    protected $shifts;

    public function index()
    {
        try {
            $this->shifts = Shift::all();
            return new ShiftCollection($this->shifts);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->shifts = Shift::onlyTrashed()->get();
            return new ShiftCollection($this->shifts);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(ShiftRequest $request)
    {
        try {
            $shift = Shift::create($request->validated());
            return $this->createdResponse($shift, config('messages.success.create_title'), 'El turno '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Shift $shift)
    {
        try {
            return new ShiftResource($shift);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(ShiftRequest $request, Shift $shift)
    {
        try {
            $shift->update($request->validated());
            return $this->successResponse($shift, config('messages.success.update_title'), 'El turno '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $shift = Shift::withTrashed()->findOrFail($id);
            if(!$delete) {
                $shift->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'El turno '.config('messages.success.remove_message'));
            } else {
                $shift->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El turno '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $shift = Shift::onlyTrashed()->findOrFail($id);
            $shift->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'El turno '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $shifts = new ShiftCollection($request->resources);
            $shiftIds = $shifts->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingShift = Shift::withTrashed()->whereIn('id', $shiftIds)->count();

            if ($existingShift !== count($shiftIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingShift, 404);
            }

            if(!$delete) {
                Shift::whereIn('id', $shiftIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Los turnos '.config('messages.success.removeall_message'));
            }
            else {
                Shift::whereIn('id', $shiftIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Los turnos '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $shifts = new ShiftCollection($request->resources);
            $shiftIds = $shifts->getIdsAttribute();
            Shift::whereIn('id', $shiftIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Los turnos '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
