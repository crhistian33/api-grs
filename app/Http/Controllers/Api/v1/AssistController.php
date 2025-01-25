<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AssistCollection;
use App\Models\Assist;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistController extends Controller
{
    protected $assists;

    public function index()
    {
        $this->assists = Assist::orderBy('start_date', 'desc')->get();
        return new AssistCollection($this->assists);
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
