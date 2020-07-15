<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterFromRequest;
use App\Tokens;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class AuthController extends Controller
{
    //
    public function register(RegisterFromRequest $request)
    {
        /**
         * gender: [0 - Other, 1 - Male, 2 - Female]
         * user_type: [11 - Normal User, 21 - Coach]
         */
        $params = $request->only('email', 'username', 'password', 'user_type', 'gender');
        $user = new User();
        $user->email = $params['email'];
        $user->username = $params['username'];
        $user->gender = $params['gender'];
        $user->user_type = $params['user_type'];
        $user->password = bcrypt($params['password']);
        $user->save();

        return response()->json(
            ['message' => 'Register Success, You are logged in!',
            'code' => 200,
            'data' => []
            ], Response::HTTP_OK);
    }

    public function login(Request $request)
    {
        $username = $this->findUsername($request);
        $params = $request->only($username, 'device_id', 'os');
        $credentials = $request->only($username, 'password');
        $device_id = $params['device_id'];
        $os        = $params['os'];
        $token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addMinutes(1)->timestamp]);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'error' => 'invalid.credentials',
                'msg' => 'Invalid Credentials.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $login = $request->get($username);

        $user = User::query()->where($username, '=', $login)->first();

        if (Tokens::where('user_id', '=', $user->id)->count())
        {
            Tokens::where('user_id', '=', $user->id)->update(['token' => $token]);
        } else
        {
            $user->tokens()->create([
                'token' => $token,
                'device_id' => $device_id,
                'os' => $os,
            ]);
        }

        return response()->json(['code' => 200, 'data' =>['user' => $user, 'token' => $token]], Response::HTTP_OK);
    }

    public function user(Request $request)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e){
            return response()->json(['message' => 'Token Expired'], 401);
        }

        $user = Auth::user();

        if ($user) {
            return response($user, Response::HTTP_OK);
        }

        return response(null, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     *
     * @param Request $request
     */
    public function logout(Request $request) {
        $this->validate($request, ['token' => 'required']);

        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json('You have successfully logged out.', Response::HTTP_OK);
        } catch (JWTException $e) {
            return response()->json('Failed to logout, please try again.', Response::HTTP_BAD_REQUEST);
        }
    }

    public function refresh()
    {
        return response(JWTAuth::getToken(), Response::HTTP_OK);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername($request)
    {
        $login = $request->input('login');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}
