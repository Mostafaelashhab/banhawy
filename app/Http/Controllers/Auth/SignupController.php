<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneOtp;
use App\Models\User;
use App\Services\WhatsAppSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SignupController extends Controller
{
    public function show()
    {
        return view('auth.signup');
    }

    public function store(Request $request, WhatsAppSender $wa)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => Str::slug($data['name']) . '-' . Str::random(6) . '@banhawy.local',
            'phone'    => $data['phone'],
            'role'     => 'visitor',
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Fire off an OTP via WhatsApp
        $this->sendInitialOtp($user, $wa);

        return redirect()->route('phone.show')
            ->with('flash', 'أهلًا بك ' . explode(' ', $user->name)[0] . ' 👋  بعتنالك كود تحقق على واتساب.');
    }

    private function sendInitialOtp(User $user, WhatsAppSender $wa): void
    {
        $code = $wa->generateOtp(5);
        PhoneOtp::create([
            'user_id'    => $user->id,
            'phone'      => $user->phone,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $msg = "بنهاوي · كود التحقق:\n*{$code}*\n\nالكود ساري لمدة 10 دقايق.";
        $wa->send($user->phone, $msg);
    }
}
