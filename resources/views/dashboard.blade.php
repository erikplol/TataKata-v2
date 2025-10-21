@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    
    {{-- Header/Navbar dengan gradasi --}}
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-5 px-8 flex justify-between items-center">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-12 h-12">
            </div>

            {{-- Judul Tengah (Ditambahkan untuk konsistensi) --}}
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            {{-- User Menu --}}
            <div class="flex items-center gap-4">
                {{-- Profile Icon dengan tooltip --}}
                <a class="relative flex items-center group">
                    <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-9 h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        Keluar
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </form>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="relative w-full h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden">
        
        {{-- Efek glossy & Semburan Background (Dipotong untuk brevity) --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.5)_0%,rgba(255,255,255,0)_55%)]"></div>
        <div class="absolute -top-48 -right-48 w-[900px] h-[900px] bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 rounded-full blur-[220px] pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-[700px] h-[700px] bg-gradient-to-tr from-[#FFD6E0]/40 via-[#FFE0EB]/35 to-[#FFF0F3]/25 rounded-full blur-[200px] pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-[550px] h-[550px] bg-white/50 rounded-full blur-[150px] pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-[450px] h-[450px] bg-white/35 rounded-full blur-[130px] pointer-events-none"></div>
        <div class="absolute -bottom-28 -right-28 w-[600px] h-[600px] bg-[#E0E7FF]/45 rounded-full blur-[160px] pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-[450px] h-[450px] bg-[#D6E0FF]/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[750px] h-[420px] bg-[#F8F9FF]/45 rounded-full blur-[170px] pointer-events-none"></div>
        
        {{-- Elemen 1 & 2 --}}
        <div class="absolute -top-12 -right-12 w-[350px] h-[350px] opacity-35 pointer-events-none" 
             style="filter: brightness(1.1) saturate(0.9) contrast(1.05) drop-shadow(0 0 6px rgba(200,200,220,0.3));">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain">
        </div>
        <div class="absolute bottom-4 left-0 w-[550px] h-[550px] opacity-30 pointer-events-none -translate-x-8" 
             style="filter: brightness(1.15) saturate(0.85) contrast(1.08) drop-shadow(0 0 10px rgba(200,200,220,0.35));">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        {{-- Content Area --}}
        <div class="relative z-10 h-full flex flex-col p-12">

            {{-- Beranda --}}
            <div class="absolute top-8 left-12">
                <h3 class="text-2xl font-semibold text-[#1a1a2e]/80">Beranda</h3>
            </div>
            
            {{-- Welcome Section --}}
            <div class="mt-20 text-center">
                <h1 class="text-5xl font-bold text-[#1a1a2e] leading-tight">
                    Selamat datang di,<br>
                    <span class="text-5xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent">Tata Kata.</span>
                </h1>
            </div>

            {{-- Cards Section (Diubah menjadi 3 kartu) --}}
            <div class="flex gap-10 justify-center items-center flex-1">
                
                {{-- Card 1: Unggah Dokumen --}}
                <a href="{{ route('upload') }}" class="group">
                    <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] 
                                rounded-[2rem] p-12 shadow-xl hover:shadow-2xl transition-all duration-300 
                                hover:scale-105 w-64 h-64 flex flex-col items-center justify-center 
                                text-center border-[2px] border-[#2a3a5a]">
                        
                        <h2 class="text-2xl font-bold text-white mb-6">
                            Unggah<br>Dokumen
                        </h2>
                        
                        <img src="{{ asset('images/ikonplus.png') }}" alt="Ikon Plus" 
                             class="w-14 h-14 mt-1 transition-transform duration-200 group-hover:scale-110">
                    </div>
                </a>

                {{-- Card 2: Riwayat --}}
                <a href="{{ route('history') }}" class="group">
                    <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] 
                                rounded-[2rem] p-12 shadow-xl hover:shadow-2xl transition-all duration-300 
                                hover:scale-105 w-64 h-64 flex flex-col items-center justify-center 
                                text-center border-[2px] border-[#2a3a5a]">
                        
                        <h2 class="text-2xl font-bold text-white mb-6">
                            Riwayat
                        </h2>

                        <img src="{{ asset('images/ikonriwayat.png') }}" alt="Ikon Riwayat" 
                             class="w-14 h-14 mt-1 transition-transform duration-200 group-hover:scale-110">
                    </div>
                </a>
            </div>

            {{-- Success Message --}}
            @if(session('success'))
            <div class="mt-8">
                <div class="bg-green-100 border-2 border-green-400 text-green-700 px-6 py-4 rounded-xl shadow-md">
                    {{ session('success') }}
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection