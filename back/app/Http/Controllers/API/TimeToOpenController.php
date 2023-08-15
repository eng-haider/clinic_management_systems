<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Validator;
use App\TimeToOpen;
use Illuminate\Http\Request;

class TimeToOpenController extends Controller
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TimeToOpen  $timeToOpen
     * @return \Illuminate\Http\Response
     */
    public function show(TimeToOpen $timeToOpen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TimeToOpen  $timeToOpen
     * @return \Illuminate\Http\Response
     */
    public function edit(TimeToOpen $timeToOpen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TimeToOpen  $timeToOpen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeToOpen $timeToOpen)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TimeToOpen  $timeToOpen
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        echo 'hi';
        $TimeToOpen=TimeToOpen::findOrFail($id);
        $TimeToOpen->delete();

        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);
    }
}
