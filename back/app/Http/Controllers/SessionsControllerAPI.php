<?php

namespace App\Http\Controllers;

use App\Models\Sessions;
use App\Http\Resources\SessionsResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\SessionsRequest;
use App\Http\Resources\Collections\SessionsCollection;

class SessionsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\SessionsCollection
     */
    public function index()
    {
        $this->authorize('viewAny', Sessions::class);

        $sessions = Sessions::all();

        return new SessionsCollection($sessions);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SessionsRequest  $request
     * @return \App\Http\Resources\SessionsResource
     */
    public function store(SessionsRequest $request)
    {
        $this->authorize('create', Sessions::class);

        $sessions = Sessions::create($request->validated());

        return new SessionsResource($sessions);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sessions  $sessions
     * @return \App\Http\Resources\SessionsResource
     */
    public function show(Sessions $sessions)
    {
        $this->authorize('view', $sessions);

        return new SessionsResource($sessions);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\SessionsRequest  $request
     * @param  \App\Models\Sessions  $sessions
     * @return \App\Http\Resources\SessionsResource
     */
    public function update(SessionsRequest $request, Sessions $sessions)
    {
        $this->authorize('update', $sessions);

        $sessions->update($request->validated());

        return new SessionsResource($sessions);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sessions  $sessions
     * @return \App\Http\Resources\SessionsResource
     */
    public function destroy(Sessions $sessions)
    {
        $this->authorize('delete', $sessions);

        $sessions->delete();

        return new SessionsResource($sessions);

    }
}
