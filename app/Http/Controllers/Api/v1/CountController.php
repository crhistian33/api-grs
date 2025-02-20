<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Shift;
use App\Models\TypeWorker;
use App\Models\Unit;
use App\Models\Worker;
use Exception;

class CountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getcounts() {
        try {
            return response()->json([
                'centers' => Center::count(),
                'companies' => Company::count(),
                'customers' => Customer::count(),
                'shifts' => Shift::count(),
                'typeworkers' => TypeWorker::count(),
                'units' => Unit::count(),
                'workers' => Worker::count(),
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    public function getCountsByCompany(Company $company) {
        try {
            return response()->json([
                'centers' => Center::count(),
                'customers' => Customer::where('company_id', $company->id)->count(),
                'shifts' => Shift::count(),
                'typeworkers' => TypeWorker::count(),
                'units' => Unit::whereHas('customer', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count(),
                'workers' => Worker::where('company_id', $company->id)->count(),
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
