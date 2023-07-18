<?php

namespace App\Http\Controllers;

use App\Models\roles;
use App\Http\Resources\rolesResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\rolesRequest;
use App\Http\Resources\Collections\rolesCollection;

class rolesControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\rolesCollection
     */
    public function index()
    {
        $this->authorize('viewAny', roles::class);

        $roles = roles::all();

        return new rolesCollection($roles);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\rolesRequest  $request
     * @return \App\Http\Resources\rolesResource
     */
    public function store(rolesRequest $request)
    {
        $this->authorize('create', roles::class);

        $roles = roles::create($request->validated());

        return new rolesResource($roles);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\roles  $roles
     * @return \App\Http\Resources\rolesResource
     */
    public function show(roles $roles)
    {
        $this->authorize('view', $roles);

        return new rolesResource($roles);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\rolesRequest  $request
     * @param  \App\Models\roles  $roles
     * @return \App\Http\Resources\rolesResource
     */
    public function update(rolesRequest $request, roles $roles)
    {
        $this->authorize('update', $roles);

        $roles->update($request->validated());

        return new rolesResource($roles);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\roles  $roles
     * @return \App\Http\Resources\rolesResource
     */
    public function destroy(roles $roles)
    {
        $this->authorize('delete', $roles);

        $roles->delete();

        return new rolesResource($roles);

    }
}
