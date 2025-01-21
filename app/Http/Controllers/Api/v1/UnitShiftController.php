<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\UnitShift;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class UnitShiftController extends Controller
{
    use ApiResponse;

    protected $unitshifts;

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
}
