<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssistRequest;
use App\Http\Resources\v1\AssistCollection;
use App\Http\Resources\v1\UnitShiftResource;
use App\Models\Assist;
use App\Models\State;
use App\Models\Worker;
use App\Models\WorkerAssignment;
use Illuminate\Http\Request;
use Exception;

class AssistController extends Controller
{
    protected $assists;

    public function getWorkerAssist(AssistRequest $request) {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Worker::whereHas('assignments', function($q) {
            $q->where('state', 1);
        });

        if($request->has('company_id')) {
            $companyId = $request->input('company_id');
            $query->whereHas('company', function($q) use($companyId) {
                $q->where('id', $companyId);
            });
        }

        $workers = $query->get();

        $this->assists = $workers->map(function($worker) use($dateFrom, $dateTo) {
            return [
                'id' => $worker->id,
                'name' => $worker->name,
                'dni' => $worker->dni,
                'unitshift' => [
                    'id' => $worker->assignments[0]->unitShift->id,
                    'unit' => [
                        'id' => $worker->assignments[0]->unitShift->unit->id,
                        'name' => $worker->assignments[0]->unitShift->unit->name,
                        'customer' => [
                            'id' => $worker->assignments[0]->unitShift->unit->customer->id,
                            'company' => [
                                'id' => $worker->assignments[0]->unitShift->unit->customer->company->id
                            ]
                        ],
                    ],
                    'shift' => [
                        'id' => $worker->assignments[0]->unitShift->shift->id
                    ],
                ],
                'days' => $this->generateDaysInRange(
                    $worker,
                    $worker->assignments[0]->unitShift->shift,
                    $dateFrom,
                    $dateTo
                )
            ];
        });

        return response()->json([
            'data' => $this->assists
        ]);
    }

    private function generateDaysInRange($worker, $shift, string $dateFrom, string $dateTo): array
    {
        $defaultState = [
            'id' => null,
            'name' => $shift->shortName === 'D' ? 'Asistencia mañana' : 'Asistencia noche',
            'shortName' => $shift->shortName,
            'type' => 1
        ];

        $inassistances = [];
        $idsInassistance = [];
        foreach ($worker->inassists as $inassist) {
            $inassistStart = max(strtotime($inassist->start_date), strtotime($dateFrom));

            $dateKey = date('Y-m-d', $inassistStart);
            $inassistances[$dateKey] = [
                'id' => $inassist->state->id,
                'name' => $inassist->state->name,
                'shortName' => $inassist->state->shortName,
                'type' => $inassist->state->type,
            ];
            $idsInassistance[$dateKey] = $inassist->id;
        }
        $days = [];
        $currentDate = strtotime($dateFrom);
        $endDate = strtotime($dateTo);

        while ($currentDate <= $endDate) {
            $currentDay = (int)date('d', $currentDate);
            $currentMonth = (int)date('m', $currentDate);
            $currentKey = date('Y-m-d', $currentDate);

            $days[] = [
                'key' => $currentKey,
                'day' => $currentDay,
                'month' => $currentMonth,
                'state' => $inassistances[$currentKey] ?? $defaultState,
                'inassist_id' => $idsInassistance[$currentKey] ?? null
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $days;
    }

    public function getWorkerAssistBreaks(AssistRequest $request) {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Worker::whereHas('assignmentBreaks', function($q) {
            $q->where('state', 1);
        })->with(['assignmentBreaks' => function ($query) {
            $query->where('state', 1);
        }])->get();

        $this->assists = $query->map(function ($worker) use($dateFrom, $dateTo) {
            return [
                'id' => $worker->id,
                'name' => $worker->name,
                'dni' => $worker->dni,
                'company' => $worker->company,
                'days' => $this->getDaysBreaks($worker, $dateFrom, $dateTo),
            ];
        });

        return response()->json([
            'data' => $this->assists
        ]);
    }

    public function getDaysBreaks(Worker $worker, string $dateFrom, string $dateTo): array
    {
        $inassistances = [];
        $idsInassistance = [];
        foreach ($worker->inassists as $inassist) {
            $inassistStart = max(strtotime($inassist->start_date), strtotime($dateFrom));

            $dateKey = date('Y-m-d', $inassistStart);
            $inassistances[$dateKey] = [
                'id' => $inassist->state->id,
                'name' => $inassist->state->name,
                'shortName' => $inassist->state->shortName,
                'type' => $inassist->state->type,
            ];
            $idsInassistance[$dateKey] = $inassist->id;
        }

        $days = [];
        $currentDate = strtotime($dateFrom);
        $endDate = strtotime($dateTo);

        while ($currentDate <= $endDate) {
            $currentKey = date('Y-m-d', $currentDate);
            $dayName = date('l', $currentDate);

            if (isset($inassistances[$currentKey])) {
                $state = $inassistances[$currentKey];
            } else {
                $assignment = $worker->assignmentBreaks
                    ->where('day', $dayName)
                    ->first();
                $shift = $assignment ? $assignment->unitShift->shift : null;

                if ($shift) {
                    $state = [
                        'id' => null,
                        'name' => $shift->shortName === 'D' ? 'Asistencia mañana' : 'Asistencia noche',
                        'shortName' => $shift->shortName,
                        'type' => 1
                    ];
                } else {
                    $state = null;
                }
            }

            $days[] = [
                'key' => $currentKey,
                'day' => (int)date('d', $currentDate),
                'month' => (int)date('m', $currentDate),
                'state' => $state,
            ];

            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $days;
    }
}
