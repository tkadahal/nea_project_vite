<x-layouts.app>
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center text-sm text-gray-600 dark:text-gray-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.project.index') }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ __('Projects') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.project.show', $project) }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ $project->title }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('Add Expense') }}</span>
    </nav>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add Expense for:') }}
            {{ $project->title }}</h1>

        <form method="POST" action="{{ route('admin.projects.expenses.store', $project) }}"
            class="mt-6 space-y-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            @csrf
            <div>
                <label for="amount"
                    class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</label>
                <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required
                    class="mt-1 w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    aria-describedby="amount-error">
                @error('amount')
                    <p class="text-red-500 text-sm mt-1" id="amount-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description"
                    class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</label>
                <textarea name="description" id="description"
                    class="mt-1 w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    rows="4">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="date"
                    class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" required
                    class="mt-1 w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    aria-describedby="date-error">
                @error('date')
                    <p class="text-red-500 text-sm mt-1" id="date-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.project.show', $project) }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    aria-label="{{ __('Cancel') }}">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 dark:focus:ring-offset-gray-900"
                    aria-label="{{ __('Add Expense') }}">
                    {{ __('Add Expense') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
