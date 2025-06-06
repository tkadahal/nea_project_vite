<x-layouts.app>
    <!-- Page Title -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Priority') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update Priority') }}</p>
        </div>

        <a href="{{ route('admin.priority.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Priorities
        </a>
    </div>

    <div class="flex flex-col md:flex-row gap-6">

        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6">
                    <form class="max-w-md mb-10" action="{{ route('admin.priority.update', $priority->id) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-forms.input label="Title" name="title" type="text"
                                value="{{ old('title', $priority->title) }}" />
                        </div>

                        <div class="flex space-x-2">
                            <x-buttons.primary>{{ __('Update') }}</x-buttons.primary>
                            <a href="{{ route('admin.priority.index') }}"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
