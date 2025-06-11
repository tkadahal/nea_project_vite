<x-layouts.app>
    <!-- Page Title and Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Project Details') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Details for:') }} <span
                    class="font-semibold">{{ $project->title }}</span></p>
        </div>
        <a href="{{ route('admin.project.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            {{ __('Back to Projects') }}
        </a>
    </div>

    <!-- Project Details Card -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Project Title --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Title:') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $project->title }}</p>
            </div>

            {{-- Directorate --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Directorate:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->directorate->name ?? 'N/A' }}</p>
            </div>

            {{-- Department --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Department:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->department->name ?? 'N/A' }}</p>
            </div>

            {{-- Project Manager --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Project Manager:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->projectManager->name ?? 'N/A' }}
                </p>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->status->name ?? 'N/A' }}</p>
            </div>

            {{-- Priority --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Priority:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->priority->name ?? 'N/A' }}</p>
            </div>

            {{-- Progress --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Progress:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->progress ?? 'N/A' }}%</p>
            </div>

            {{-- Start Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Start Date:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</p>
            </div>

            {{-- End Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('End Date:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</p>
            </div>

            {{-- Total Budget (from accessor) --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Budget (Latest):') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ number_format($project->total_budget, 2) }}</p>
            </div>

            {{-- Description (full width if needed, or adjust grid) --}}
            <div class="col-span-full">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->description ?? 'N/A' }}</p>
            </div>

            {{-- Created At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $project->created_at->format('M d, Y H:i A') }}</p>
            </div>

            {{-- Updated At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Updated At:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $project->updated_at->format('M d, Y H:i A') }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex space-x-3">
            <a href="{{ route('admin.project.edit', $project) }}"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600
                      focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                      dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                {{ __('Edit Project') }}
            </a>

            <form action="{{ route('admin.project.destroy', $project) }}" method="POST"
                onsubmit="return confirm('{{ __('Are you sure you want to delete this project? This action cannot be undone.') }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                               dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                    {{ __('Delete Project') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
