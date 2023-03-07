<?php

namespace App\Http\Controllers;

use App\Models\Objectif;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class ObjectifController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $now = Carbon::now();
        $objectifs = Auth::user()->objectifs()->where('year',$now->year)->get();
        
        return response()->json(['objectifs' => $objectifs],200);
        
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
        $now = Carbon::now();
        $objectifs = Auth::user()->objectifs()->where('year',$now->year)->get();
        $count  = count($objectifs); 
        if($count == 0)
        {
            Objectif::create([
                'year'=>$now->year,
                'yearly'=>$request['yearly'],
                'monthly'=>$request['monthly'],
                'user_id'=>Auth::user()->id
            ]);
        }else {
            $objectif = $objectifs[$count -1];
            $objectif->yearly = $request['yearly'];
            $objectif->monthly = $request['monthly'];
            $objectif->save();


        }
        return response()->json(['success'=>true,'message'=>"objectif ajouté avec succès"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Objectif  $objectif
     * @return \Illuminate\Http\Response
     */
    public function show(Objectif $objectif)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Objectif  $objectif
     * @return \Illuminate\Http\Response
     */
    public function edit(Objectif $objectif)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Objectif  $objectif
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Objectif $objectif)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Objectif  $objectif
     * @return \Illuminate\Http\Response
     */
    public function destroy(Objectif $objectif)
    {
        //
    }
}
