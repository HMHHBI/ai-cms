<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
  protected $apiKey;

  public function __construct()
  {
    $this->apiKey = config('services.gemini.key');
  }

  public function generateResponse($prompt)
  {
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

    $response = Http::withoutVerifying()->post($url, [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt]
          ]
        ]
      ]
    ]);

    // Agar error aaye toh poora response dikhayein debug karne ke liye
    if ($response->failed()) {
      return "API Request Failed: " . $response->body();
    }

    return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'AI Error';
  }
}