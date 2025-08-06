<?php

namespace App\Http\Controllers;

use App\Models\PiholeUrl;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PiholeController extends Controller
{
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
            
            PiholeUrl::create(['url' => $url]);

            $piholeAddress = config('pihole.address', 'pi.hole');
            $piholeToken = config('pihole.api_token');

            if ($piholeToken) {
                $response = Http::timeout(10)->get("http://{$piholeAddress}/admin/api.php", [
                    'disable' => 300,
                    'auth' => $piholeToken
                ]);

                if (!$response->successful()) {
                    \Log::warning('Pi-hole API call failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            }

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
}
