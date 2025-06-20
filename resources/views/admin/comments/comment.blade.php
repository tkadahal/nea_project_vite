<div class="comment mb-4" style="margin-left: {{ $level * 1.5 }}rem;">
    <div class="flex items-start space-x-3">
        <span
            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white font-medium text-sm">
            {{ $comment->user->initials() }}
        </span>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $comment->user->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
            </div>
            <p class="mt-1 text-gray-800 dark:text-gray-200 text-sm sm:text-base">{{ $comment->content }}</p>
            <button class="text-blue-500 hover:underline text-sm mt-2 inline-block focus:outline-none"
                onclick="toggleReplyForm({{ $comment->id }})">
                {{ __('Reply') }}
            </button>
        </div>
    </div>

    <!-- Reply Form (Hidden by Default) -->
    <div id="reply-form-{{ $comment->id }}" class="mt-3 hidden pl-11">
        <form method="POST" action="{{ route($routePrefix . '.comments.store', $commentable) }}" class="space-y-3">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <div class="flex items-start space-x-3">
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white font-medium text-sm">
                    {{ Auth::user()->initials() }}
                </span>
                <div class="flex-1">
                    <textarea name="content"
                        class="w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Write a reply...') }}" rows="3" required></textarea>
                    @error('content')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    {{ __('Post Reply') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Nested Replies -->
    @if ($comment->replies && $comment->replies->isNotEmpty())
        @foreach ($comment->replies as $reply)
            @include('admin.comments.comment', [
                'comment' => $reply,
                'level' => $level + 1,
                'commentable' => $commentable,
                'routePrefix' => $routePrefix,
            ])
        @endforeach
    @endif
</div>
