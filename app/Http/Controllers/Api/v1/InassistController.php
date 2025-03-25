<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UnitResource;
use App\Models\Company;
use App\Models\Inassist;
use App\Models\State;
use App\Models\Worker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use App\Traits\DateFormatter;

class InassistController extends Controller
{
    use ApiResponse;
    use DateFormatter;

    protected $inassists;

    public function all(?Company $company = null) {
        // $workers = Worker::whereHas('workerAssignments', function($query) {
        //     $query->whereHas('assignment');
        // })->get();

        // return response()->json($workers);
        try {
            $query = Inassist::select(
                'worker_id',
                'unit_shift_id',
                'month',
                DB::raw("GROUP_CONCAT(DAY(start_date) ORDER BY start_date ASC SEPARATOR ', ') as days")
            )
            ->with(['worker', 'unitShift'])
            ->orderBy('month', 'DESC')
            // ->with(['worker.assignments' => function($query) {
            //     $query->wherePivot('deleted_at', null)
            //           ->where('worker.assignments.state', true);
            // }])
            // ->whereHas('worker.assignments', function($query) {
            //     $query->wherePivot('deleted_at', null)
            //           ->where('worker.assignments.state', true);
            // })
            // ->whereHas('worker.assignments', function($q) {
            //     $q->where('state', 1)
            //         ->whereHas('workerAssignments', function($qr) {
            //             $qr->whereNull('deleted_at');
            //         });
            // })
            // ->with([
            //     'worker.assignments' => function($q) {
            //         $q->where('state', 1)
            //             ->whereHas('workerAssignments', function($qr) {
            //                 $qr->whereNull('deleted_at');
            //             })
            //             ->with('unitShift');
            //     }
            // ])
            // ->with([
            //     'worker.assignments',
            //     // 'worker.workerAssignments' => function($q) {
            //     //     $q->whereNull('deleted_at') // Only active worker assignments
            //     //       ->with(['assignment.unitShift']);
            //     // }
            // ])
            // ->whereHas('worker.workerAssignments.assignment', function($q) {
            //     $q->where('state', 1);
            // })
            // ->with([
            //     'worker',
            //     'worker.workerAssignments',
            //     'worker.workerAssignments.assignment.unitShift'
            // ])
            // ->with(['worker', 'worker.assignments' => function($query) {
            //     $query->where('state', 1)->with('unitShift');
            // }])
            ->whereHas('state', function($q) {
                $q->where('shortName', 'X');
            });

            if($company) {
                $query->whereHas('worker', function($q) use($company) {
                    $q->where('company_id', $company->id);
                });
            }

            $query->groupBy('worker_id', 'month', 'unit_shift_id');

            //$query->groupBy('worker_id', 'month');

            //return response()->json($query->get());

            $this->inassists = $query->get()->map(function ($item, $index) {
                //$activeAssignment = $item->worker->assignments->first();
                return [
                    'id' => $index + 1,
                    'worker' => $item->worker,
                    'month' => $item->month,
                    'days' => $item->days,
                    //'assignment' => $item->worker->assignments,
                    'unitshift' => [
                        'unit' => new UnitResource($item->unitShift->unit),
                        'shift' => $item->unitShift->shift,
                    ]
                ];
            });

            return response()->json([
                'data' => $this->inassists
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request) {
        try {
            return DB::transaction(function() use ($request) {
                $dataCollection = collect($request->all());

                $workerIds = $dataCollection->pluck('worker_id')->unique()->toArray();
                $unitshiftIds = $dataCollection->pluck('unitshift_id')->unique()->toArray();
                $months = $dataCollection->pluck('month')->unique()->toArray();

                $workersInUnit = Worker::whereHas('assignments', function($query) use ($unitshiftIds) {
                    $query->where('state', 1)
                          ->whereIn('unit_shift_id', $unitshiftIds);
                })->pluck('id')->toArray();

                $workerIds = array_intersect($workerIds, $workersInUnit);

                Inassist::whereIn('worker_id', $workerIds)
                    ->whereIn('month', $months)->delete();

                $dataToInsert = $dataCollection->map(function ($item) {
                    return [
                        'worker_id' => $item['worker_id'],
                        'state_id' => State::getIdByValue('X'),
                        'start_date' => $item['start_date'],
                        'month' => $item['month'],
                        'unit_shift_id' => $item['unitshift_id'],
                        'created_by' => $item['created_by'],
                        'created_at' => now(),
                    ];
                })->toArray();

                Inassist::insert($dataToInsert);

                $query = Inassist::select(
                    'worker_id',
                    'month',
                    DB::raw("GROUP_CONCAT(DAY(start_date) ORDER BY start_date ASC SEPARATOR ', ') as days")
                )
                ->orderBy('month', 'DESC')
                ->with(['worker', 'worker.assignments' => function($query) {
                    $query->where('state', 1)->with('unitShift');
                }])
                ->whereHas('state', function($q) {
                    $q->where('shortName', 'X');
                })
                ->whereIn('worker_id', $workerIds)
                ->whereIn('month', $months)
                ->groupBy('worker_id', 'month');

                $this->inassists = $query->get()->map(function ($item) {
                    $activeAssignment = $item->worker->assignments->first();
                    return [
                        'id' => $item->worker_id,
                        'name' => $item->worker->name,
                        'dni' => $item->worker->dni,
                        'month' => $item->month,
                        'days' => $item->days,
                        'unitshift' => $activeAssignment ? [
                            'unit' => new UnitResource($activeAssignment->unitShift->unit),
                            'shift' => $activeAssignment->unitShift->shift
                        ] : null
                    ];
                });

                return $this->createdResponse(
                    $this->inassists,
                    config('messages.success.create_many_title'),
                    'Los descansos '.config('messages.success.create_many_message')
                );
            });
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDaysUnitMonth(Request $request) {
        $month = $request->input('month');
        $unit_shift_id = $request->input('unit_shift_id');

        $days = Inassist::whereHas('worker', function($q) use($unit_shift_id) {
                $q->whereHas('assignments', function($q) use($unit_shift_id) {
                    $q->where(['unit_shift_id' => $unit_shift_id, 'state' => true])
                        ->whereNull('worker_assignments.deleted_at');
                });
            })
            ->where('month', $month)
            ->get();

        $response = $days->map(function($day) {
            return [
                'worker_id' => $day->worker_id,
                //'state' => $day->state->shortName,
                'start_date' => $day->start_date,
                'month' => $day->month,
            ];
        });

        return response()->json([
            'data' => $response
        ]);
    }

    public function destroy(Request $request) {
        try {
            $workerId = $request->input('worker_id');
            $month = $request->input('month');

            Inassist::where('worker_id', $workerId)
                ->where('month', $month)
                ->delete();

            return $this->successResponse(
                null,
                config('messages.success.delete_title'),
                config('messages.success.delete_message')
            );
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyMany(Request $request) {
        try {
            return DB::transaction(function() use ($request) {
                $dataCollection = collect($request->all());
                $workerIds = $dataCollection->pluck('worker_id')->unique()->toArray();
                $months = $dataCollection->pluck('month')->unique()->toArray();

                Inassist::whereIn('worker_id', $workerIds)
                    ->whereIn('month', $months)
                    ->delete();

                return $this->successResponse(
                    null,
                    config('messages.success.deleteall_title'),
                    config('messages.success.deleteall_message')
                );
            });
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
