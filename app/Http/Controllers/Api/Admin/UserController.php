<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    /**
     * Display Listing of the resources
     *
     */
    public function index()
    {
        // get users
        $users = User::when(request()->q, function($users) {
           $users = $users->where('name', 'like', '%'. request()->q . '%' );
        })->latest()->paginate(5);

        // return success with API resource
        return new UserResource(true, 'List Data Users', $users);
    }

    /**
     * Store User
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required',
           'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // creating user
        $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password)
        ]);

        // return success with Api Resource
        if($user) {
            return new UserResource(true, 'Data Berhasil Disimpan', $user);
        }

        return new UserResource(false, 'Data Gagal Disimpan', null);
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::whereId($id)->first();

        if($user) {
            return new UserResource(true, 'Detail Data User!', $user);
        }

        return new UserResource(false. 'Detail data user gagal ditampilkan', null);
    }

    /**
     * Updating Data
     */

    public function update(Request $request, User $user)
    {
        $validator = Validator::make(request()->all(), [
           'name' => 'required',
           'email' => 'required|unique:users,email,'.$user->id,
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->password == "") {
            // update user without password
            $user->update([
               'name' => $request->name,
               'email' => $request->email,
            ]);
        }

        // update user with password
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        if($user) {
            // return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        }

        // return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Diupdate!', null);
    }

    /**
     * Deleting Data
     */
    public function destroy(User $user)
    {
        if($user->delete()) {
            // return success with Api Resource
            return new UserResource(true, 'Data Berhasil Dihapus', null);
        }

        // return success with Api Resource
        return new UserResource(false, 'Data Gagal Dihapus', null);
    }
}


