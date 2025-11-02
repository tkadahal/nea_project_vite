{{-- resources/views/admin/project-activities/show.blade.php --}}
<x-layouts.app>
    <div class="mb-6">
        <div class="flex justify-between items-start mb-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $project->title }} - {{ $fiscalYear->name ?? $fiscalYear->id }} -
                {{ trans('global.projectActivity.title') }}
            </h1>
            <a href="{{ route('admin.projectActivity.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.view') }} {{ trans('global.projectActivity.title_singular') }}
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">

        <!-- Capital Expenditure Section -->
        <div class="mb-8">
            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ trans('global.projectActivity.headers.capital') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-600">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    {{ trans('global.projectActivity.fields.program') }}
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Total Budget
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Expenses Till Date
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Planned Budget of this F/Y
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $topLevel = 0; @endphp
                            @foreach ($capitalActivities->whereNull('parent_id') as $topActivity)
                                @php
                                    $topLevel++;
                                    $level1 = 0;
                                @endphp
                                <tr class="projectActivity-row" data-depth="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{ $topLevel }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-700 dark:text-gray-200">
                                        {{ $topActivity->program }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->total_budget ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->total_expense ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->planned_budget ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q1 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q2 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q3 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q4 ?? 0, 2) }}
                                    </td>
                                </tr>

                                @foreach ($topActivity->children as $level1Activity)
                                    @php
                                        $level1++;
                                        $level2 = 0;
                                    @endphp
                                    <tr class="projectActivity-row" data-depth="1">
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                            {{ $topLevel }}.{{ $level1 }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 pl-5 text-gray-700 dark:text-gray-200">
                                            {{ $level1Activity->program }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->total_budget ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->total_expense ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->planned_budget ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q1 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q2 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q3 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q4 ?? 0, 2) }}
                                        </td>
                                    </tr>

                                    @foreach ($level1Activity->children as $level2Activity)
                                        @php $level2++; @endphp
                                        <tr class="projectActivity-row" data-depth="2">
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                                {{ $topLevel }}.{{ $level1 }}.{{ $level2 }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 pl-10 text-gray-700 dark:text-gray-200">
                                                {{ $level2Activity->program }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->total_budget ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->total_expense ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->planned_budget ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q1 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q2 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q3 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q4 ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php $capitalTotal = $capitalActivities->whereNull('parent_id')->sum('total_budget'); @endphp
                <div class="mt-4 text-lg font-bold">
                    Total Capital Budget: {{ number_format($capitalTotal, 2) }}
                </div>
            </div>
        </div>

        <!-- Recurrent Expenditure Section -->
        <div class="mb-8">
            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ trans('global.projectActivity.headers.recurrent') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-600">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    {{ trans('global.projectActivity.fields.program') }}
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Total Budget
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Expenses Till Date
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Planned Budget of this F/Y
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $topLevel = 0; @endphp
                            @foreach ($recurrentActivities->whereNull('parent_id') as $topActivity)
                                @php
                                    $topLevel++;
                                    $level1 = 0;
                                @endphp
                                <tr class="projectActivity-row" data-depth="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{ $topLevel }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-700 dark:text-gray-200">
                                        {{ $topActivity->program }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->total_budget ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->total_expense ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->planned_budget ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q1 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q2 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q3 ?? 0, 2) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                        {{ number_format($topActivity->q4 ?? 0, 2) }}
                                    </td>
                                </tr>

                                @foreach ($topActivity->children as $level1Activity)
                                    @php
                                        $level1++;
                                        $level2 = 0;
                                    @endphp
                                    <tr class="projectActivity-row" data-depth="1">
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                            {{ $topLevel }}.{{ $level1 }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 pl-5 text-gray-700 dark:text-gray-200">
                                            {{ $level1Activity->program }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->total_budget ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->total_expense ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->planned_budget ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q1 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q2 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q3 ?? 0, 2) }}
                                        </td>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ number_format($level1Activity->q4 ?? 0, 2) }}
                                        </td>
                                    </tr>

                                    @foreach ($level1Activity->children as $level2Activity)
                                        @php $level2++; @endphp
                                        <tr class="projectActivity-row" data-depth="2">
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                                {{ $topLevel }}.{{ $level1 }}.{{ $level2 }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 pl-10 text-gray-700 dark:text-gray-200">
                                                {{ $level2Activity->program }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->total_budget ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->total_expense ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->planned_budget ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q1 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q2 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q3 ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                                {{ number_format($level2Activity->q4 ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php $recurrentTotal = $recurrentActivities->whereNull('parent_id')->sum('total_budget'); @endphp
                <div class="mt-4 text-lg font-bold">
                    Total Recurrent Budget: {{ number_format($recurrentTotal, 2) }}
                </div>
            </div>
        </div>

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
