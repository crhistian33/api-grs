<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AssignmentCollection;
use App\Models\Assignment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class AssignmentController extends Controller
{
    use ApiResponse;

    protected $assigments;

    public function index()
    {
        try {
            $this->assigments = Assignment::all();
            return new AssignmentCollection($this->assigments);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
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
