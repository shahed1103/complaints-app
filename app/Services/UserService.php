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
    private function sendWhatsappOtpWithUltraMsg($phone , $otp){
        $instance = env('ULTRA_MSG_INSTANCE');
        $token = env('ULTRA_MSG_TOKEN');

        $url = "https://api.ultramsg.com/instance151958/messages/chat";

        $client = new \GuzzleHttp\Client();

        $client->post($url, [
            'form_params' => [
                'token' => $token,
                'to' => $phone,          
                'body' => "رمز التحقق الخاص بك هو: $otp",
            ]
        ]);
    }

    public function register($request): array{
        $clientRole = Role::query()->firstWhere('name', 'Client')->id;

         $sourcePath = 'uploads/seeder_photos/defualtProfilePhoto.png';
         $targetPath = 'uploads/det/defualtProfilePhoto.png';

        Storage::disk('public')->put($targetPath, File::get($sourcePath));

        if (filter_var($request['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                $request->validate(['emailOrPhone' => 'email|unique:users,email']);
                $email = $request['emailOrPhone'];
                $phone = null;
        } elseif (preg_match('/^\+963[0-9]{9}$/', $request['emailOrPhone'])) {
                $request->validate(['emailOrPhone' => 'unique:users,phone']);
                $phone = $request['emailOrPhone'];
                $email = null;
        } else {
        throw new Exception("الرجاء إدخال بريد إلكتروني أو رقم هاتف صحيح", 422 );
        }

        $otp = rand(100000, 999999);

        $user = User::query()->create([
            'role_id' =>  $clientRole,
            'name' => $request['name'],
            'email' => $email ,
            'password' => Hash::make($request['password']),
            'phone' =>  $phone,
            'photo' => url(Storage::url($targetPath)),
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
            'is_verified' => false
        ]);

        if ($phone) {
            $this->sendWhatsappOtpWithUltraMsg($phone, $otp);
            
        } else {
            Mail::raw("Your OTP code is: $otp", function ($message) use ($email) {
                $message->to($email)->subject('OTP Verification');
        });
        }

        $clientRole = Role::query()->where('name', 'Client')->first();
        $user->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);

        $user->load('roles' , 'permissions');

        $user = User::query()->find($user['id']);

        $user = $this->appendRolesAndPermissions($user);

        $message = 'The Otp code send to you please check it ';

        return ['user' => $user , 'message' => $message];
    }

    public function checkOtpCode($request , $userID): array{

            $user = User::find($userID);

            if ($user->otp_code != $request->otp_code) {
                    throw new Exception("رمز التحقق غير صحيح", 422 );
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                    throw new Exception("انتهت صلاحية الرمز", 422 );
            }

            $user->update([
                'is_verified' => true,
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

               $message = 'تم تفعيل الحساب بنجاح';
               $code = 200;
               $user['token'] = $user->createToken("token")->plainTextToken;


                return ['verifyCode' => $user['token'] , 'message' => $message , 'code' => $code];
    }
    
    public function signin($request): array{

        if (filter_var($request['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                $user = User::query()->where('email',$request['emailOrPhone'])->first();

        } else{
                $user = User::query()->where('phone',$request['emailOrPhone'])->first();
        }

        if (is_null($user)) {
            throw new Exception("User not found.", 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw new Exception("User information does not with our record.", 401);
        }

        if (!$user->is_verified) {
            throw new Exception("يجب تفعيل الحساب عبر رمز التحقق قبل تسجيل الدخول.", 403);
        }

            $user = $this->appendRolesAndPermissions($user);
            $user['token'] = $user->createToken("token")->plainTextToken;
            $code = 200;
            $message = 'User logged in successfully';
 
     return ['user' => $user , 'message' => $message , 'code' => $code];
    }

    public function resendOtp($userId){
        // if (filter_var($request['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
        //     $user = User::where('email', $request['emailOrPhone'])->first();
        // } else{
        //     $user = User::where('phone', $request['emailOrPhone'])->first();
        // }

        // if (is_null($user)) {
        //     throw new Exception("هذا الحساب غير موجود.", 404);
        // }

        $user = User::find($userId);

        if ($user->is_verified) {
            throw new Exception("الحساب مفعّل مسبقًا، لا حاجة لإعادة إرسال رمز التحقق.", 400);
        }

        $otp = rand(100000, 999999);
        $user->update([ 'otp_code' => $otp , 
                        'otp_expires_at' => now()->addMinutes(5) , 
                        'is_verified' => false 
                    ]);

        if ($user->phone) {
            $this->sendWhatsappOtpWithUltraMsg($user->phone, $otp);
        } else {
            Mail::raw("رمز التحقق الخاص بك هو: $otp", function ($message) use ($user) {
                $message->to($user->email)->subject("OTP Verification");
            });
        }

        $message = 'تم إرسال رمز تحقق جديد بنجاح.';
        $code = 200;

        return ['user' => $user , 'message' => $message , 'code' => $code];
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

        return ['user' => $user , 'message' => $message , 'code' => $code];
    }
 
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
            return ['user' => $data , 'message' => $message , 'code' => $code];
    }

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

           return $user; 
    }

}
