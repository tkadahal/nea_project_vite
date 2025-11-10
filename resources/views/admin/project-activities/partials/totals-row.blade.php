{{-- resources/views/admin/project-activities/partials/totals-row.blade.php --}}
@php
    $total_budget = 0;
    $total_expense = 0;
    $planned = 0;
    $q1 = 0;
    $q2 = 0;
    $q3 = 0;
    $q4 = 0;

    foreach ($parentActivity->children as $child) {
        $child_total_budget = $child->children->isNotEmpty()
            ? $child->children->sum('total_budget') ?? 0
            : $child->total_budget ?? 0;
        $child_total_expense = $child->children->isNotEmpty()
            ? $child->children->sum('total_expense') ?? 0
            : $child->total_expense ?? 0;
        $child_planned = $child->children->isNotEmpty()
            ? $child->children->sum('planned_budget') ?? 0
            : $child->planned_budget ?? 0;
        $child_q1 = $child->children->isNotEmpty() ? $child->children->sum('q1') ?? 0 : $child->q1 ?? 0;
        $child_q2 = $child->children->isNotEmpty() ? $child->children->sum('q2') ?? 0 : $child->q2 ?? 0;
        $child_q3 = $child->children->isNotEmpty() ? $child->children->sum('q3') ?? 0 : $child->q3 ?? 0;
        $child_q4 = $child->children->isNotEmpty() ? $child->children->sum('q4') ?? 0 : $child->q4 ?? 0;

        $total_budget += $child_total_budget;
        $total_expense += $child_total_expense;
        $planned += $child_planned;
        $q1 += $child_q1;
        $q2 += $child_q2;
        $q3 += $child_q3;
        $q4 += $child_q4;
    }
@endphp
<tr class="projectActivity-total-row bg-blue-50 dark:bg-blue-900/30 border-t-2 border-blue-300 dark:border-blue-600"
    data-depth="{{ $depth }}">
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-bold text-blue-700 dark:text-blue-300"
        style="padding-left: {{ ($depth + 1) * 20 }}px;">
        Total of {{ $number }}
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($total_budget, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($total_expense, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($planned, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($q1, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($q2, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($q3, 2) }}</td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
        {{ number_format($q4, 2) }}</td>
</tr>
