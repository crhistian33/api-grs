<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\StateCollection;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    protected $states;
    protected $state;
    protected array $fields = ['id', 'name', 'shortName', 'type'];

    public function index()
    {
        $this->states = State::select($this->fields)->get();
        return new StateCollection($this->states);
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
