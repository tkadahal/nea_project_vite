<x-layouts.app>
    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Priority') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create new priority') }}</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">

        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6">
                    <form class="max-w-md mb-10" action="{{ route('admin.priority.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-forms.input label="Title" name="title" type="text" />
                        </div>

                        <div>
                            <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
