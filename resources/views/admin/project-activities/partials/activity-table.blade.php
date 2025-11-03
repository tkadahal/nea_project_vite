{{-- resources/views/admin/project-activities/partials/activity-table.blade.php --}}
<div class="mb-8">
    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
        <h3
            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
            {{ $header }}
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
                    @foreach ($activities->whereNull('parent_id') as $topActivity)
                        @php
                            $topLevel++;
                            $hasTopChildren = $topActivity->children->isNotEmpty();
                            $topBgClass = $hasTopChildren ? 'bg-gray-50 dark:bg-gray-700/50' : '';
                            $topTotalBudget = $hasTopChildren ? 0 : $topActivity->total_budget ?? 0;
                            $topTotalExpense = $hasTopChildren ? 0 : $topActivity->total_expense ?? 0;
                            $topPlanned = $hasTopChildren ? 0 : $topActivity->planned_budget ?? 0;
                            $topQ1 = $hasTopChildren ? 0 : $topActivity->q1 ?? 0;
                            $topQ2 = $hasTopChildren ? 0 : $topActivity->q2 ?? 0;
                            $topQ3 = $hasTopChildren ? 0 : $topActivity->q3 ?? 0;
                            $topQ4 = $hasTopChildren ? 0 : $topActivity->q4 ?? 0;
                        @endphp
                        <tr class="projectActivity-row {{ $topBgClass }}" data-depth="0">
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                {{ $topLevel }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1"
                                style="padding-left: {{ 0 * 20 }}px;">
                                <span
                                    class="font-bold text-gray-900 dark:text-gray-100">{{ $topActivity->program }}</span>
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topTotalBudget, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topTotalExpense, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topPlanned, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topQ1, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topQ2, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topQ3, 2) }}
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($topQ4, 2) }}
                            </td>
                        </tr>
                        @if ($hasTopChildren)
                            @include('admin.project-activities.partials.hierarchy-rows', [
                                'parentActivity' => $topActivity,
                                'depth' => 1,
                                'numberPrefix' => $topLevel,
                            ])
                            @include('admin.project-activities.partials.totals-row', [
                                'depth' => 0,
                                'number' => $topLevel,
                                'parentActivity' => $topActivity,
                            ])
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @php $sectionTotal = $activities->whereNull('parent_id')->sum('total_budget'); @endphp
        <div class="mt-4 text-lg font-bold">
            Total {{ $header }} Budget: {{ number_format($sectionTotal, 2) }}
        </div>
    </div>
</div>
