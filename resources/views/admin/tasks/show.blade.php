<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Task Details</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Details for: <span
                    class="font-semibold">{{ $task->name ?? 'Unnamed Task' }}</span></p>
        </div>
        <a href="{{ route('admin.task.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Tasks
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Task Name --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Task Title</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $task->title ?? 'Unnamed Task' }}</p>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $task->status->title ?? 'Unknown' }}</p>
            </div>

            {{-- Description --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->description ?? 'No description provided' }}</p>
            </div>

            {{-- Priority --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Priority</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $task->priority->title ?? 'None' }}</p>
            </div>

            {{-- Project --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $task->projects->first()->title ?? 'None' }}
                </p>
            </div>

            {{-- Assigned Users --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Users</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    @if ($task->users->isNotEmpty())
                        <span
                            class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs"
                            title="{{ $task->users->pluck('name')->implode(', ') }}">
                            {{ $task->users->map(fn($user) => $user->initials())->implode(', ') }}
                        </span>
                    @else
                        No users assigned
                    @endif
                </p>
            </div>

            {{-- Start Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('M d, Y') : 'Not set' }}
                </p>
            </div>

            {{-- Due Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'Not set' }}
                </p>
            </div>

            {{-- Completion Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completion Date</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->completion_date ? \Carbon\Carbon::parse($task->completion_date)->format('M d, Y') : 'Not completed' }}
                </p>
            </div>

            {{-- Created At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->created_at->format('M d, Y H:i A') }}
                </p>
            </div>

            {{-- Updated At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated At</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $task->updated_at->format('M d, Y H:i A') }}
                </p>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="{{ route('admin.task.edit', $task) }}"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600
                      focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                      dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                Edit Task
            </a>

            <form action="{{ route('admin.task.destroy', $task) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                               dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                    Delete Task
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
