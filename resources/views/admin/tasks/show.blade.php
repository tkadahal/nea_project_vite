<x-layouts.app>
    @if (session('success'))
        <div
            class="alert alert-success bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ trans('global.task.title_singular') }} {{ trans('global.details') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ trans('global.details_for') }} :
                <span class="font-semibold">
                    {{ $task['title'] ?? 'Unnamed Task' }} ({{ $task['project_name'] ?? 'No Project' }})
                </span>
            </p>
        </div>

        @can('task_access')
            <a href="{{ route('admin.task.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
                {{ trans('global.back_to_list') }}
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="md:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-grow">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.title') }}
                    </p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $task['title'] ?? 'Unnamed Task' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.status_id') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        @if ($task['status'])
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                style="background-color: {{ $task['status']['color'] ?? 'gray' }};">
                                {{ ucfirst($task['status']['title']) }}
                            </span>
                        @else
                            None
                        @endif
                    </p>
                </div>

                <div class="col-span-full">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.description') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['description'] ?? 'No description provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.priority_id') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        @if ($task['priority'])
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                style="background-color: {{ $task['priority']['color'] ?? 'gray' }};">
                                {{ ucfirst($task['priority']['title']) }}
                            </span>
                        @else
                            None
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.project_id') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100 space-y-2">
                        @if (!empty($task['projects']))
                            @foreach ($task['projects'] as $project)
                                <span
                                    class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs w-fit"
                                    title="{{ $project['title'] }}">
                                    {{ $project['title'] }}
                                    @if ($project['id'] == $task['project_id'])
                                        <span class="ml-1 text-xs font-semibold">(Current)</span>
                                    @endif
                                </span>
                            @endforeach
                        @else
                            <span class="text-gray-500 dark:text-gray-400 text-xs w-fit">
                                {{ trans('global.noRecords') }}
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.progress') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['progress'] ?? 0 }}%
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.user_id') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        @if (!empty($task['users']))
                            <span
                                class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs"
                                title="{{ collect($task['users'])->pluck('name')->implode(', ') }}">
                                {{ collect($task['users'])->pluck('initials')->implode(', ') }}
                            </span>
                        @else
                            {{ trans('global.noRecords') }}
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.start_date') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['start_date'] ? \Carbon\Carbon::parse($task['start_date'])->format('M d, Y') : 'Not set' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.due_date') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['due_date'] ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'Not set' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.completion_date') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['completion_date'] ? \Carbon\Carbon::parse($task['completion_date'])->format('M d, Y') : 'Not completed' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.created_at') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['created_at']->format('M d, Y H:i A') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ trans('global.task.fields.updated_at') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $task['updated_at']->format('M d, Y H:i A') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex space-x-3">
                @can('task_edit')
                    <a href="{{ route('admin.task.edit', $task['id']) }}"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                        {{ trans('global.edit') }} {{ trans('global.task.title_singular') }}
                    </a>
                @endcan

                @can('task_delete')
                    <form action="{{ route('admin.task.destroy', $task['id']) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                            {{ trans('global.delete') }} {{ trans('global.task.title_singular') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div
            class="md:col-span-1 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ __('Comments') }}</h2>

            <div class="flex-grow overflow-y-auto pr-2" style="max-height: 500px;">
                @if ($comments->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('No comments yet. Be the first to add one!') }}
                    </p>
                @else
                    @foreach ($comments as $comment)
                        <div class="border-b py-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $comment->user->name }} • {{ $comment->created_at->diffForHumans() }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">{!! $comment->content !!}</p>
                            @if ($comment->replies->count())
                                <div class="ml-4 mt-2">
                                    @foreach ($comment->replies as $reply)
                                        <div class="border-l pl-4 py-1">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $reply->user->name }} • {{ $reply->created_at->diffForHumans() }}
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-300">{!! $reply->content !!}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-2">
                                <button onclick="toggleReplyForm({{ $comment->id }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ __('Reply') }}
                                </button>
                                <form id="reply-form-{{ $comment->id }}" class="hidden mt-2" method="POST"
                                    action="{{ route('admin.tasks.comments.store', [$task['id'], $task['project_id']]) }}">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <input type="hidden" name="project_id" value="{{ $task['project_id'] }}">
                                    <div class="flex items-start space-x-3">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white font-medium">
                                            {{ Auth::user()->initials() }}
                                        </span>
                                        <div class="flex-1">
                                            <textarea name="content"
                                                class="w-full p-3 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="{{ __('Reply to comment...') }}" rows="3" required></textarea>
                                            @error('content')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                            {{ __('Post Reply') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mt-6 flex-shrink-0">
                <form method="POST"
                    action="{{ route('admin.tasks.comments.store', [$task['id'], $task['project_id']]) }}"
                    class="space-y-4">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $task['project_id'] }}">
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
