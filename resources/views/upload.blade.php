@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    
    {{-- Header/Navbar --}}
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-5 px-8 flex justify-between items-center">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-12 h-12">
            </div>

            {{-- Judul Tengah (Tata Kata) --}}
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            {{-- User Menu --}}
            <div class="flex items-center gap-4">
                {{-- Profile --}}
                <a href="{{ route('profile.edit') }}" class="relative flex items-center group">
                    <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-9 h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        Keluar
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </form>
            </div>
        </div>
    </header>

    {{-- Background --}}
    <div class="relative w-full h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden">

        {{-- Semburan pink lembut kanan atas --}}
        <div class="absolute -top-48 -right-48 w-[900px] h-[900px] 
                    bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 
                    rounded-full blur-[220px] pointer-events-none"></div>

        {{-- Semburan putih glossy kiri bawah --}}
        <div class="absolute -bottom-20 -left-20 w-[550px] h-[550px] bg-white/50 rounded-full blur-[150px] pointer-events-none"></div>

        {{-- Elemen 1 (dikecilkan dan mepet kanan) --}}
        <div class="absolute top-0 right-0 w-[320px] h-[320px] opacity-30 pointer-events-none">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain object-right">
        </div>

        {{-- Elemen 2 (mepet dasar layar bawah) --}}
        <div class="absolute bottom-0 left-0 w-[550px] h-[550px] opacity-30 pointer-events-none -translate-x-8 mb-[-2px]">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        {{-- Konten Utama --}}
        <main class="relative z-10 flex flex-col justify-center items-center h-full px-6 sm:px-8 lg:px-12">

            {{-- Breadcrumb --}}
            <a href="{{ route('dashboard') }}" class="absolute top-8 left-12 text-3xl font-semibold text-[#1a1a2e]/80 hover:underline">
                Beranda
            </a>

            {{-- Judul Halaman --}}
            <h1 class="text-4xl md:text-5xl font-bold text-[#1a1a2e] mb-10 text-center">
                Unggah Dokumen
            </h1>

            {{-- Kotak Upload --}}
            <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] rounded-[2rem] p-10 md:p-12 shadow-2xl w-full max-w-3xl text-center border-[2px] border-[#2a3a5a]">
                <form id="document-upload" method="POST" action="{{ route('upload.post') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- Nama Dokumen --}}
                    <input type="text" 
                           name="document_name" 
                           id="document_name"
                           placeholder="Nama Dokumen"
                           required
                           class="w-full px-6 py-4 bg-gray-100 text-gray-900 rounded-2xl text-lg focus:outline-none focus:ring-4 focus:ring-indigo-300 placeholder-gray-500"
                           value="{{ old('document_name') }}">
                    @error('document_name')
                        <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    {{-- Area Upload --}}
                    <div class="bg-gray-100 rounded-2xl p-10 text-center relative">
                        <input type="file" 
                               name="file" 
                               id="document"
                               accept=".pdf,.doc,.docx"
                               required
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                               onchange="updateFileName(this)">
                        
                        <div class="pointer-events-none">
                            <svg class="w-20 h-20 mx-auto text-gray-900 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <p class="text-gray-600 text-lg" id="file-label">Tarik & lepas dokumen disini</p>
                            <p class="text-gray-500 text-sm mt-2">atau klik untuk memilih file</p>
                            <p class="text-gray-400 text-xs mt-2">Format: PDF, DOC, DOCX (Max: 10MB)</p>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tombol di bawah kotak sejajar kiri-kanan --}}
            <div class="flex flex-col sm:flex-row gap-6 justify-between items-center mt-8 w-full max-w-3xl px-2">
                <a href="{{ route('dashboard') }}" 
                   class="px-10 py-3 bg-white text-gray-900 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all duration-200 shadow-lg text-center ml-[-10px]">
                    Batal
                </a>
                
                <button type="submit" form="document-upload" 
                        class="px-10 py-3 bg-white text-gray-900 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all duration-200 shadow-lg mr-[-10px]">
                    Unggah dan periksa
                </button>
            </div>

            {{-- Pesan Notifikasi --}}
            @if(session('success'))
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-md">
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-md">
                    {{ session('error') }}
                </div>
            </div>
            @endif

        </main>
    </div>
</div>

<script>
function updateFileName(input) {
    const label = document.getElementById('file-label');
    label.textContent = input.files && input.files[0] ? input.files[0].name : 'Tarik & lepas dokumen disini';
}
</script>
@endsection
