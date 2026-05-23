<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneOtp;
use App\Services\WhatsAppSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        if ($user->phone_verified_at) {
            return redirect()->intended(route('home'));
        }

        return view('auth.verify-phone', ['user' => $user]);
    }

    /**
     * Generate a new OTP and send it via WhatsApp.
     * Throttled: max 1 OTP per minute, 5 per hour.
     */
    public function send(Request $request, WhatsAppSender $wa): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        if (! $user->phone) {
            return back()->with('flash_error', 'لا يوجد رقم تليفون مرتبط بحسابك.');
        }
        if ($user->phone_verified_at) {
            return redirect()->intended(route('home'));
        }

        $recent = PhoneOtp::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinute())
            ->exists();
        if ($recent) {
            return back()->with('flash_warn', 'استنّى دقيقة وحاول تاني.');
        }

        $hourlyCount = PhoneOtp::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        if ($hourlyCount >= 5) {
            return back()->with('flash_error', 'تجاوزت الحد المسموح. حاول بعد ساعة.');
        }

        $code = $wa->generateOtp(5);
        $otp = PhoneOtp::create([
            'user_id'    => $user->id,
            'phone'      => $user->phone,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $msg = "بنهاوي · كود التحقق:\n*{$code}*\n\nالكود ساري لمدة 10 دقايق. لو مش انت اللي طلبت، تجاهل الرسالة.";
        $sent = $wa->send($user->phone, $msg);

        if (! $sent) {
            if (! config('services.waapi.enabled') || app()->environment('local')) {
                // Dev-friendly: show the code in the flash so testing is possible
                return back()->with('flash_warn', "تعذّر إرسال الرسالة. كود التحقق (وضع التطوير): $code");
            }
            return back()->with('flash_error', 'تعذّر إرسال كود التحقق. حاول تاني.');
        }

        return back()->with('flash', 'تم إرسال كود التحقق عبر واتساب ✓');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $data = $request->validate([
            'code' => 'required|string|min:4|max:10',
        ]);
        $code = preg_replace('/\D/', '', $data['code']);

        $otp = PhoneOtp::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages(['code' => 'لا يوجد كود نشط. اضغط "إعادة إرسال".']);
        }

        if ($otp->isExpired()) {
            throw ValidationException::withMessages(['code' => 'الكود انتهت صلاحيته. اطلب كود جديد.']);
        }

        if ($otp->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'محاولات كتيرة. اطلب كود جديد.']);
        }

        $otp->increment('attempts');

        if ($otp->code !== $code) {
            throw ValidationException::withMessages(['code' => 'الكود مش صحيح.']);
        }

        $otp->update(['verified_at' => now()]);
        $user->update(['phone_verified_at' => now()]);

        return redirect()->intended(route('home'))->with('flash', 'تم تأكيد رقمك ✓');
    }
}
