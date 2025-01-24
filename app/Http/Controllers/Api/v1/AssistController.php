<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Assist;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistController extends Controller
{
    protected $assists;

    public function index()
    {
        


        $states = State::all()->keyBy('id');

        $this->assists = Assist::with([
            'workerAssignment.assignment.unitShift.unit',
            'workerAssignment.assignment.unitShift.shift'
        ])
        ->get()
        ->groupBy('workerAssignment.assignment.unit_shift_id')
        ->map(function ($group) use ($states) {
            $first = $group->first(); // Tomar el primer elemento del grupo para obtener los datos de unit y shift

            return [
                'unit_shift_id' => $first->workerAssignment->assignment->unit_shift_id,
                'unit' => ['name' => $first->workerAssignment->assignment->unitShift->unit->name],
                'shift' => ['name' => $first->workerAssignment->assignment->unitShift->shift->name],
                'states' => $states->map(function ($state, $stateId) use ($group) {
                    return [
                        'state_id' => (int) $stateId,
                        'state_name' => $state->shortName,
                        'total' => $group->where('state_id', $stateId)->count(), // Si no hay registros, será 0
                    ];
                })->values(),
            ];
        })->values();
        return response()->json($this->assists);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
