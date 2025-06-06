<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Users</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage Users</p>
        </div>
        <a href="{{ route('admin.user.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                  dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-900">
            Add New
        </a>
    </div>

    <div class="mb-6">
        <x-table.dataTable :headers="$headers" :data="$data" :routePrefix="$routePrefix" :actions="$actions" :deleteConfirmationMessage="$deleteConfirmationMessage" />
    </div>
</x-layouts.app>
