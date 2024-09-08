<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class RegisterPage extends Component
{

    public $name;
    public $email;
    public $password;
    // register user
public function save()
{


    $this->validate([
    'name' => 'required|max: 255',
    'email' => 'required|email|unique:users|max: 255',
    'password' => 'required|min: 6 | max:255',
    ]);
    // save to database
    $user = User::create([
    'name' => $this->name,
    'email' => $this->email,
    'password' => Hash::make($this->password),
    ]);
    
    // login
    auth()->login($user);
    return redirect('/');
}   
    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
