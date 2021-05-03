<?php

namespace App\Http\Controllers;

use App\Classes\AgoraDynamicKey\RtcTokenBuilder;
use App\Events\MakeVideoCall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    /**
     * Get a token.
     */
    public function token(Request $request)
    {
        $appID = config('agora.app_id');
        $appCertificate = config('agora.app_certificate');
        $expireTimeInSeconds = config('agora.expire_time');

        return RtcTokenBuilder::buildTokenWithUserAccount(
            $appID,
            $appCertificate,
            $request->channelName,
            Auth::user()->name,
            RtcTokenBuilder::RoleAttendee,
            now()->getTimestamp() + $expireTimeInSeconds
        );
    }

    /**
     * Call a user.
     */
    public function call(Request $request)
    {
        $videoCallEvent = new MakeVideoCall([
            'userToCall' => $request->user_to_call,
            'channelName' => $request->channel_name,
            'from' => Auth::id()
        ]);

        broadcast($videoCallEvent)->toOthers();
    }
}
