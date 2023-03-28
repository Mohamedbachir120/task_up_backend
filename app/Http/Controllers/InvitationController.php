<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Auth;
use App\Events\TaskAffected;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $departement = Auth::user()->structurable;

        return response()->json($departement->invitations()
                        ->where('status','PENDING')
                        ->with('collaboration.created_by')->get(),200);
    }

    public function change_status(Request $request,$id){

        $invitation = Invitation::find($id);
        $invitation->status = $request['status'];
        $collaboration = $invitation->collaboration;
        if($request['status'] == 'ACCEPTED'){
            $collaboration->departements()->attach(Auth::user()->structurable_id);
            TaskAffected::dispatch($collaboration->created_by,
            "Invitation Accepté",Auth::user()->name." A rejoint votre collaboration ".$collaboration->topic);
        }else{
            TaskAffected::dispatch($collaboration->created_by,
            "Invitation rejeté",Auth::user()->name." A rejeté votre invitation ".$collaboration->topic);
        }



        $invitation->save();
        return response()->json(["success" => true,'message' =>"Status updated Successfully"],200);

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
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function show(Invitation $invitation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function edit(Invitation $invitation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invitation $invitation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invitation $invitation)
    {
        //
    }
}
