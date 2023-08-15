<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Validator;
use App\DayToOpen;
use Illuminate\Http\Request;

class daytoopencontrollerr extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function update_day_open_byitem_id(Request $request)
    {
            echo $item_id;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        echo $request->item_id;
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DayToOpen  $dayToOpen
     * @return \Illuminate\Http\Response
     */
    public function show(DayToOpen $dayToOpen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DayToOpen  $dayToOpen
     * @return \Illuminate\Http\Response
     */
    public function edit(DayToOpen $dayToOpen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DayToOpen  $dayToOpen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DayToOpen $dayToOpen)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DayToOpen  $dayToOpen
     * @return \Illuminate\Http\Response
     */
    public function destroy(DayToOpen $dayToOpen)
    {
        //
    }
}
