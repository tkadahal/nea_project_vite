<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Task Details') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Details for:') }} <span
                    class="font-semibold">{{ $task->title ?? 'Unnamed Task' }}</span></p>
        </div>
        <a href="{{ route('admin.task.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            {{ __('Back to Tasks') }}
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="md:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-grow">
                {{-- Task Title --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Task Title') }}</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $task->title ?? 'Unnamed Task' }}</p>
                </div>

                {{-- Status --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $task->status->title ?? 'Unknown' }}</p>
                </div>

                {{-- Description --}}
                <div class="col-span-full">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->description ?? 'No description provided' }}</p>
                </div>

                {{-- Priority --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Priority') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $task->priority->title ?? 'None' }}</p>
                </div>

                {{-- Project --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Project') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->projects->first()->title ?? 'None' }}</p>
                </div>

                {{-- Assigned Users --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Assigned Users') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        @if ($task->users->isNotEmpty())
                            <span
                                class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs"
                                title="{{ $task->users->pluck('name')->implode(', ') }}">
                                {{ $task->users->map(fn($user) => $user->initials())->implode(', ') }}
                            </span>
                        @else
                            {{ __('No users assigned') }}
                        @endif
                    </p>
                </div>

                {{-- Start Date --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Start Date') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('M d, Y') : 'Not set' }}
                    </p>
                </div>

                {{-- Due Date --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Due Date') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'Not set' }}
                    </p>
                </div>

                {{-- Completion Date --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Completion Date') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->completion_date ? \Carbon\Carbon::parse($task->completion_date)->format('M d, Y') : 'Not completed' }}
                    </p>
                </div>

                {{-- Created At --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->created_at->format('M d, Y H:i A') }}
                    </p>
                </div>

                {{-- Updated At --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Updated At') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task->updated_at->format('M d, Y H:i A') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex space-x-3 flex-shrink-0">
                <a href="{{ route('admin.task.edit', $task) }}"
                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                    {{ __('Edit Task') }}
                </a>

                <form action="{{ route('admin.task.destroy', $task) }}" method="POST"
                    onsubmit="return confirm('{{ __('Are you sure you want to delete this task? This action cannot be undone.') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                        {{ __('Delete Task') }}
                    </button>
                </form>
            </div>
        </div>

        <div
            class="md:col-span-1 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ __('Comments') }}</h2>

            <div class="flex-grow overflow-y-auto pr-2" style="max-height: 500px;">
                @if (!$task->comments || $task->comments->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('No comments yet. Be the first to add one!') }}</p>
                @else
                    @foreach ($task->comments->whereNull('parent_id') as $comment)
                        @include('admin.comments.comment', [
                            'comment' => $comment,
                            'level' => 0,
                            'commentable' => $task,
                            'routePrefix' => 'admin.tasks',
                        ])
                    @endforeach
                @endif
            </div>

            <div class="mt-6 flex-shrink-0">
                <form method="POST" action="{{ route('admin.tasks.comments.store', $task) }}" class="space-y-4">
                    @csrf
                    <div class="flex items-start space-x-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white font-medium">
                            {{ Auth::user()->initials() }}
                        </span>
                        <div class="flex-1">
                            <textarea name="content"
                                class="w-full p-3 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="{{ __('Add a comment...') }}" rows="4" required></textarea>
                            @error('content')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            {{ __('Post Comment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById(`reply-form-${commentId}`);
            if (form) {
                form.classList.toggle('hidden');
            } else {
                console.error(`Reply form with ID reply-form-${commentId} not found.`);
            }
        }
    </script>
</x-layouts.app>
