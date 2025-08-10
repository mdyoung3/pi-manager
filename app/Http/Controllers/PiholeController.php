<?php

namespace App\Http\Controllers;

use App\Models\PiholeUrl;
use App\Models\BlockedUrl;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PiholeController extends Controller
{
    protected $piholeAddress;

    protected $piholeSid;

    public function __construct(PiholeUrl $piholeAddress)
    {
        $this->piholeAddress = config('pihole.address', 'pi.hole');
        $this->piholeSid = $this->getPiholeSid();
    }

    private function getPiholeSid(): mixed {
        $sessionData = session('pihole_sid');

        if($sessionData) {
            self::testSid();
            if(time() - $sessionData['created_at'] > 1800) {
                return self::storeSid();
            } else {
                return $sessionData['sid'];
            }
        } else {
            return self::getNewPiholeSid();
        }
    }

    private function testSid()
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                "Content-Type" => "application/json"
            ])->withBody(json_encode([
                'sid' => $this->piholeSid,
            ]))->post("https://{$this->piholeAddress}/api/history");

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['error']) && $responseData['error']['key'] === 'unauthorized') {
                    throw new \Exception('Unauthorized access. Please check your API Key');
                }
            } else {
                throw new \Exception('HTTP request failed');
            }

        } catch (\Exception $e) {
            // Log the error message, you can also return the exception message
            Log::error($e->getMessage());
        }
    }
    function getNewPiholeSid(): string {
        $piholeSid = self::getSid();
        $created_at = time(); // Get current Unix timestamp
        $sessionData = ['sid' => $piholeSid, 'created_at' => $created_at];

        // Storing it in session
        session(['pihole_sid' => $sessionData]);

        return $sessionData['sid'];
    }

    private function getSid(): string
    {
        $piholePassword = config('pihole.password');

        $response = Http::withoutVerifying()->withHeaders([
            "Content-Type" => "application/json"
        ])->withBody(json_encode([
            'disable' => 300,
            'password' => $piholePassword]), 'application/json'
        )->post("https://{$this->piholeAddress}/api/auth");

        return $response->json()['session']['sid'];
    }

    private function storeSid()
    {
        $response = self::getSid();
        $sessionData = $response->json();
        $piholeSid = $sessionData['session']['sid'];
        $creationTime = time(); // Get current Unix timestamp
        session(['pihole_sid' => [ 'sid' => $piholeSid, 'created_at' => $creationTime]]);
        return $piholeSid;
    }

    public function disablePihole() {
        $blocker = Http::withoutVerifying()->withHeaders([
            "Content-Type" => "application/json"
        ])->withBody(json_encode([
                'blocking' => false,
                'timer' => 360,
                'sid' => $this->piholeSid,])
        )->post("https://{$this->piholeAddress}/api/dns/blocking");

        return $blocker->json([$blocker]);
    }

    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');

            // Check if URL is blocked
            $isBlocked = BlockedUrl::where('url', $url)->exists();
            if ($isBlocked) {
                return response()->json([
                    'message' => 'This URL is not permitted.'
                ], 422);
            }

            PiholeUrl::create(['url' => $url]);

            return response()->json([
                'message' => 'URL saved and Pi-hole temporarily disabled for 5 minutes'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in pihole submit', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $urls = PiholeUrl::orderBy('created_at', 'desc')->get();

            return response()->json([
                'data' => $urls
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching URLs', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to fetch URLs'
            ], 500);
        }
    }

    public function destroy(PiholeUrl $url): JsonResponse
    {
        try {
            $url->delete();

            return response()->json([
                'message' => 'URL deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting URL', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete URL'
            ], 500);
        }
    }

    public function storeBlockedUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');

            // Check if URL is already blocked
            $existingBlocked = BlockedUrl::where('url', $url)->first();
            if ($existingBlocked) {
                return response()->json([
                    'message' => 'URL is already blocked'
                ], 422);
            }

            BlockedUrl::create(['url' => $url]);

            return response()->json([
                'message' => 'URL blocked successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error blocking URL', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'An error occurred while blocking the URL'
            ], 500);
        }
    }

    public function getBlockedUrls(): JsonResponse
    {
        try {
            $urls = BlockedUrl::orderBy('created_at', 'desc')->get();

            return response()->json([
                'data' => $urls
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching blocked URLs', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to fetch blocked URLs'
            ], 500);
        }
    }

    public function destroyBlockedUrl(BlockedUrl $blockedUrl): JsonResponse
    {
        try {
            $blockedUrl->delete();

            return response()->json([
                'message' => 'Blocked URL removed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing blocked URL', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to remove blocked URL'
            ], 500);
        }
    }

    private function sendUrlToPihole(): JsonResponse {
        $piholeSid = self::getPiholeSid();

        $blocker = Http::withoutVerifying()->withHeaders([
            "Content-Type" => "application/json"
        ])->withBody(json_encode([
                'domain' => "example.com",
                'sid' => $piholeSid,])
        )->post("https://{$this->piholeAddress}/api/domains/allow/regex");

        return $blocker->json();
    }

}
