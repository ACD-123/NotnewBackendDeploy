<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Notifications\Welcome;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws AuthorizationException
     */
    public function verifyRegisterUser(Request $request)
    {
        $user = User::where('id', $request->route('id'))->firstOrFail();

        if (!hash_equals((string)$request->route('id'), (string)$user->getKey())) {
            throw new AuthorizationException;
        }
        $envKey = env('APP_KEY');
        if (!hash_equals((string)$request->route('hash'), sha1($user->getEmailForVerification() . $envKey))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            throw new ConflictHttpException("Already Verified");
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $user->notify(new Welcome($user));
        }

        if ($response = $this->verified($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect($this->redirectPath())->with('verified', true);
    }

    protected function verified(Request $request)
    {
        return $this->genericResponse(true, "Your email has been verified");
    }
}
