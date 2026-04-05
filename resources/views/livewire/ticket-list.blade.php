<?php
use Livewire\Volt\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

new class extends Component {
  // List fetch karne ke liye
  public $selectedTicket = null;

  public function viewTicket($id)
  {
    $this->selectedTicket = Ticket::find($id);
  }

  public function closeTicket($id)
  {
    Ticket::find($id)->update(['status' => 'closed']);
    $this->selectedTicket = null;
  }

  public function with()
  {
    return [
      'tickets' => Ticket::with('user')->where('user_id', Auth::id())->latest()->get(),
    ];
  }

  public function approveAndSend($ticketId)
  {
    $ticket = Ticket::with('user')->find($ticketId);

    // Asli Email Logic
    Mail::raw("Hi {$ticket->user->name}, \n\n{$ticket->ai_suggestion}", function ($message) use ($ticket) {
      $message->to($ticket->user->email)
        ->subject('Support Reply: ' . $ticket->subject);
    });

    $ticket->update(['status' => 'closed']);

    session()->flash('status', 'Email sent and ticket closed!');
  }

  public function deleteTicket($id)
  {
    // Sirf Admin hi delete kar sakay
    if (Auth::user()->role === 'admin') {
      Ticket::find($id)->delete();
      session()->flash('status', 'Ticket deleted successfully!');
    }
  }
}; ?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">AI Suggestion</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mood</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
      @foreach($tickets as $ticket)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
          <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</td>
          <td class="px-6 py-4 text-sm">
            <span
              class="px-2 py-1 rounded text-xs {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
              {{ ucfirst($ticket->status) }}
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-500 italic">
            {{ Str::limit($ticket->ai_suggestion, 50) }}
          </td>
          <td class="px-6 py-4 text-sm">
            <div class="flex gap-2">
              <button wire:click="viewTicket({{ $ticket->id }})"
                class="ml-2 bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">
                View & Reply
              </button>
              @if(auth()->user()->role === 'admin')
                <button wire:click="approveAndSend({{ $ticket->id }})"
                  class="ml-2 bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700"
                  onclick="confirm('Are you sure you want to send this AI reply?') || event.stopImmediatePropagation()">
                  Approve & Send Email
                </button>
              @else
                <span class="text-xs text-gray-400 italic">Waiting for Admin Approval</span>
              @endif
              @if(auth()->user()->role === 'admin')
                <button wire:click="deleteTicket({{ $ticket->id }})"
                  wire:confirm="Are you sure you want to delete this ticket?"
                  class="ml-2 text-red-600 hover:text-red-900 transition" title="Delete Ticket">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              @endif
            </div>
          </td>
          <td class="px-6 py-4 text-sm font-bold">
            @if(Str::contains(strtolower($ticket->ai_suggestion), 'negative'))
              <span class="text-red-600 bg-red-100 px-2 py-1 rounded-full text-xs">🔥 Urgent / Angry</span>
            @elseif(Str::contains(strtolower($ticket->ai_suggestion), 'positive'))
              <span class="text-green-600 bg-green-100 px-2 py-1 rounded-full text-xs">😊 Happy Customer</span>
            @else
              <span class="text-blue-600 bg-blue-100 px-2 py-1 rounded-full text-xs">😐 Neutral</span>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @if($selectedTicket)
    <div class="mt-8 p-6 bg-indigo-50 border-l-4 border-indigo-500 rounded shadow-md dark:bg-gray-700">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100">
          Ticket Details: {{ $selectedTicket->subject }}
        </h3>
        <button wire:click="$set('selectedTicket', null)" class="text-gray-500 hover:text-red-500">
          ✖ Close
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <span class="font-bold text-gray-700 dark:text-gray-300">Customer Message:</span>
          <p class="mt-1 p-3 bg-white dark:bg-gray-900 rounded border dark:text-gray-200">
            {{ $selectedTicket->message }}
          </p>
        </div>

        <div>
          <span class="font-bold text-gray-700 dark:text-gray-300">AI Suggested Reply:</span>
          <div
            class="mt-1 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 rounded text-green-900 dark:text-green-100">
            {!! nl2br(e($selectedTicket->ai_suggestion)) !!}
          </div>
        </div>

        <div class="flex gap-2">
          <button wire:click="approveAndSend({{ $selectedTicket->id }})"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Confirm & Send this Reply
          </button>

          <button wire:click="closeTicket({{ $selectedTicket->id }})"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Mark as Resolved (Close)
          </button>
        </div>
      </div>
    </div>
  @endif
</div>