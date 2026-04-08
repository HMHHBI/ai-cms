<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
        @if (session('status'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold mb-4">Pending Company Approvals</h3>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }} ({{ $user->email }})</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->company?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('super.approve', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 font-bold">Approve</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No pending approvals.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>