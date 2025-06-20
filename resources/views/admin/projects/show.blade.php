<x-layouts.app>
    <!-- Header with Buttons -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Project Details') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Details for:') }} <span
                    class="font-semibold">{{ $project->title }}</span></p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.projects.expenses.create', $project) }}"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                aria-label="{{ __('Add New Expense') }}">
                {{ __('Add Expense') }}
            </a>
            <a href="{{ route('admin.project.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900"
                aria-label="{{ __('Back to Projects') }}">
                {{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Project Details -->
        <div
            class="md:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-grow">
                <!-- Project Title -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Title:') }}</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $project->title }}</p>
                </div>

                <!-- Directorate -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Directorate:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->directorate->title ?? 'N/A' }}
                    </p>
                </div>

                <!-- Department -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Department:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->department->title ?? 'N/A' }}
                    </p>
                </div>

                <!-- Project Manager -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Project Manager:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->projectManager->name ?? 'N/A' }}</p>
                </div>

                <!-- Status -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->status->title ?? 'N/A' }}
                    </p>
                </div>

                <!-- Priority -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Priority:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->priority->title ?? 'N/A' }}
                    </p>
                </div>

                <!-- Physical Progress -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Physical Progress:') }}</p>
                    <div class="mt-1 flex items-center" role="progressbar" aria-valuenow="{{ $project->progress }}"
                        aria-valuemin="0" aria-valuemax="100">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $project->progress }}%"></div>
                        </div>
                        <p class="ml-2 text-lg text-gray-900 dark:text-gray-100">{{ $project->progress }}%</p>
                    </div>
                </div>

                <!-- Financial Progress -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Financial Progress:') }}</p>
                    <div class="mt-1 flex items-center" role="progressbar"
                        aria-valuenow="{{ $project->financial_progress }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-green-500 h-2.5 rounded-full"
                                style="width: {{ $project->financial_progress }}%"></div>
                        </div>
                        <p class="ml-2 text-lg text-gray-900 dark:text-gray-100">{{ $project->financial_progress }}%
                        </p>
                    </div>
                </div>

                <!-- Show in Chart Button -->
                <div class="col-span-full">
                    <a href="{{ route('admin.projects.progress.chart', $project) }}"
                        class="inline-block px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        aria-label="{{ __('View Progress Chart') }}">
                        {{ __('Show in Chart') }}
                    </a>
                </div>

                <!-- Collapsible Contracts Section -->
                @if ($project->contracts->isNotEmpty())
                    <div class="col-span-full mt-6">
                        <button type="button"
                            class="flex items-center w-full text-lg font-bold text-gray-800 dark:text-gray-100 hover:text-blue-500 dark:hover:text-blue-400 focus:outline-none"
                            onclick="toggleSection('contracts-section')" aria-expanded="true"
                            aria-controls="contracts-section">
                            <span class="mr-2">{{ __('Contracts') }}</span>
                            <svg class="w-5 h-5 transform transition-transform" id="contracts-icon" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="contracts-section" class="mt-4">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Title') }}</th>
                                        <th
                                            class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Progress') }}</th>
                                        <th
                                            class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Contract Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($project->contracts as $contract)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                                {{ $contract->title }}</td>
                                            <td class="px-4 py-2">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                                    <div class="bg-blue-500 h-2.5 rounded-full"
                                                        style="width: {{ $contract->progress }}%"></div>
                                                </div>
                                                <span>{{ $contract->progress }}%</span>
                                            </td>
                                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                                {{ number_format($contract->contract_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Start Date -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Start Date:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}
                    </p>
                </div>

                <!-- End Date -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('End Date:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}
                    </p>
                </div>

                <!-- Total Budget -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Budget (Latest):') }}
                    </p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ number_format($project->total_budget, 2) }}</p>
                </div>

                <!-- Description -->
                <div class="col-span-full">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->description ?? 'N/A' }}</p>
                </div>

                <!-- Created At -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->created_at->format('M d, Y H:i A') }}</p>
                </div>

                <!-- Updated At -->
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Updated At:') }}</p>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $project->updated_at->format('M d, Y H:i A') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex space-x-3">
                <a href="{{ route('admin.project.edit', $project) }}"
                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    aria-label="{{ __('Edit Project') }}">
                    {{ __('Edit Project') }}
                </a>
                <form action="{{ route('admin.project.destroy', $project) }}" method="POST"
                    onsubmit="return confirm('{{ __('Are you sure you want to delete this project? This action cannot be undone.') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        aria-label="{{ __('Delete Project') }}">
                        {{ __('Delete Project') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Comments Section -->
        <div
            class="md:col-span-1 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 flex flex-col">
            <button type="button"
                class="flex items-center w-full text-xl font-bold text-gray-800 dark:text-gray-100 hover:text-blue-500 dark:hover:text-blue-400 focus:outline-none mb-4"
                onclick="toggleSection('comments-section')" aria-expanded="true" aria-controls="comments-section">
                <span class="mr-2">{{ __('Comments') }}</span>
                <svg class="w-5 h-5 transform transition-transform" id="comments-icon" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="comments-section" class="flex-grow overflow-y-auto pr-2" style="max-height: 300px;">
                @if ($project->comments->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('No comments yet. Be the first to add one!') }}</p>
                @else
                    @foreach ($project->comments->whereNull('parent_id') as $comment)
                        @include('admin.comments.comment', [
                            'comment' => $comment,
                            'level' => 0,
                            'commentable' => $project,
                            'routePrefix' => 'admin.projects',
                        ])
                    @endforeach
                @endif
            </div>
            <div class="mt-6 flex-shrink-0">
                <form method="POST" action="{{ route('admin.projects.comments.store', $project) }}"
                    class="space-y-4">
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
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            aria-label="{{ __('Post Comment') }}">
                            {{ __('Post Comment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

    <!-- JavaScript for Collapsible Sections -->
    <script>
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById(`${sectionId}-icon`);
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                icon.classList.remove('rotate-180');
                section.previousElementSibling.setAttribute('aria-expanded', 'true');
            } else {
                section.classList.add('hidden');
                icon.classList.add('rotate-180');
                section.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
        }

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
