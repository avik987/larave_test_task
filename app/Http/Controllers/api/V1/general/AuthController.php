<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class AuthController extends Controller
{

    public function registration(UserRequest $request)
    {
        try {
            $newUser = User::create([
                "id" => $request['user_id'],
                'name' => $request['name'],
                'login' => $request['login'],
                'password' => Hash::make($request['password']),
            ]);
            Auth::login($newUser);
            $token = $newUser->createToken($request["login"], ['server:update']);
            $newUser["api_token"] = $token->plainTextToken;
            $data = [
                'success' => true,
                'data' => $newUser,
            ];
            return
                response($data)->setStatusCode(200)->header('Status-Code', '200');

        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', $e->getCode()));
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->where('id', Auth::id())->delete();
        $user = Auth()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json(['success' => true,
            'message' => 'The user successfully logged out'])->header('Status-Code', '200');
    }


    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), ['login' => 'required', 'password' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ],)->header('Status-Code', 200);
        }
        $validUser = Auth::attempt(['login' => $request["login"], 'password' => $request["password"]]);
        if ($validUser) {
            $user = Auth::getProvider()->retrieveByCredentials(['login' => $request["login"], 'password' => $request["password"]]);
            Auth::login($user);
            $token = $user->createToken($request["login"], ['server:update']);
            $user["api_token"] = $token->plainTextToken;
            $data = [
                'success' => true,
                'data' => $user,
            ];
            return response()->json($data)
                ->setStatusCode(200)->header('Status-Code', '200');

        } else {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => "Invalid username or password",
            ], 401)->header('Status-Code', '401'));
        }

    }


    public function updateUserData(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'min:5',
            'login' => 'min:5',
            'user_id' => 'uuid',
        ]);
        if (!empty($validated->errors()->messages())) {
            return response()->json(['messages' => $validated->errors()->messages()], 200);
        }
        $user = User::where('id', Auth::id())->first();
        $user->name = $request->name ?? $user->name;
        $user->login = $request->login ?? $user->login;
        if (!empty($request->current_password) && !empty($request->new_password)) {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Current password is wrong",
                ]);
            }
        }
        $user->save();
        return response()->json(['success' => true, 'message' => ["User data successfully updated!"]]);
    }

    public function deleteUser()
    {
        $user = User::query()->find(auth()->id());
        if ($user) {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => "User deleted",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "User not found",
            ]);
        }
    }

    public function getUsers(Request $request)
    {
        $limit = $request["limit"] ?? 50;
        $users = User::with("locations")->paginate($limit);
        return response()->json([
            'success' => true,
            'data' => new UserResource($users),
        ]);

    }

    public function getUser($id)
    {
        $user = User::with("locations")->find($id);
        if ($user) {
            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "User not found",
            ]);
        }
    }
}
