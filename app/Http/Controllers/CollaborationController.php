<?php

namespace App\Http\Controllers;

use App\Models\Collaboration;
use Illuminate\Http\Request;
use App\Events\TaskAffected;
use Auth;
use App\Models\Departement;
use App\Models\Invitation;
use App\Models\User;

class CollaborationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $departement = Auth::user()->structurable;

        return response()->json($departement->collaborations,200);
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
        $collaboration = Collaboration::create([
            "created_by"=>Auth::user()->id,
            "topic"=>$request['topic'],
            "description"=>$request['description']
        ]);
        $collaboration->departements()->attach(Auth::user()->structurable->id);
        $departements = Departement::whereIn('id', $request['members'])->get();
        foreach($departements as $dep){
            if($dep->id != Auth::user()->structurable->id){

                Invitation::create([
                    "collaboration_id"=>$collaboration->id,
                    "departement_id"=>$dep->id
                ]);
                $user = User::where('structurable_id',$dep->id )->where('role_id',2)->first();
                TaskAffected::dispatch($user->id,"Invitation de collaboration",
                Auth::user()->name." Souhaite vous inviter à rejoindre  ".$collaboration->topic);
            }


        }

        return response()->json(["success"=>true,"message"=>" collaboration created successfully"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Http\Response
     */
    public function show(Collaboration $collaboration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Http\Response
     */
    public function edit(Collaboration $collaboration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collaboration $collaboration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collaboration $collaboration)
    {
        //
    }
}
