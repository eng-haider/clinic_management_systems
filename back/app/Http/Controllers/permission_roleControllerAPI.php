<?php

namespace App\Http\Controllers;

use App\Models\permission_role;
use App\Http\Resources\permission_roleResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\permission_roleRequest;
use App\Http\Resources\Collections\permission_roleCollection;

class permission_roleControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\permission_roleCollection
     */
    public function index()
    {
        $this->authorize('viewAny', permission_role::class);

        $permission_role = permission_role::all();

        return new permission_roleCollection($permission_role);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\permission_roleRequest  $request
     * @return \App\Http\Resources\permission_roleResource
     */
    public function store(permission_roleRequest $request)
    {
        $this->authorize('create', permission_role::class);

        $permission_role = permission_role::create($request->validated());

        return new permission_roleResource($permission_role);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\permission_role  $permission_role
     * @return \App\Http\Resources\permission_roleResource
     */
    public function show(permission_role $permission_role)
    {
        $this->authorize('view', $permission_role);

        return new permission_roleResource($permission_role);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\permission_roleRequest  $request
     * @param  \App\Models\permission_role  $permission_role
     * @return \App\Http\Resources\permission_roleResource
     */
    public function update(permission_roleRequest $request, permission_role $permission_role)
    {
        $this->authorize('update', $permission_role);

        $permission_role->update($request->validated());

        return new permission_roleResource($permission_role);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\permission_role  $permission_role
     * @return \App\Http\Resources\permission_roleResource
     */
    public function destroy(permission_role $permission_role)
    {
        $this->authorize('delete', $permission_role);

        $permission_role->delete();

        return new permission_roleResource($permission_role);

    }
}
