<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AssistCollection;
use App\Models\Assist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class AssistController extends Controller
{
    protected $assists;

    public function index()
    {
        try {
            $this->assists = Assist::orderBy('start_date', 'desc')->get();
            return new AssistCollection($this->assists);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
