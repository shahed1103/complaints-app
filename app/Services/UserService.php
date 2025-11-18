<?php


namespace App\Services;

use App\Models\User;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UserSigninRequest;
use App\Http\Requests\Auth\UserSignupRequest;
use App\Http\Requests\Auth\UserForgotPasswordRequest;
use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Storage;
use Illuminate\Support\Facades\File;


class UserService
{

    public function register($request): array{
        $clientRole = Role::query()->firstWhere('name', 'Client')->id;

         $sourcePath = 'uploads/seeder_photos/defualtProfilePhoto.png';
         $targetPath = 'uploads/det/defualtProfilePhoto.png';

        Storage::disk('public')->put($targetPath, File::get($sourcePath));
        if (empty($request['phone']) && empty($request['email'])) {
            throw new Exception("You must enter phone number or email.", 401 );
        }

        $otp = rand(100000, 999999);

        $user = User::query()->create([
            'role_id' =>  $clientRole,
            'name' => $request['name'],
            'email' => $request['email'] ?? null,
            'password' => Hash::make($request['password']),
            'phone' => $request['phone'] ?? null,
            'photo' => url(Storage::url($targetPath)),
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        if ($user->phone) {
            // $this->sendSmsOtp($user->phone, $otp);
        } else {
            // Mail::to($request['email'])->send($otp);
                    Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
            $message->to($request['email'])->subject('OTP Verification');
        });
        }

        $clientRole = Role::query()->where('name', 'Client')->first();
        $user->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);

        $user->load('roles' , 'permissions');

        $user = User::query()->find($user['id']);

        $user = $this->appendRolesAndPermissions($user);
        // $user['token'] = $user->createToken("token")->plainTextToken;

        $message = 'User created successfully';

        return ['user' => $Permission , 'message' => $message];
    }


    public function signin($request): array{
     $user = User::query()->where('email',$request['email'])->first();
     if (!is_null($user)){
        if(!Auth::attempt($request->only(['email' , 'password']))){
        throw new Exception("User email & password does not with our record.", 401 );
        }

        else {
            $user = $this->appendRolesAndPermissions($user);
            $user['token'] = $user->createToken("token")->plainTextToken;
            $message = 'User logged in successfully';
            $code = 200;
        }
     }
     else {
        throw new Exception("User not found.",  404);
     }

     return ['user' => $Permission , 'message' => $message , 'code' => $code];
    }

    public function logout(): array{
        $user = Auth::user();
        if(!is_null(Auth::user())){
            Auth::user()->currentAccessToken()->delete();
            $message = 'User logged out successfully';
            $code = 200;
        }

        else{
            throw new Exception("invalid token.", 404);
        }

        return ['user' => $user , 'message' => $message , 'code' => $code];}


    
     public function forgotPassword($request): array{

              //Delete all old code user send before
              ResetCodePassword::query()->where('email' , $request['email'])->delete();
               $data['email'] =  $request['email'];
              //generate random code
                $data['code'] = mt_rand(100000, 999999);

                $data['role'] = User::query()->firstWhere('email' , $request['email'])->role_id;
                //Create a new code
                $codeData = ResetCodePassword::query()->create($data);

                //Send email to user
                Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));

                $message = 'code sent';
                $code = 200;
            return ['user' => $data , 'message' => $message , 'code' => $code];}

    public function checkCode($request): array{
        //find the code
               $passwordReset = ResetCodePassword::query()->firstWhere('code' , $request['code']);

               $user = User::where('email' , $passwordReset->email)->first();

      //  check if it is not expired: the time is one hour
                if($passwordReset['created_at'] > now()->addHour()){
                    $passwordReset->delete();
                    $message = 'code_is_expire';
                    $code = 422;
                    return ['verifyCode' => 'expire', 'message' => $message , 'code' => $code];
                }

                $verifyCode['token'] = $user->createToken("token")->plainTextToken;

                $verifyCode['code'] = $passwordReset['code'];

               $message = 'code_is_valid';
               $code = 200;

                return ['verifyCode' => $verifyCode , 'message' => $message , 'code' => $code];
            }

            public function resetPassword($request , $codeR) : array{
                //find the code
                $passwordReset = ResetCodePassword::query()->firstWhere('code' , $codeR);
                // check if it is not expired: the time is one hour
                if($passwordReset['created_at'] > now()->addHour()){
                   $passwordReset->delete();

                   $message = 'code_is_expire';
                   $code = 422;

                   return ['', 'message' => $message , 'code' => $code];
                }

                //find user's email
                     $user = User::query()->firstWhere('email' , $passwordReset['email']);

                //update user password
                     $request['password'] = bcrypt($request['password']);

                     $user->update([
                        'password' =>  $request['password']
                     ]);

                     $data['token'] = $user->createToken("token")->plainTextToken;
                     $data['role'] = $user->role_id;

                     $message = 'password has been successfully reset';
                     $code = 200;

                //delete current code
                $passwordReset->delete;

              return ['role' =>  $data ,'message' => $message  , 'code' => $code];
                    }

    private function appendRolesAndPermissions($user){
           $roles = [];

           foreach ($user->roles as $role){
            $roles[] = $role->name;
           }

           unset($user['roles']);

           $user['roles']= $role;

           $permissions = [];
           foreach ($user->permissions as $permission){
            $permissions[] = $permission->name;
           }
           $user['permissions']= $permission;

           return $user; }

}
