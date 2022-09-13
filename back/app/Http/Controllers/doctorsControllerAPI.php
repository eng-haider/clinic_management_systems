<?php

namespace App\Http\Controllers;

use App\Models\doctors;
use App\Http\Resources\doctorsResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\doctorsRequest;
use App\Http\Resources\Collections\doctorsCollection;

class doctorsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\doctorsCollection
     */
    public function index()
    {
        $this->authorize('viewAny', doctors::class);

        $doctors = doctors::all();

        return new doctorsCollection($doctors);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\doctorsRequest  $request
     * @return \App\Http\Resources\doctorsResource
     */
    public function store(doctorsRequest $request)
    {
        $this->authorize('create', doctors::class);

        $doctors = doctors::create($request->validated());

        return new doctorsResource($doctors);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function show(doctors $doctors)
    {
        $this->authorize('view', $doctors);

        return new doctorsResource($doctors);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\doctorsRequest  $request
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function update(doctorsRequest $request, doctors $doctors)
    {
        $this->authorize('update', $doctors);

        $doctors->update($request->validated());

        return new doctorsResource($doctors);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function destroy(doctors $doctors)
    {
        $this->authorize('delete', $doctors);

        $doctors->delete();

        return new doctorsResource($doctors);

    }
}
