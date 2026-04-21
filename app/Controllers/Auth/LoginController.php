<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    /**
     * Display the login form.
     */
    public function loginView()
    {
        if (auth()->loggedIn()) {
            return $this->redirectUser();
        }

        helper('theme');

        return view('auth/login');
    }

    /**
     * Handle the login submission.
     */
    public function loginAction()
    {
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        // Attempt to login
        $result = auth()->attempt($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->withInput()->with('error', $result->reason());
        }

        return $this->redirectUser();
    }

    /**
     * Log the user out.
     */
    public function logout()
    {
        auth()->logout();

        return redirect()->to(config('Auth')->logoutRedirect());
    }

    /**
     * Helper to redirect user based on role.
     */
    protected function redirectUser()
    {
        $user = auth()->user();

        if ($user->inGroup('superadmin')) {
            return redirect()->to('/superadmin/dashboard');
        }

        return redirect()->to('/dashboard');
    }
}
