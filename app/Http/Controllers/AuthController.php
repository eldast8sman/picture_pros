<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;
use App\Mail\Mailings;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UserActivationRequest;

class AuthController extends Controller
{
    public function index(){
        $users = User::orderBy('name', 'asc');
        if($users->count() > 0){
            return response([
                'status' => 'success',
                'message' => 'Users fetched successfully',
                'data' => $users->get()
            ]);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No User fetched',
                'data' => []
            ], 404);
        }
    }

    public function store_admin(Request $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'auth_level' => 1
        ]);

        return response([
            'status' => 'success',
            'message' => 'User Added',
            'data' => $user
        ]);
    }

    public function store(StoreUserRequest $request){
        if($this->user()->auth_level == 1){
            $all = $request->all();
            $all['status'] = 0;
            if($user = User::create($all)){
                $token = base64_encode($user->id."PicturePros".Str::random(20));
                $user->verification_token = $token;
                $user->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
                $user->save();

                Mail::to($user)->send(new Mailings('Invitation to be an Admin on PicturePros', 'emails.invite_admin', [
                    'title' => 'Invitiation to be an Admin',
                    'name' => $user->name,
                    'link' => env('FRONTEND_URL', 'https://picturepros.com').'/admin/activate/'.$token
                ]));
                
                return response([
                    'status' => 'success',
                    'message' => 'Admin added successfully',
                    'data' => $user
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Admin was not added'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Not Authorized to add new User'
            ], 409);
        }
    }

    public function resend_verification_link($id){
        if($this->user()->auth_level == 1){
            if(!empty($user = User::find($id))){
                $token = base64_encode($user->id."PicturePros".Str::random(20));
                $user->verification_token = $token;
                $user->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
                $user->save();
    
                Mail::to($user)->send(new Mailings('Invitation to be an Admin on PicturePros', 'emails.invite_admin', [
                    'title' => 'Invitiation to be an Admin',
                    'name' => $user->name,
                    'link' => env('FRONTEND_URL', 'https://picturepros.com').'/admin/activate/'.$token
                ]));
                
                return response([
                    'status' => 'success',
                    'message' => 'Invitation/Activation Link sent successfully'
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No User was fetched'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Not Authorized to carry out this Action'
            ], 409);
        }
    }

    public function show($id){
        $user = User::find($id);
        if(!empty($user)){
            return response([
                'status' => 'success',
                'message' => 'User fetched successfully',
                'data' => $user
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Admin found',
                'data' => []
            ], 404);
        }
    }

    public function update(UpdateUserRequest $request, $id){
        if(!empty($user = User::find($id))){
            if(($this->user()->auth_level == 1) || $user->id == $this->user()->id){
                $data = $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|email|string'
                ]);
    
                $user->update($data);
    
                return response([
                    'status' => 'success',
                    'message' => 'User Edited Successfully',
                    'data' => $user
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Not Authorized'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No User was fetched'
            ], 404);
        }
    }

    public function user(){
        $user = auth()->user();
        return $user;
    }

    public function me(){
        if(!empty($this->user())){
            return response([
                'status' => 'success',
                'message' => 'User details fetched',
                'data' => $this->user()
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No User fetched'
            ], 404);
        }
    }

    public function fetch_by_verification_token($token){
        $user = User::where('verification_token', $token)->first();
        if(!empty($user)){
            $current_time = date('Y-m-d H:i:s');
            if(($user->verification_token_expiry >= $current_time) && ($user->status == 0)){
                return response([
                    'status' => 'failed',
                    'message' => 'User fetched successfully',
                    'data' => $user
                ], 200);
            } else {
                $user->verification_token = null;
                $user->verification_token_expiry = null;
                $user->save();
                return response([
                    'status' => 'failed',
                    'message' => 'Expired Link'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No User was fetched'
            ], 404);
        }
    }

    private function login_function($email, $password){
        $auth = [
            'email' => $email,
            'password' => $password
        ];

        if(!empty($user = User::where('email'))){
            if(auth()->attempt($auth)){
                $user = auth()->user();
                $token = $user->createToken('main')->plainTextToken;
                $user->authorization = [
                    'type' => 'Bearer',
                    'token' => $token
                ];

                return $user;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function activate(UserActivationRequest $request){
        $user = User::where('verification_token', $request->verification_token)->first();
        if(($user->verification_token_expiry >= date('Y-m-d H:i:s')) && ($user->status == 0)){
            $user->password = bcrypt($request->password);
            $user->status = 1;
            $user->verification_token = null;
            $user->verification_token_expiry = null;
            $user->save();

            $login = $this->login_function($user->email, $request->password);
            return response([
                'status' => 'success',
                'message' => 'User activated and logged in',
                'data' => $login
            ], 200);
        } else {
            $user->verification_token = null;
            $user->verification_token_expiry = null;
            $user->save();
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 409);
        }
    }

    public function login(LoginRequest $request){
        if($user = $this->login_function($request->email, $request->password)){
            return response([
                'status' => 'success',
                'message' => 'Login successfully',
                'data' => $user
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Wrong Login Credentials'
            ], 409);
        }
    }

    public function recover_password($email){
        $user = User::where('email', $email)->first();
        if(!empty($user)){
            $user->token = md5("PicturePros".$user->id.time().Str::random(30));
            $user->token_expiry = date('Y-m-d H:i:s', time() + (60 *10));
            $user->save();
            Mail::to($user)->send(new Mailings('Password Reset Link', 'emails.reset_password', [
                'title' => 'Reset your Password',
                'name' => $user->name,
                'link' => env('FRONTEND_URL', 'http://localhost:3000').'/password-reset/'.$user->token
            ]));

            return response([
                'status' => 'success',
                'message' => 'Password Reset Link sent to '.$user->email
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No User was fetched'
            ], 404);
        }
    }

    public function reset_password(ResetPasswordRequest $request){
        $user = User::where('token', $request->token)->first();
        if($user->token_expiry >= date('Y-m-d H:i:s')){
            $user->password = bcrypt($request->password);
            $user->token = null;
            $user->token_expiry = null;
            $user->save();

            return response([
                'status' => 'success',
                'message' => 'Password reset successfully. You can now login with your credentials'
            ], 200);
        } else {
            $user->token = null;
            $user->token_expiry = null;
            $user->save();
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 404);
        }
    }

    public function change_password(ChangePasswordRequest $request){
        $user = $this->user();

        if($user = $this->login_function($user->email, $request->old_password)){
            $user = User::find($user->id);
            $user->password = bcrypt($request->password);
            $user->save();

            $user = $this->login_function($user->email, $user->password);
            return response([
                'status' => 'success',
                'message' => 'Password changed successfully',
                'data' => $user
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Wrong Password'
            ], 409);
        }
    }

    public function destroy($id){
        if($this->user()->auth_level == 1){
            if(!empty($user = User::find($id))){
                $user->delete();

                return response([
                    'status' => 'success',
                    'message' => 'User successfully deleted',
                    'data' => $user
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'User not fetched'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'You are not authorised to carry out this Action'
            ], 409);
        }
    }
}
