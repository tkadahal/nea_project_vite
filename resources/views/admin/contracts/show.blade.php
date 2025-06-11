<x-layouts.app>
    <!-- Page Title and Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Contract Details') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Details for:') }} <span
                    class="font-semibold">{{ $contract->title }}</span></p>
        </div>
        <a href="{{ route('admin.contract.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            {{ __('Back to Contracts') }}
        </a>
    </div>

    <!-- Contract Details Card -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Contract Title --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Title:') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $contract->title }}</p>
            </div>

            {{-- Directorate --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Directorate:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->directorate->title ?? 'N/A' }}
                </p>
            </div>

            {{-- Project --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Project:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->project->title ?? 'N/A' }}</p>
            </div>

            {{-- Contractor --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Contractor:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->contractor ?? 'N/A' }}</p>
            </div>

            {{-- Contract Amount --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Contract Amount:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ number_format($contract->contract_amount, 2) }}</p>
            </div>

            {{-- Contract Variation Amount --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Variation Amount:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ number_format($contract->contract_variation_amount, 2) }}</p>
            </div>

            {{-- Initial Contract Period (Days) --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Contract Period (Days):') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->initial_contract_period ?? 'N/A' }}</p>
            </div>

            {{-- Progress --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Progress:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->progress ?? 'N/A' }}%</p>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->status->title ?? 'N/A' }}</p>
            </div>

            {{-- Priority --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Priority:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->priority->title ?? 'N/A' }}</p>
            </div>

            {{-- Agreement Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Agreement Date:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->contract_agreement_date ? $contract->contract_agreement_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>

            {{-- Effective Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Effective Date:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->agreement_effective_date ? $contract->agreement_effective_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>

            {{-- Completion Date --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Completion Date:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->agreement_completion_date ? $contract->agreement_completion_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>

            {{-- Description (full width) --}}
            <div class="col-span-full">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $contract->description ?? 'N/A' }}</p>
            </div>

            {{-- Created At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->created_at->format('M d, Y H:i A') }}</p>
            </div>

            {{-- Updated At --}}
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Updated At:') }}</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                    {{ $contract->updated_at->format('M d, Y H:i A') }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex space-x-3">
            <a href="{{ route('admin.contract.edit', $contract) }}"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600
                      focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                      dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-offset-gray-900">
                {{ __('Edit Contract') }}
            </a>

            <form action="{{ route('admin.contract.destroy', $contract) }}" method="POST"
                onsubmit="return confirm('{{ __('Are you sure you want to delete this contract? This action cannot be undone.') }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                               dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-offset-gray-900">
                    {{ __('Delete Contract') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
