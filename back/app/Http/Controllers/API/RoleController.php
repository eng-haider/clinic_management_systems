<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Validator;
use App\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true ,
                'data' => Role::all()
            ], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'role_name' => 'required'
        ]);

        $role = new Role;
        $role->role_name = $request->role_name;
        $role->save();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);

    }


    public function update(Request $request)
    {
        $this->validate($request,[
            'role_id' => 'required',
            'role_name' => 'required'
        ]);

        $role = Role::find($request->role_id);
        $role->role_name = $request->role_name;
        $role->save();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }

}
