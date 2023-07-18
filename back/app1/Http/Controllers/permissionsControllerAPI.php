<?php

namespace App\Http\Controllers;

use App\Models\permissions;
use App\Http\Resources\permissionsResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\permissionsRequest;
use App\Http\Resources\Collections\permissionsCollection;

class permissionsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\permissionsCollection
     */
    public function index()
    {
        $this->authorize('viewAny', permissions::class);

        $permissions = permissions::all();

        return new permissionsCollection($permissions);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\permissionsRequest  $request
     * @return \App\Http\Resources\permissionsResource
     */
    public function store(permissionsRequest $request)
    {
        $this->authorize('create', permissions::class);

        $permissions = permissions::create($request->validated());

        return new permissionsResource($permissions);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\permissions  $permissions
     * @return \App\Http\Resources\permissionsResource
     */
    public function show(permissions $permissions)
    {
        $this->authorize('view', $permissions);

        return new permissionsResource($permissions);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\permissionsRequest  $request
     * @param  \App\Models\permissions  $permissions
     * @return \App\Http\Resources\permissionsResource
     */
    public function update(permissionsRequest $request, permissions $permissions)
    {
        $this->authorize('update', $permissions);

        $permissions->update($request->validated());

        return new permissionsResource($permissions);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\permissions  $permissions
     * @return \App\Http\Resources\permissionsResource
     */
    public function destroy(permissions $permissions)
    {
        $this->authorize('delete', $permissions);

        $permissions->delete();

        return new permissionsResource($permissions);

    }
}
