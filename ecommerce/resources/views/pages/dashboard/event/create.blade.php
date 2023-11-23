<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Event &raquo; Create') !!}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div>
                @if ($errors->any())
                    <div class="mb-5" role="alert">
                        <div class="bg-red-500 text-white font-bold rounded-t px-4 py-2">
                            There's something wrong!
                        </div>
                        <div class="border border-t-0 border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700">
                            <p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            </p>
                        </div>
                    </div>
                @endif
                <form class="w-full" action="{{ route('dashboard.event.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Name
                            </label>
                            <input value="{{ old('name') }}" name="name"
                                class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Name">
                        </div>
                    </div>
                     <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                   for="poster">
                                Poster
                            </label>
                            <input name="poster" type="file" accept="image/*" required>
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Title
                            </label>
                            <input value="{{ old('title') }}" name="title"
                                class="appearance-none block w-full bg-white black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Title">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Description
                            </label>
                            <textarea name="description"
                                class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Description">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Date
                            </label>
                            <input value="{{ old('date') }}" name="date"
                                class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Date">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Time
                            </label>
                            <input value="{{ old('time') }}" name="time"
                                class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Time">
                        </div>
                    </div>

                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2"
                                for="grid-last-name">
                                Location
                            </label>
                            <input value="{{ old('location') }}" name="location"
                                class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="grid-last-name" type="text" placeholder="Event Location">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3 text-right">
                            <button type="submit"
                                class=" shadow-lg bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Add Event
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
