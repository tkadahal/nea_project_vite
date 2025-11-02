<tr class="projectExpense-total-row bg-blue-50 dark:bg-blue-900/30 border-t-2 border-blue-300 dark:border-blue-600">
    <td
        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm font-bold text-blue-700 dark:text-blue-300">
    </td>
    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-bold text-blue-700 dark:text-blue-300"
        style="padding-left: {{ ($depth + 1) * 20 }}px;">
        Total of {{ $number }}
    </td>
    @foreach ([1, 2, 3, 4] as $q)
        <td
            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-bold text-blue-700 dark:text-blue-300">
            {{ number_format($subtreeQuarterTotals[$activityId]['q' . $q] ?? 0, 2) }}
        </td>
    @endforeach
</tr>
