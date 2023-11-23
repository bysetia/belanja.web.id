<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Store') }}
        </h2>
    </x-slot>

    <x-slot name="script">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                var datatable = $('#crudTable').DataTable({
                    ajax: {
                        url: '{!! url()->current() !!}',
                    },
                    columns: [
                        { data: 'id', name: 'id', width: '5%' },
                        { data: 'logo', name: 'logo' },
                        { data: 'name', name: 'name' },
                        { data: 'user.name', name: 'user.name' },
                        { data: 'followers', name: 'followers' },
                        { data: 'rate', name: 'rate' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, width: '25%' },
                    ],
                    // Tambahan: Mengatur bahasa DataTables ke Bahasa Indonesia
                    // language: {
                    //     "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/Indonesian.json"
                    // }
                });
            });
        </script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <table id="crudTable" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Author</th>
                                <th>Follower</th>
                                <th>Rate</th>
                                <th>Created_at</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
