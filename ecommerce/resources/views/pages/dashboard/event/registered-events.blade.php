<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registered Users for {{ $event->name }}
        </h2>
    </x-slot>

    <x-slot name="script">
        <script>
            $(document).ready(function () {
                $('#registeredUsersTable').DataTable({
                    // processing: true,
                    serverSide: true,
                    ajax: '{!! route('dashboard.event.registered-events', $event) !!}',
                    columns: [
                        { data: 'id', name: 'id', width: '5%' },
                        { data: 'profile_photo_path', name: 'profile_photo_path' },
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'phone', name: 'phone' },
                        { data: 'roles', name: 'roles' },
                    ],
                });
            });
        </script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <table id="registeredUsersTable" class="min-w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Hp</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
