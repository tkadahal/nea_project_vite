{{-- resources/views/admin/project-activities/show.blade.php --}}
<x-layouts.app>
    <div class="mb-6">
        <div class="flex justify-between items-start mb-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ trans('global.projectActivity.title') }} - {{ $project->title }} -
                {{ $fiscalYear->title ?? $fiscalYear->id }}
            </h1>

            <!-- NEW: Button Group - Excel and Back to List close together -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.projectActivity.download-activities', [$projectId, $fiscalYearId]) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">
                    <svg class="h-4 w-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Download Excel with Weighted Progress
                </a>
                <a href="{{ route('admin.projectActivity.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            Detailed breakdown of Annual Program for Fiscal Year {{ $fiscalYear->title }}.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">

        @include('admin.project-activities.partials.activity-table', [
            'activities' => $capitalActivities,
            'header' => trans('global.projectActivity.headers.capital'),
            'sums' => $capitalSums,
        ])

        @include('admin.project-activities.partials.activity-table', [
            'activities' => $recurrentActivities,
            'header' => trans('global.projectActivity.headers.recurrent'),
            'sums' => $recurrentSums,
        ])

        <div class="mt-8 flex space-x-3">
            <a href="{{ route('admin.projectActivity.edit', [$projectId, $fiscalYearId]) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                {{ trans('global.edit') }}
            </a>
            <a href="{{ route('admin.projectActivity.destroy', [$projectId, $fiscalYearId]) }}"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
                onclick="return confirm('{{ trans('global.areYouSure') }}')">
                {{ trans('global.delete') }}
            </a>
        </div>
    </div>

    @push('styles')
        <style>
            .projectActivity-row[data-depth="1"] td:nth-child(2) {
                padding-left: 20px;
            }

            .projectActivity-row[data-depth="2"] td:nth-child(2) {
                padding-left: 40px;
            }

            .projectActivity-table {
                table-layout: fixed;
            }

            .projectActivity-table th:nth-child(1),
            .projectActivity-table td:nth-child(1) {
                position: sticky;
                left: 0;
                z-index: 20;
                background-color: inherit;
                border-right: 1px solid #d1d5db;
                width: 3rem;
            }

            .projectActivity-table th:nth-child(2),
            .projectActivity-table td:nth-child(2) {
                position: sticky;
                left: 3rem;
                z-index: 20;
                background-color: inherit;
                border-right: 1px solid #d1d5db;
                width: 20rem;
            }

            .dark .projectActivity-table th:nth-child(1),
            .dark .projectActivity-table td:nth-child(1),
            .dark .projectActivity-table th:nth-child(2),
            .dark .projectActivity-table td:nth-child(2) {
                border-right-color: #4b5563;
            }

            .projectActivity-table th:nth-child(1),
            .projectActivity-table td:nth-child(1),
            .projectActivity-table th:nth-child(2),
            .projectActivity-table td:nth-child(2) {
                box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
            }

            .dark .projectActivity-table th:nth-child(1),
            .dark .projectActivity-table td:nth-child(1),
            .dark .projectActivity-table th:nth-child(2),
            .dark .projectActivity-table td:nth-child(2) {
                box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.3);
            }

            /* Sticky backgrounds for special rows */
            .projectActivity-total-row.bg-blue-50 td:nth-child(1),
            .projectActivity-total-row.bg-blue-50 td:nth-child(2) {
                background-color: #eff6ff;
            }

            .projectActivity-total-row.bg-yellow-50 td:nth-child(1),
            .projectActivity-total-row.bg-yellow-50 td:nth-child(2) {
                background-color: #fefce8;
            }

            .projectActivity-row.bg-gray-50 td:nth-child(1),
            .projectActivity-row.bg-gray-50 td:nth-child(2) {
                background-color: #f9fafb;
            }

            .dark .projectActivity-total-row.bg-blue-900\/30 td:nth-child(1),
            .dark .projectActivity-total-row.bg-blue-900\/30 td:nth-child(2) {
                background-color: rgba(30, 58, 138, 0.3);
            }

            .dark .projectActivity-total-row.bg-yellow-900\/30 td:nth-child(1),
            .dark .projectActivity-total-row.bg-yellow-900\/30 td:nth-child(2) {
                background-color: rgba(133, 77, 14, 0.3);
            }

            .dark .projectActivity-row.bg-gray-700\/50 td:nth-child(1),
            .dark .projectActivity-row.bg-gray-700\/50 td:nth-child(2) {
                background-color: rgba(55, 65, 81, 0.5);
            }

            /* Header sticky */
            .projectActivity-table thead th:nth-child(1),
            .projectActivity-table thead th:nth-child(2) {
                z-index: 30;
                background-color: #e5e7eb;
            }

            .dark .projectActivity-table thead th:nth-child(1),
            .dark .projectActivity-table thead th:nth-child(2) {
                background-color: #374151;
            }
        </style>
    @endpush
</x-layouts.app>
