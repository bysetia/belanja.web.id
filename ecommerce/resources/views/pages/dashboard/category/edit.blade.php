<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Category &raquo; {{ $item->name }} &raquo; Edit
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
                <form class="w-full" action="{{ route('dashboard.category.update', $item->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2" for="grid-last-name">
                                Name
                            </label>
                            <input value="{{ old('name') ?? $item->name }}" name="name" class="appearance-none block w-full bg-white text-gray-700 border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-last-name" type="text" placeholder="Nama Kategori">
                        </div>
                    </div>
                     <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2" for="grid-image">
                                Logo
                            </label>
                            <input name="photo" type="file" id="grid-image">
                        </div>
                    </div>
                       <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2" for="grid-image">
                                Banner
                            </label>
                            <input name="banner" type="file" id="grid-image">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide text-black text-xs font-bold mb-2" for="grid-last-name">
                                Slug
                            </label>
                            <input value="{{ old('slug') }}" name="slug" class="appearance-none block w-full bg-white text-black border border-black-400 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-last-name" type="text" placeholder="slug">
                        </div>
                    </div>
                  
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3 text-right">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                               Update Category
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>