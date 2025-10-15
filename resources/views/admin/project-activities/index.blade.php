{{-- resources/views/admin/project-activities/index.blade.php --}}
<x-layouts.app>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Project Activities Overview
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Summary of budgets by project and fiscal year.
            </p>
        </div>

        {{-- @can('budget_create') --}}
        <a href="{{ route('admin.projectActivity.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                  dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-900">
            Add Project Activity
        </a>
        {{-- @endcan --}}
    </div>

    @if (session('success'))
        <div
            class="mb-6 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg dark:bg-green-900 dark:text-green-200 dark:border-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Fiscal Year
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Project
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Total Budget
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Capital Budget
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Recurrent Budget
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-600">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $activity->fiscalYear->title ?? ($activity->fiscalYear->title ?? 'N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $activity->project->title ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                {{ number_format($activity->total_budget ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                {{ number_format($activity->capital_budget ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                {{ number_format($activity->recurrent_budget ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.projectActivity.edit', [$activity->project_id, $activity->fiscal_year_id]) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    Edit
                                </a>
                                <a href="{{ route('admin.projectActivity.show', [$activity->project_id, $activity->fiscal_year_id]) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No project activities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
