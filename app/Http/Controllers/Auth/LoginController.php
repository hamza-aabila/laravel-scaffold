<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Socialite;
use Auth;
use App\User;

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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request)
    {
        if (!$request->has('code') || $request->has('denied')) {
          return redirect('/');
        }

        $socialUser = Socialite::driver('facebook')->stateless()->user();

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            return $this->authAndRedirect($user);
        } else {
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail() ?? $socialUser->getId().'@facebook.com',
                'avatar' => $socialUser->getAvatar(),
                'password' => Hash::make($socialUser->getId())
            ]);

            $user->assignRole('user');

            return $this->authAndRedirect($user);
        }

    }

    public function authAndRedirect($user)
    {
        Auth::login($user);

        return redirect()->to('/');
    }
}
