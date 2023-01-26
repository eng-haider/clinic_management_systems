<?php

namespace App\Http\Controllers;

use App\Models\bills;
use App\Http\Resources\billsResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\billsRequest;
use App\Http\Resources\Collections\billsCollection;

class billsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\billsCollection
     */
    public function index()
    {
        $this->authorize('viewAny', bills::class);

        $bills = bills::all();

        return new billsCollection($bills);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\billsRequest  $request
     * @return \App\Http\Resources\billsResource
     */
    public function store(billsRequest $request)
    {
        $this->authorize('create', bills::class);

        $bills = bills::create($request->validated());

        return new billsResource($bills);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\bills  $bills
     * @return \App\Http\Resources\billsResource
     */
    public function show(bills $bills)
    {
        $this->authorize('view', $bills);

        return new billsResource($bills);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\billsRequest  $request
     * @param  \App\Models\bills  $bills
     * @return \App\Http\Resources\billsResource
     */
    public function update(billsRequest $request, bills $bills)
    {
        $this->authorize('update', $bills);

        $bills->update($request->validated());

        return new billsResource($bills);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\bills  $bills
     * @return \App\Http\Resources\billsResource
     */
    public function destroy(bills $bills)
    {
      


       if($bills->case->user_id==auth()->user()->id)
       {
        $bills->delete();
        return new billsResource($bills);
       }
      

        

    }
}
