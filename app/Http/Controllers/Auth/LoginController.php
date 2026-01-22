<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User; 

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('employee_id', $credentials['employee_id'])->first();

        if (!$user || $user->password !== $credentials['password']) {
             throw ValidationException::withMessages([
                 'employee_id' => trans('auth.failed'),
             ]);
        }
            
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        return redirect()->intended(route('employees.list'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}