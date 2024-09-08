<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordPage extends Component
{
    public $token;
    #[Url()] //mendapatkan url, contoh: ?email=
    public $email;
    public $password_confirmation;
    public $password;

    public function mount($token)
    {
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token
                // Data input (email, password, password_confirmation, token) dikirimkan ke mekanisme reset password bawaan Laravel.
            ],

            // Jika reset berhasil, callback function akan dieksekusi, di mana password pengguna akan di-hash (Hash::make) dan token "remember me" yang baru di-generate (Str::random(60)).
            function (User $user, string $password) {
                $password = $this->password;
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );
        
        //Jika password berhasil di-reset ($status === Password::PASSWORD_RESET), pengguna akan diarahkan ke halaman login.
        // jika masuk dengan token yang sama, akan muncul session error
        return $status === Password::PASSWORD_RESET ? redirect('/login') : session()->flash('error', 'Something went wrong.');  
    }
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
