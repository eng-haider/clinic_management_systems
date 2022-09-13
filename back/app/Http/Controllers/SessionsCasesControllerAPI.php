<?php

namespace App\Http\Controllers;

use App\Models\SessionsCases;
use App\Http\Resources\SessionsCasesResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\SessionsCasesRequest;
use App\Http\Resources\Collections\SessionsCasesCollection;

class SessionsCasesControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\SessionsCasesCollection
     */
    public function index()
    {
        $this->authorize('viewAny', SessionsCases::class);

        $sessionsCases = SessionsCases::all();

        return new SessionsCasesCollection($sessionsCases);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SessionsCasesRequest  $request
     * @return \App\Http\Resources\SessionsCasesResource
     */
    public function store(SessionsCasesRequest $request)
    {
        $this->authorize('create', SessionsCases::class);

        $sessionsCases = SessionsCases::create($request->validated());

        return new SessionsCasesResource($sessionsCases);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return \App\Http\Resources\SessionsCasesResource
     */
    public function show(SessionsCases $sessionsCases)
    {
        $this->authorize('view', $sessionsCases);

        return new SessionsCasesResource($sessionsCases);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\SessionsCasesRequest  $request
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return \App\Http\Resources\SessionsCasesResource
     */
    public function update(SessionsCasesRequest $request, SessionsCases $sessionsCases)
    {
        $this->authorize('update', $sessionsCases);

        $sessionsCases->update($request->validated());

        return new SessionsCasesResource($sessionsCases);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return \App\Http\Resources\SessionsCasesResource
     */
    public function destroy(SessionsCases $sessionsCases)
    {
        $this->authorize('delete', $sessionsCases);

        $sessionsCases->delete();

        return new SessionsCasesResource($sessionsCases);

    }
}
