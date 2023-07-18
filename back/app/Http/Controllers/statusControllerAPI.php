<?php

namespace App\Http\Controllers;

use App\Models\status;
use App\Http\Resources\statusResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\statusRequest;
use App\Http\Resources\Collections\statusCollection;

class statusControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\statusCollection
     */
    public function index()
    {
        $this->authorize('viewAny', status::class);

        $status = status::all();

        return new statusCollection($status);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\statusRequest  $request
     * @return \App\Http\Resources\statusResource
     */
    public function store(statusRequest $request)
    {
        $this->authorize('create', status::class);

        $status = status::create($request->validated());

        return new statusResource($status);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\status  $status
     * @return \App\Http\Resources\statusResource
     */
    public function show(status $status)
    {
        $this->authorize('view', $status);

        return new statusResource($status);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\statusRequest  $request
     * @param  \App\Models\status  $status
     * @return \App\Http\Resources\statusResource
     */
    public function update(statusRequest $request, status $status)
    {
        $this->authorize('update', $status);

        $status->update($request->validated());

        return new statusResource($status);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\status  $status
     * @return \App\Http\Resources\statusResource
     */
    public function destroy(status $status)
    {
        $this->authorize('delete', $status);

        $status->delete();

        return new statusResource($status);

    }
}
