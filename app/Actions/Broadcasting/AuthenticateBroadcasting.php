<?php

declare(strict_types=1);

namespace App\Actions\Broadcasting;

use App\Actions\Broadcasting\TrackConnection;
use App\Data\Requests\AuthenticateBroadcastingRequest;
use App\Data\Response\AuthenticateBroadcastingResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class AuthenticateBroadcasting
{
    /**
     * Authenticate a broadcasting channel and track the connection
     */
    public function __invoke(
        AuthenticateBroadcastingRequest $data,
        Request $request,
    ): JsonResponse {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                '_tag' => 'AuthenticationError',
                'message' => 'Unauthorized',
            ], 401);
        }

        // Authenticate the channel
        try {
            $authData = Broadcast::auth($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Channel authentication failed',
            ], 403);
        }

        // Track the connection
        $tracker = new TrackConnection;
        $tracker->connect(
            $data->socket_id,
            $user->id,
            $data->channel_name,
            $request,
        );

        return response()->json(AuthenticateBroadcastingResponse::from([
            'auth' => is_array($authData) && isset($authData['auth'])
                ? $authData['auth']
                : '',
        ]));
    }
}
