<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UnitResource;
use App\Http\Resources\v1\UnitShiftCollection;
use App\Models\Assignment;
use App\Models\Company;
use App\Models\UnitShift;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class UnitShiftController extends Controller
{
    use ApiResponse;

    protected $unitshifts;

    public function all(?Company $company = null) {
        $query = UnitShift::whereHas('unit')
        ->whereHas('shift');

        if ($company) {
            $query->whereHas('unit.customer', function($q) use ($company) {
                $q->where('company_id', $company->id);
            });
        }

        $this->unitshifts = $query->get()
            ->map(function ($unitshift) {
                return [
                    'id' => $unitshift->id,
                    'name' => $unitshift->unit->name.' - '.$unitshift->shift->name,
                    'assignments' => $unitshift->assignments,
                    'unit' => new UnitResource($unitshift->unit),
                ];
            });

        return response()->json([
            'data' => $this->unitshifts
        ]);
    }

    public function getWithAssigns() {
        $unitshifts = UnitShift::whereHas('assignments', function($query) {
            return $query->where('state', true);
        })
        ->get()
        ->map(function($unitshift) {
            return [
                'id' => $unitshift->id,
                'name' => "{$unitshift->unit->name} - {$unitshift->shift->name}",
                'unit' => $unitshift->unit,
                'assignments' =>$unitshift->assignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id
                    ];
                })
                // 'unit_shift_id' => $assignment->unit_shift_id,
                // 'name' => "{$assignment->unitShift->unit->name} - {$assignment->unitShift->shift->name}",
                // ->map(function ($unitshift) {
                //     return [
                //         'id' => $unitshift->id
                //     ];
                // }),
            ];
        });

        return response()->json([
            'data' => $unitshifts
        ]);
    }

    public function verifiedAssignment(UnitShift $unitshift, Assignment $assignment = null) {
        if(is_null($assignment)) {
            $assignments = $unitshift->assignments;
            if(!$assignments->isEmpty()) {
                $u_assignment = $assignments->where('state', '=', true)->first();
                if($u_assignment) {
                    return $this->verifiedResponse(config('messages.verified.exist_title'), config('messages.verified.exist_message'), $u_assignment->id, true);
                }
            }

        } else {
            $u_assignment = Assignment::where('unit_shift_id', $unitshift->id)
                ->where('id', '!=', $assignment->id)
                ->where('state', '=', true)
                ->first();
            if($u_assignment) {
                return $this->verifiedResponse(config('messages.verified.exist_title'), config('messages.verified.exist_message'), $u_assignment, true);
            }
        }
        return response()->json([
            'verified' => false
        ], Response::HTTP_OK);
    }

    public function getWorkers(UnitShift $unitShift) {
        $query = $unitShift
            ->assignments()
            ->with('workers')
            ->where('state', true)
            ->first();

        $workers = $query->workers->map(function($worker) {
            return [
                'id' => $worker->id,
                'name' => $worker->name,
            ];
        });

        return response()->json([
            'data' => $workers
        ]);
    }
}
