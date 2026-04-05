<?php

use Livewire\Volt\Component;
use App\Services\GeminiService;

new class extends Component {
    public string $prompt = '';
    public string $response = '';
    public bool $loading = false;

    public function askAi(GeminiService $ai)
    {
        if (empty($this->prompt))
            return;

        $this->loading = true;
        $this->response = $ai->generateResponse($this->prompt);
        $this->loading = false;
        $this->prompt = ''; // Input clear kar dein
    }
}; ?>

<div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        🤖 AI Assistant (Gemini)
    </h3>

    <div class="mb-4">
        <textarea 
            wire:model="prompt" 
            placeholder="Ask AI to write a reply or analyze data..."
            class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white"
        ></textarea>
    </div>

    <button 
        wire:click="askAi" 
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
        wire:loading.attr="disabled"
    >
        <span wire:loading.remove>Ask AI</span>
        <span wire:loading>Thinking...</span>
    </button>

    @if($response)
        <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-md text-gray-800 dark:text-gray-200">
            <strong>AI Response:</strong>
            <p class="mt-2 text-sm">{{ $response }}</p>
        </div>
    @endif
</div>