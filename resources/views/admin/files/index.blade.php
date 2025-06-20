<x-layouts.app>
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center text-sm text-gray-600 dark:text-gray-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.project.index') }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ __('Projects') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('Files') }}</span>
    </nav>

    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('All Files') }}</h1>
            <div class="flex space-x-2">
                <button id="grid-view-btn" class="p-2 rounded-md bg-blue-500 text-white"
                    aria-label="{{ __('Grid View') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button id="list-view-btn" class="p-2 rounded-md bg-gray-200 dark:bg-gray-700"
                    aria-label="{{ __('List View') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grid View -->
        <div id="grid-view" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                // Group files by fileable_type and fileable_id
                $groupedFiles = $files->groupBy(function ($file) {
                    return $file->fileable_type . '|' . $file->fileable_id;
                });
            @endphp

            @forelse ($groupedFiles as $key => $group)
                @php
                    $file = $group->first();
                    $folderName = match ($file->fileable_type) {
                        \App\Models\Project::class => 'Project: ' . $file->fileable->title,
                        \App\Models\Contract::class => 'Contract: ' . $file->fileable->title,
                        \App\Models\Task::class => 'Task: ' . $file->fileable->title,
                        default => 'Unknown: ' . $file->fileable_type,
                    };
                    $folderId = str_replace('\\', '_', $file->fileable_type) . '_' . $file->fileable_id;
                @endphp

                <!-- Folder -->
                <div class="folder border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col items-center text-center bg-white dark:bg-gray-800 cursor-pointer"
                    data-folder-id="{{ $folderId }}" role="region" aria-label="Folder {{ $folderName }}">
                    <div class="mb-2">
                        <svg class="w-16 h-16 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20 6h-8l-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2zm0 12H4V6h5.17l2 2H20v10z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate w-full">{{ $folderName }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $group->count() }} {{ __('file(s)') }}</p>
                </div>

                <!-- Files inside Folder (hidden by default) -->
                <div id="files-{{ $folderId }}"
                    class="hidden col-span-full border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($group as $file)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col items-center text-center bg-white dark:bg-gray-800"
                                role="region" aria-label="File {{ $file->filename }}">
                                <div class="mb-2">
                                    @if (in_array($file->file_type, ['jpg', 'jpeg', 'png']))
                                        <img src="{{ Storage::url($file->path) }}" alt="{{ $file->filename }}"
                                            class="w-16 h-16 object-cover rounded">
                                    @elseif ($file->file_type === 'pdf')
                                        <svg class="w-16 h-16 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M20 2H8a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2zm-1 14H9V4h10v12z" />
                                        </svg>
                                    @else
                                        <svg class="w-16 h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm4 18H6V4h7v5h5v11z" />
                                        </svg>
                                    @endif
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate w-full">
                                    {{ $file->filename }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $file->file_type }} •
                                    {{ round($file->file_size / 1024, 2) }} KB</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">By {{ $file->user->name }} on
                                    {{ $file->created_at->format('M d, Y') }}</p>
                                <div class="mt-2 flex space-x-2">
                                    <a href="{{ route('admin.files.download', $file) }}"
                                        class="text-blue-500 hover:text-blue-600"
                                        aria-label="{{ __('Download') }} {{ $file->filename }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    @can('delete', $file)
                                        <form action="{{ route('admin.files.destroy', $file) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this file?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-600"
                                                aria-label="{{ __('Delete') }} {{ $file->filename }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 col-span-full text-center">{{ __('No files found.') }}</p>
            @endforelse
        </div>

        <!-- List View -->
        <div id="list-view" class="hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Name') }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Type') }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Size') }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Uploaded By') }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Date') }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($groupedFiles as $key => $group)
                        @php
                            $file = $group->first();
                            $folderName = match ($file->fileable_type) {
                                \App\Models\Project::class => 'Project: ' . $file->fileable->title,
                                \App\Models\Contract::class => 'Contract: ' . $file->fileable->title,
                                \App\Models\Task::class => 'Task: ' . $file->fileable->title,
                                default => 'Unknown: ' . $file->fileable_type,
                            };
                            $folderId = str_replace('\\', '_', $file->fileable_type) . '_' . $file->fileable_id;
                        @endphp

                        <!-- Folder Row -->
                        <tr class="folder cursor-pointer" data-folder-id="{{ $folderId }}">
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20 6h-8l-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2zm0 12H4V6h5.17l2 2H20v10z" />
                                </svg>
                                {{ $folderName }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ __('Folder') }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $group->count() }}
                                {{ __('file(s)') }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100"></td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100"></td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100"></td>
                        </tr>

                        <!-- File Rows (hidden by default) -->
                        @foreach ($group as $file)
                            <tr id="file-row-{{ $folderId }}-{{ $file->id }}"
                                class="hidden file-row-{{ $folderId }}">
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100 pl-8">{{ $file->filename }}</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $file->file_type }}</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                    {{ round($file->file_size / 1024, 2) }} KB</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $file->user->name }}</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                    {{ $file->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <a href="{{ route('admin.files.download', $file) }}"
                                        class="text-blue-500 hover:text-blue-600"
                                        aria-label="{{ __('Download') }} {{ $file->filename }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    @can('delete', $file)
                                        <form action="{{ route('admin.files.destroy', $file) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this file?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-600"
                                                aria-label="{{ __('Delete') }} {{ $file->filename }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-gray-500 dark:text-gray-400 text-center">
                                {{ __('No files found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize grid view as default
            $('#grid-view').show();
            $('#list-view').hide();

            // Grid view button click
            $('#grid-view-btn').on('click', function() {
                $('#grid-view').show();
                $('#list-view').hide();
                $(this).addClass('bg-blue-500 text-white').removeClass('bg-gray-200 dark:bg-gray-700');
                $('#list-view-btn').removeClass('bg-blue-500 text-white').addClass(
                    'bg-gray-200 dark:bg-gray-700');
            });

            // List view button click
            $('#list-view-btn').on('click', function() {
                $('#grid-view').hide();
                $('#list-view').show();
                $(this).addClass('bg-blue-500 text-white').removeClass('bg-gray-200 dark:bg-gray-700');
                $('#grid-view-btn').removeClass('bg-blue-500 text-white').addClass(
                    'bg-gray-200 dark:bg-gray-700');
            });

            // Folder click handler for grid view
            $('.folder').on('click', function() {
                const folderId = $(this).data('folder-id');
                $(`#files-${folderId}`).toggle();
            });

            // Folder click handler for list view
            $('#list-view .folder').on('click', function() {
                const folderId = $(this).data('folder-id');
                $(`.file-row-${folderId}`).toggle('fast');
            });
        });
    </script>
</x-layouts.app>
