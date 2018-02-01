<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Socialite;
use App\User;
use App\Profile;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */


    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }



    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $facebook = Socialite::driver('facebook')->user();
        // dd($facebook);
        $isUser = User::where('fb_id', $facebook->id)->first();
        // 15 str_replace('@facebook.com', '', )
        // dd($isUser);
        if($isUser) {
            Auth::loginUsingId($isUser->id);
        } else {
            //建立User
            $user = new User();
            $email = $facebook->getEmail() ? $facebook->getEmail() : $facebook->getId() .'@facebook.com';
            $user->fb_id = $facebook->getId();
            $user->email = $email;
            $user->name = $facebook->getName();
            $user->save();

            //建立Profile
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->avator = $facebook->getAvatar();
            $profile->save();

            Auth::login($user);
        }
        
            // +name: "Asher Jay"
            // +email: null
            // +avatar: "https://graph.facebook.com/v2.10/110398259776729/picture?type=normal"
        return redirect('/home');
    }
}
