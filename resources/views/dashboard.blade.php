@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    
    {{-- Header/Navbar dengan gradasi --}}
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-3 sm:py-4 lg:py-5 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            {{-- Logo --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12">
            </div>

            {{-- Judul Tengah --}}
            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            {{-- User Menu --}}
            <div class="flex items-center gap-2 sm:gap-3 lg:gap-4">
                {{-- Profile Icon dengan tooltip --}}
                <a class="relative flex items-center group">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-9 lg:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-xs sm:text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 lg:w-9 lg:h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-9 lg:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-xs sm:text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        Keluar
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </form>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="relative w-full min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-72px)] lg:h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden">
        
        {{-- Efek glossy & Semburan Background --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.5)_0%,rgba(255,255,255,0)_55%)]"></div>
        
        {{-- Background blurs - responsive sizes --}}
        <div class="absolute -top-24 sm:-top-36 lg:-top-48 -right-24 sm:-right-36 lg:-right-48 w-[450px] sm:w-[700px] lg:w-[900px] h-[450px] sm:h-[700px] lg:h-[900px] bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 rounded-full blur-[120px] sm:blur-[180px] lg:blur-[220px] pointer-events-none"></div>
        
        <div class="absolute top-0 right-0 w-[350px] sm:w-[550px] lg:w-[700px] h-[350px] sm:h-[550px] lg:h-[700px] bg-gradient-to-tr from-[#FFD6E0]/40 via-[#FFE0EB]/35 to-[#FFF0F3]/25 rounded-full blur-[100px] sm:blur-[150px] lg:blur-[200px] pointer-events-none"></div>
        
        <div class="absolute -bottom-10 sm:-bottom-16 lg:-bottom-20 -left-10 sm:-left-16 lg:-left-20 w-[280px] sm:w-[420px] lg:w-[550px] h-[280px] sm:h-[420px] lg:h-[550px] bg-white/50 rounded-full blur-[80px] sm:blur-[120px] lg:blur-[150px] pointer-events-none"></div>
        
        <div class="absolute bottom-5 sm:bottom-8 lg:bottom-10 left-5 sm:left-8 lg:left-10 w-[220px] sm:w-[340px] lg:w-[450px] h-[220px] sm:h-[340px] lg:h-[450px] bg-white/35 rounded-full blur-[70px] sm:blur-[100px] lg:blur-[130px] pointer-events-none"></div>
        
        <div class="absolute -bottom-14 sm:-bottom-20 lg:-bottom-28 -right-14 sm:-right-20 lg:-right-28 w-[300px] sm:w-[460px] lg:w-[600px] h-[300px] sm:h-[460px] lg:h-[600px] bg-[#E0E7FF]/45 rounded-full blur-[90px] sm:blur-[130px] lg:blur-[160px] pointer-events-none"></div>
        
        <div class="absolute bottom-5 sm:bottom-8 lg:bottom-10 right-5 sm:right-8 lg:right-10 w-[220px] sm:w-[340px] lg:w-[450px] h-[220px] sm:h-[340px] lg:h-[450px] bg-[#D6E0FF]/30 rounded-full blur-[70px] sm:blur-[100px] lg:blur-[120px] pointer-events-none"></div>
        
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[380px] sm:w-[580px] lg:w-[750px] h-[210px] sm:h-[320px] lg:h-[420px] bg-[#F8F9FF]/45 rounded-full blur-[90px] sm:blur-[130px] lg:blur-[170px] pointer-events-none"></div>
        
        {{-- Elemen Dekoratif - hidden di mobile kecil --}}
        <div class="hidden sm:block absolute -top-6 sm:-top-10 lg:-top-12 -right-6 sm:-right-10 lg:-right-12 w-[180px] sm:w-[280px] lg:w-[350px] h-[180px] sm:h-[280px] lg:h-[350px] opacity-25 sm:opacity-30 lg:opacity-35 pointer-events-none" 
             style="filter: brightness(1.1) saturate(0.9) contrast(1.05) drop-shadow(0 0 6px rgba(200,200,220,0.3));">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain">
        </div>
        
        <div class="hidden sm:block absolute bottom-2 sm:bottom-3 lg:bottom-4 left-0 w-[280px] sm:w-[420px] lg:w-[550px] h-[280px] sm:h-[420px] lg:h-[550px] opacity-20 sm:opacity-25 lg:opacity-30 pointer-events-none -translate-x-4 sm:-translate-x-6 lg:-translate-x-8" 
             style="filter: brightness(1.15) saturate(0.85) contrast(1.08) drop-shadow(0 0 10px rgba(200,200,220,0.35));">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        {{-- Content Area --}}
        <div class="relative z-10 h-full flex flex-col p-4 sm:p-6 md:p-8 lg:p-12">

            {{-- Beranda --}}
            <div class="mb-4 sm:mb-6 lg:mb-0 lg:absolute lg:top-8 lg:left-12">
                <h3 class="text-lg sm:text-xl lg:text-2xl font-semibold text-[#1a1a2e]/80">Beranda</h3>
            </div>
            
            {{-- Welcome Section --}}
            <div class="mt-4 sm:mt-8 lg:mt-20 text-center mb-6 sm:mb-8 lg:mb-0">
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-[#1a1a2e] leading-tight">
                    Selamat datang di,<br>
                    <span class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent">Tata Kata.</span>
                </h1>
            </div>

            {{-- Cards Section --}}
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 lg:gap-10 justify-center items-center flex-1 py-4 sm:py-6 lg:py-0 max-w-2xl mx-auto w-full">
                
                {{-- Card 1: Unggah Dokumen --}}
                <a href="{{ route('upload') }}" class="group w-full sm:w-auto max-w-sm">
                    <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] 
                                rounded-2xl sm:rounded-3xl lg:rounded-[2rem] p-6 sm:p-8 lg:p-12 
                                shadow-xl hover:shadow-2xl transition-all duration-300 
                                hover:scale-105 w-full sm:w-48 md:w-52 lg:w-64 
                                h-44 sm:h-48 md:h-52 lg:h-64 
                                flex flex-col items-center justify-center 
                                text-center border-[2px] border-[#2a3a5a]">
                        
                        <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mb-3 sm:mb-4 lg:mb-6">
                            Unggah<br>Dokumen
                        </h2>
                        
                        <img src="{{ asset('images/ikonplus.png') }}" alt="Ikon Plus" 
                             class="w-10 h-10 sm:w-11 sm:h-11 lg:w-14 lg:h-14 mt-1 transition-transform duration-200 group-hover:scale-110">
                    </div>
                </a>

                {{-- Card 2: Riwayat --}}
                <a href="{{ route('history') }}" class="group w-full sm:w-auto max-w-sm">
                    <div class="bg-gradient-to-br from-[#4a5a7a] via-[#556080] to-[#5a6a8a] 
                                rounded-2xl sm:rounded-3xl lg:rounded-[2rem] p-6 sm:p-8 lg:p-12 
                                shadow-xl hover:shadow-2xl transition-all duration-300 
                                hover:scale-105 w-full sm:w-48 md:w-52 lg:w-64 
                                h-44 sm:h-48 md:h-52 lg:h-64 
                                flex flex-col items-center justify-center 
                                text-center border-[2px] border-[#2a3a5a]">
                        
                        <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mb-3 sm:mb-4 lg:mb-6">
                            Riwayat
                        </h2>

                        <img src="{{ asset('images/ikonriwayat.png') }}" alt="Ikon Riwayat" 
                             class="w-10 h-10 sm:w-11 sm:h-11 lg:w-14 lg:h-14 mt-1 transition-transform duration-200 group-hover:scale-110">
                    </div>
                </a>
            </div>

            {{-- Success Message --}}
            @if(session('success'))
            <div class="mt-4 sm:mt-6 lg:mt-8">
                <div class="bg-green-100 border-2 border-green-400 text-green-700 px-4 sm:px-5 lg:px-6 py-3 sm:py-3.5 lg:py-4 rounded-lg sm:rounded-xl text-sm sm:text-base shadow-md">
                    {{ session('success') }}
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection