<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $subject = '';
    public string $message = '';

    public function saveTicket(\App\Services\TicketService $service)
    {
        $this->validate([
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);

        $service->createTicket(Auth::user(), [
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        return redirect()->to('/dashboard')->with('status', 'Ticket created with AI suggestion!');
    }
}; ?>

<div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
    <form wire:submit="saveTicket">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
            <input type="text" wire:model="subject"
                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
            <textarea wire:model="message" rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Create Ticket (with AI Draft)
        </button>
    </form>
</div>