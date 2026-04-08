<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-xl font-bold mb-6">Manage Your Staff</h2>

            <form action="{{ route('admin.staff.store') }}" method="POST" class="mb-10 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded">
                @csrf
                <input type="text" name="name" placeholder="Full Name" class="border-gray-300 rounded-md" required>
                <input type="email" name="email" placeholder="Email Address" class="border-gray-300 rounded-md" required>
                <input type="password" name="password" placeholder="Password" class="border-gray-300 rounded-md" required>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Staff</button>
            </form>

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($staff as $member)
                      <tr>
                          <td class="px-6 py-4">{{ $member->name }}</td>
                          <td class="px-6 py-4">{{ $member->email }}</td>
                          <td class="px-6 py-4 text-right">
                              <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                  @csrf @method('DELETE')
                                  <button class="text-red-600 hover:text-red-900">Remove</button>
                              </form>
                          </td>
                      </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>