<?php

namespace App\Http\Controllers\Api\Overlay;

use App\Http\Controllers\Controller;
use App\Http\Resources\Donate\DonateResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OverlayController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/overlay/donate",
     * operationId="donateOverlay",
     * tags={"Overlay"},
     * summary="Get streamer donates",
     * @OA\Parameter(name="key",in="query",description="sdfsdfdfdf",required=true),
     * @OA\Response(
     *    response=200,
     *    description="Your request has been successfully completed.",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="bool", example="true"),
     *       @OA\Property(property="message", type="string", example="Your request has been successfully completed."),
     *       @OA\Property(property="data"),
     *        )
     *     ),
     * )
     */
    public function donate(Request $request)
    {
        $streamer = User::where('uuid', $request->key)->first();
        if (!$streamer) {
            abort(404, 'Invalid key');
        }
        $response = new StreamedResponse(function () use ($streamer){
            while (true) {
                if (Cache::has("donate-$streamer->username")) {
                    $data = Cache::pull("donate-$streamer->username");
                    echo "data: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();
                }
                sleep(1);
            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }

    /**
     * @OA\Get(
     * path="/api/overlay/donates",
     * operationId="getLastDonates",
     * tags={"Overlay"},
     * summary="Get last donates",
     * security={ {"sanctum": {} }},
     * @OA\Parameter(name="key",in="query",description="sdfsdfdfdf",required=true),
     * @OA\Response(
     *    response=200,
     *    description="Your request has been successfully completed.",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="bool", example="true"),
     *       @OA\Property(property="message", type="string", example="Your request has been successfully completed."),
     *       @OA\Property(property="data"),
     *        )
     *     ),
     * )
     */
    public function donates(Request $request)
    {
        $streamer = User::where('uuid', $request->key)->first();
        if (!$streamer) {
            abort(404, 'Invalid key');
        }
        $donates = $streamer->transactionsReceived()->where('is_paid', 1)->where('type', 'donate')->orderBy('id','desc')->paginate(10);
        return sendResponse('Donate that I paid', [
            'donates' => DonateResource::collection($donates),
            'pagination' => paginateResponse($donates),
        ]);
    }
}
