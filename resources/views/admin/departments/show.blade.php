<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">department Details</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Details for: <span
                    class="font-semibold">{{ $department->title }}</span></p>
        </div>
        <a href="{{ route('admin.department.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to departments
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- department Title --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Title:</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $department->title }}</p>
            </div>

            {{-- Description --}}
            <div class="md:col-span-2"> {{-- This div spans 2 columns on medium screens and up --}}
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $department->description ?? 'N/A' }} {{-- Display 'N/A' if description is empty --}}
                </p>
            </div>

            {{-- Created At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $department->created_at->format('M d, Y H:i A') }}</p>
            </div>

            {{-- Updated At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated At:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $department->updated_at->format('M d, Y H:i A') }}</p>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="{{ route('admin.department.edit', $department) }}"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600
                      focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                      dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                Edit department
            </a>

            <form action="{{ route('admin.department.destroy', $department) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this department? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                               dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                    Delete department
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
