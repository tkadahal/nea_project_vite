<x-layouts.app>
    <div class="mb-6">
        <div class="flex justify-between items-start mb-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ trans('global.projectActivity.title') }} - {{ $project->title }} -
                {{ $fiscalYear->title ?? $fiscalYear->id }}
            </h1>
            <a href="{{ route('admin.projectActivity.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            Detailed breakdown of Annual Program for Fiscal Year {{ $fiscalYear->title }}.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">

        @include('admin.project-activities.partials.activity-table', [
            'activities' => $capitalActivities,
            'header' => trans('global.projectActivity.headers.capital'),
        ])

        @include('admin.project-activities.partials.activity-table', [
            'activities' => $recurrentActivities,
            'header' => trans('global.projectActivity.headers.recurrent'),
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
        </style>
    @endpush
</x-layouts.app>
