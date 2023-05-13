<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy', 'create', 'update']);
    }
    public function get(Request $request)
    {
        $query = User::select('*');
        if ($request->has('search')) {
            $query->where('name', '=', '%' . $request->input('search') . '%');
        }
        $data = $query->paginate(10);
        return $data;

    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response(401);
        }
        $user->tokens()->delete();
        $token = $user->createToken($request->input('email'))->plainTextToken;
        header('Content-Type: application/json');
        return response()->json([
            'user' => $user,
            'role' => $user->role,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function create(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'required',
            'role' => 'required',
        ]);
        $users = User::where('email', '=', $request->input('email'))->first();
        if ($users !== null) {
            return response('البريد الالكتروني مأخوذ سابقا', 409);
        }

        if (!Gate::allows('is-super')) {

            return response('انت غير مخول  ', 403);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken($request->input('email'))->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'email' => 'required|email',
            'name' => 'required',
            'role' => 'required',
        ]);
        $users = User::where('email', '=', "$request->email")->first();
        if ($users !== null && $users->email != $request->email) {
            return response('البريد الالكتروني مأخوذ سابقا', 409);
        }
        $users = User::select('*')->where('id', '=', "$request->id")->first();
        $users->name = $request->name;
        $users->email = $request->email;
        $users->role = $request->role;
        $users->save();
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response('تم حذف المستخدم بنجاح', 200);
    }

}
