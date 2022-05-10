<?php

namespace App\Http\Controllers;

use App\Models\User;
use GrahamCampbell\ResultType\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class JuezController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        /******
         * CONSULTA BASE DE DATOS EN POSTGRES
         */
        $list_juez = DB::table('curador.mae_curador')->get();        
        //return response()->json($list_juez,200);


        $user = DB::select('select * from curador.mae_usuario where c_usuario = ? and c_clave= ?', ['RomeoSoft', bcrypt("123456")]);
        $user = DB::table('curador.mae_usuario')->where([
            'c_usuario' => 'RomeoSoft',
            'c_clave' => bcrypt('123456')
        ])->first();
        
        return response()->json($user,200);


        $list_juez = User::all();        
        return response()->json($list_juez,200);


   

        

        
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
