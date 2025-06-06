<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">User Details</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Details for: <span
                    class="font-semibold">{{ $user->name }}</span></p>
        </div>
        <a href="{{ route('admin.user.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Users
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- User Name --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Name:</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
            </div>

            {{-- Projects --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Projects:</p>
                <div class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    @forelse ($user->projects as $project)
                        {{-- Assuming x-forms.badge is defined elsewhere --}}
                        <x-forms.badge :title="$project->title" color="green" />
                    @empty
                        <p class="text-lg text-gray-900 dark:text-gray-100">No project assigned</p>
                    @endforelse
                </div>

            </div>

            {{-- Created At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $user->created_at->format('M d, Y H:i A') }}</p>
            </div>

            {{-- Updated At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated At:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $user->updated_at->format('M d, Y H:i A') }}</p>
            </div>

            {{-- Directorate --}}
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Directorate:</p>
                <x-forms.badge :title="$user->directorate->title" color="purple" />
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="{{ route('admin.user.edit', $user) }}"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600
                      focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                      dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                Edit User
            </a>

            <form action="{{ route('admin.user.destroy', $user) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600
                           focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                           dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                    Delete User
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
