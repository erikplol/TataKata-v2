@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    
    {{-- Header/Navbar --}}
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-3 sm:py-5 px-4 sm:px-8 flex justify-between items-center">

            {{-- Logo --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12">
            </div>

            {{-- Judul Tengah (Tata Kata) --}}
            <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            {{-- User Menu --}}
            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Profile --}}
                <a href="{{ route('profile.edit') }}" class="relative flex items-center group">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="hidden sm:block absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                    <div class="hidden sm:block absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        Keluar
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </form>
            </div>
        </div>
    </header>

    {{-- Background --}}
    <div class="relative w-full min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-76px)] md:min-h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden">

        {{-- Semburan pink lembut kanan atas --}}
        <div class="absolute -top-24 sm:-top-32 md:-top-48 -right-24 sm:-right-32 md:-right-48 w-[400px] sm:w-[600px] md:w-[900px] h-[400px] sm:h-[600px] md:h-[900px] 
                    bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 
                    rounded-full blur-[120px] sm:blur-[180px] md:blur-[220px] pointer-events-none"></div>

        {{-- Semburan putih glossy kiri bawah --}}
        <div class="absolute -bottom-12 sm:-bottom-16 md:-bottom-20 -left-12 sm:-left-16 md:-left-20 w-[300px] sm:w-[400px] md:w-[550px] h-[300px] sm:h-[400px] md:h-[550px] bg-white/50 rounded-full blur-[80px] sm:blur-[120px] md:blur-[150px] pointer-events-none"></div>

        {{-- Elemen 1 (dikecilkan dan mepet kanan) --}}
        <div class="absolute top-0 right-0 w-[150px] sm:w-[220px] md:w-[280px] lg:w-[320px] h-[150px] sm:h-[220px] md:h-[280px] lg:h-[320px] opacity-20 sm:opacity-25 md:opacity-30 pointer-events-none">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain object-right">
        </div>

        {{-- Elemen 2 (mepet dasar layar bawah) --}}
        <div class="absolute bottom-0 left-0 w-[250px] sm:w-[350px] md:w-[450px] lg:w-[550px] h-[250px] sm:h-[350px] md:h-[450px] lg:h-[550px] opacity-20 sm:opacity-25 md:opacity-30 pointer-events-none -translate-x-4 sm:-translate-x-6 md:-translate-x-8 mb-[-2px]">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        {{-- Konten Utama --}}
        <main class="relative z-10 flex flex-col justify-center items-center min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-76px)] md:min-h-[calc(100vh-88px)] px-4 sm:px-6 md:px-8 lg:px-12 py-6 sm:py-8">

            {{-- Breadcrumb --}}
            <a href="{{ route('dashboard') }}" class="absolute top-4 sm:top-6 md:top-8 left-4 sm:left-8 md:left-12 text-base sm:text-xl md:text-2xl lg:text-3xl font-semibold text-[#1a1a2e]/80 hover:underline">
                ← Beranda
            </a>

            {{-- Judul Halaman --}}
            <h1 class="text-xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-[#1a1a2e] mb-6 sm:mb-8 md:mb-10 text-center mt-12 sm:mt-8 md:mt-0">
                Unggah Dokumen
            </h1>

            {{-- Kotak Upload --}}
            <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] rounded-2xl sm:rounded-3xl md:rounded-[2rem] p-6 sm:p-8 md:p-10 lg:p-12 shadow-2xl w-full max-w-3xl text-center border-[2px] border-[#2a3a5a]">
                <form id="document-upload" method="POST" action="{{ route('upload.post') }}" enctype="multipart/form-data" class="space-y-5 sm:space-y-6 md:space-y-8">
                    @csrf

                    {{-- Nama Dokumen --}}
                    <input type="text" 
                           name="document_name" 
                           id="document_name"
                           placeholder="Nama Dokumen"
                           required
                           class="w-full px-4 sm:px-5 md:px-6 py-2.5 sm:py-3.5 md:py-4 bg-gray-100 text-gray-900 rounded-xl sm:rounded-2xl text-sm sm:text-lg focus:outline-none focus:ring-4 focus:ring-indigo-300 placeholder-gray-500"
                           value="{{ old('document_name') }}">
                    @error('document_name')
                        <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    {{-- Area Upload --}}
                    <div class="bg-gray-100 rounded-xl sm:rounded-2xl p-6 sm:p-8 md:p-10 text-center relative">
                        <input type="file" 
                               name="file" 
                               id="document"
                               accept=".pdf,.doc,.docx"
                               required
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                               onchange="updateFileName(this)">
                        
                        {{-- Default State (No File) --}}
                        <div id="upload-default" class="pointer-events-none">
                            <svg class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 mx-auto text-gray-900 mb-4 sm:mb-5 md:mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <p class="text-gray-600 text-base sm:text-lg font-medium">Tarik & lepas dokumen disini</p>
                            <p class="text-gray-500 text-sm mt-2">atau klik untuk memilih file</p>
                            <p class="text-gray-400 text-xs mt-2">Format: PDF, DOC, DOCX (Max: 10MB)</p>
                        </div>

                        {{-- Uploaded State (With File) --}}
                        <div id="upload-success" class="pointer-events-none hidden">
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 w-16 h-20 sm:w-20 sm:h-24 md:w-24 md:h-28 mx-auto rounded-lg shadow-lg mb-4 sm:mb-5 md:mb-6 flex items-center justify-center relative">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                                {{-- Checkmark Badge --}}
                                <div class="absolute -top-2 -right-2 bg-green-500 rounded-full w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center shadow-lg">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-gray-700 text-base sm:text-lg font-semibold mb-2" id="file-name">Dokumen.pdf</p>
                            <p class="text-gray-500 text-sm">Klik untuk mengganti file</p>
                            <div class="mt-3 flex items-center justify-center gap-2 text-green-600">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs sm:text-sm font-medium">File siap diunggah</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tombol di bawah kotak --}}
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-between items-stretch sm:items-center mt-6 sm:mt-8 w-full max-w-3xl px-2">
                <a href="{{ route('dashboard') }}" 
                   class="w-full sm:w-auto px-8 sm:px-10 py-3 bg-white text-gray-900 rounded-full font-semibold text-base sm:text-lg hover:bg-gray-100 transition-all duration-200 shadow-lg text-center">
                    Batal
                </a>
                
                <button type="submit" form="document-upload" 
                        class="w-full sm:w-auto px-8 sm:px-10 py-3 bg-white text-gray-900 rounded-full font-semibold text-base sm:text-lg hover:bg-gray-100 transition-all duration-200 shadow-lg">
                    Unggah dan periksa
                </button>
            </div>

            {{-- Pesan Notifikasi --}}
            @if(session('success'))
            <div class="mt-6 sm:mt-8 w-full max-w-2xl px-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 sm:px-6 py-3 sm:py-4 rounded-lg shadow-md text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mt-6 sm:mt-8 w-full max-w-2xl px-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 sm:px-6 py-3 sm:py-4 rounded-lg shadow-md text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            </div>
            @endif

        </main>
    </div>
</div>

<script>
function updateFileName(input) {
    const defaultState = document.getElementById('upload-default');
    const successState = document.getElementById('upload-success');
    const fileNameDisplay = document.getElementById('file-name');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
        
        // Update file name display
        fileNameDisplay.textContent = fileName;
        
        // Show success state, hide default
        defaultState.classList.add('hidden');
        successState.classList.remove('hidden');
        
        // Add a subtle animation
        successState.classList.add('animate-fadeIn');
    } else {
        // Reset to default state
        defaultState.classList.remove('hidden');
        successState.classList.add('hidden');
    }
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection