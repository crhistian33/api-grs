<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Http\Resources\v1\ShiftCollection;
use App\Http\Resources\v1\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    use ApiResponse;

    protected $shifts;
    protected $shift;
    protected array $relations = ['createdBy', 'updatedBy'];
    protected array $fields = ['id', 'name', 'shortName', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $this->shifts = Shift::with($this->relations)
                ->select($this->fields)
                ->get();

            return new ShiftCollection($this->shifts);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed() {
        try {
            $this->shifts = Shift::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed()
                ->get();

            return new ShiftCollection($this->shifts);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(ShiftRequest $request)
    {
        try {
            $shift = Shift::create($request->validated());
            $this->shift = new ShiftResource($shift);
            return $this->createdResponse($this->shift, config('messages.success.create_title'), 'El turno '.config('messages.success.create_message'));
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
            $this->shift = new ShiftResource($shift);
            return $this->successResponse($this->shift, config('messages.success.update_title'), 'El turno '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $shift = Shift::withTrashed()->findOrFail($id);

            $shift->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'El turno '.config("messages.success.{$messageKey}_message"));
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
            $shift = Shift::onlyTrashed()->findOrFail($id);
            $shift->restore();
            $this->shift = new ShiftResource($shift);
            return $this->successResponse($this->shift, config('messages.success.restore_title'), 'El turno '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $shifts = new ShiftCollection($request->resources);
            $shiftIds = $shifts->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Shift::destroyAll($shiftIds, $force);

            $data = $active
                ? shift::with($this->relations)->select($this->fields)->get()
                : shift::with($this->relations)->select($this->fields)->onlyTrashed()->get();

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
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $shifts = new ShiftCollection($request->resources);
                $shiftIds = $shifts->getIdsAttribute();
                Shift::whereIn('id', $shiftIds)->restore();
                $this->shifts = Shift::whereIn('id', $shiftIds)->get();

                return $this->successResponse($this->shifts, config('messages.success.restoreall_title'), 'Los turnos '.config('messages.success.restoreall_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
