<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Your Calendar</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200 dark:border-gray-700">
        <div id="calendar" class="w-full max-w-full"></div>
    </div>

    <!-- Modal -->
    <div id="addEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden" aria-hidden="true">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Add Event</h2>
                <form id="addEventForm" method="POST" action="{{ route('admin.calendar.store') }}">
                    @csrf
                    <div class="mb-4">
                        <x-forms.input label="Title" name="title" type="text" :value="old('title')"
                            :error="$errors->first('title')" />
                    </div>
                    <div class="mb-4">
                        <x-forms.text-area label="Description" name="description" type="text" :value="old('description')"
                            :error="$errors->first('description')" />
                    </div>
                    <div class="mb-4">
                        <x-forms.datetime-input label="Start Time" name="start_time" type="datetime-local"
                            :value="old('start_time')" :error="$errors->first('start_time')" />
                    </div>
                    <div class="mb-4">
                        <x-forms.datetime-input label="End Time" name="end_time" type="datetime-local" :value="old('end_time')"
                            :error="$errors->first('end_time')" />
                    </div>
                    <div class="mb-4">
                        <x-forms.checkbox label="Set as Reminder" name="is_reminder" />
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="closeModal"
                            class="mr-2 px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md text-gray-800 dark:text-gray-200">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-4 text-green-600 dark:text-green-400">{{ session('success') }}</div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: @json($events),
                    contentHeight: 'auto',
                    aspectRatio: isSidebarVisible() ? 1.35 : 1.8, // Initial aspect ratio based on sidebar
                    dayMaxEvents: true,
                    dateClick: function(info) {
                        document.getElementById('addEventModal').classList.remove('hidden');
                        document.getElementById('start_time').value = info.dateStr + 'T00:00';
                        document.body.classList.add('modal-open');
                    }
                });
                calendar.render();

                // Function to check if sidebar is visible (adjust class name as per your app)
                function isSidebarVisible() {
                    return !document.body.classList.contains('sidebar-hidden');
                }

                // Listen for sidebar toggle (example event, adjust to your toggle mechanism)
                document.body.addEventListener('sidebarToggle', function() {
                    calendar.setOption('aspectRatio', isSidebarVisible() ? 1.35 : 1.8);
                    calendar.render(); // Re-render to apply changes
                });

                // Fallback: Listen for resize in case sidebar affects layout indirectly
                window.addEventListener('resize', function() {
                    calendar.setOption('aspectRatio', isSidebarVisible() ? 1.35 : 1.8);
                    calendar.render();
                });

                document.getElementById('closeModal').addEventListener('click', function() {
                    document.getElementById('addEventModal').classList.add('hidden');
                    document.getElementById('addEventForm').reset();
                    document.body.classList.remove('modal-open');
                });
            });
        </script>
    @endpush
</x-layouts.app>
