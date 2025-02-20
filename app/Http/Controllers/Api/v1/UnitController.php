<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\v1\UnitCollection;
use App\Http\Resources\v1\UnitResource;
use App\Http\Resources\v1\UnitShiftCollection;
use App\Models\Company;
use App\Models\Unit;
use App\Models\UnitShift;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    Use ApiResponse;

    protected $units;
    protected $unit;
    protected array $relations = ['center', 'customer', 'createdBy', 'updatedBy', 'shifts'];
    protected array $fields = ['id', 'code', 'name', 'min_assign', 'center_id', 'customer_id', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function all(?Company $company = null)
    {
        try {
            $query = Unit::with($this->relations)
                ->select($this->fields);

            if($company)
                $query->whereHas('customer', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                });

            $this->units = $query->get();
            return new UnitCollection($this->units);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed(?Company $company = null) {
        try {
            $query = Unit::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed();

            if($company)
                $query->whereHas('customer', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                });

            $this->units = $query->get();
            return new UnitCollection($this->units);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(UnitRequest $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $unit = Unit::create($request->getAllFields());

                if($request->has('shifts')) {
                    $shiftIds = collect($request->shifts)->pluck('id')->toArray();
                    $unit->shifts()->attach($shiftIds);
                }

                $this->unit = new UnitResource($unit);

                return $this->createdResponse($this->unit, config('messages.success.create_title'), 'La unidad '.config('messages.success.create_message'));
            }, 5);
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
            return DB::transaction(function() use ($request, $unit) {
                $unit->update($request->getAllFields());

                if($request->has('shifts')) {
                    $shiftIds = collect($request->shifts)->pluck('id')->toArray();
                    $unit->shifts()->sync($shiftIds);
                }

                $this->unit = new UnitResource($unit);

                return $this->successResponse($this->unit, config('messages.success.update_title'), 'La unidad '.config('messages.success.update_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $unit = Unit::withTrashed()->findOrFail($id);

            $unit->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'La unidad '.config("messages.success.{$messageKey}_message"));
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
            $unit = Unit::onlyTrashed()->findOrFail($id);
            $unit->restore();
            $this->unit = new UnitResource($unit);
            return $this->successResponse($this->unit, config('messages.success.restore_title'), 'La unidad '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request, ?Company $company = null)
    {
        try {
            $units = new UnitCollection($request->resources);
            $unitIds = $units->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Unit::destroyAll($unitIds, $force);

            $query = $active
                ? Unit::with($this->relations)->select($this->fields)
                : Unit::with($this->relations)->select($this->fields)->onlyTrashed();

            if($company)
                $query->whereHas('customer', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                });

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
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $units = new UnitCollection($request->resources);
                $unitIds = $units->getIdsAttribute();
                Unit::whereIn('id', $unitIds)->restore();
                $this->units = Unit::with($this->relations)->whereIn('id', $unitIds)->get();

                return $this->successResponse($this->units, config('messages.success.restoreall_title'), 'Las unidades '.config('messages.success.restoreall_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
