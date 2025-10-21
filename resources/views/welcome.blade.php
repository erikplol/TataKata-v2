@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#5a5d7a] via-[#6b7094] to-[#7a8db8] flex flex-col">
    
    {{-- Navbar --}}
    <header class="flex justify-between items-center px-12 py-6">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-14 h-14">
        </div>
        <div class="space-x-4">
            <a href="{{ route('login') }}" class="px-8 py-2.5 border-2 border-white/80 text-white rounded-full hover:bg-white hover:text-[#5a5d7a] transition-all duration-300 font-semibold text-sm">Masuk</a>
            <a href="{{ route('register') }}" class="px-8 py-2.5 bg-white text-[#5a5d7a] rounded-full hover:bg-white/90 transition-all duration-300 font-semibold text-sm shadow-md">Daftar</a>
        </div>
    </header>

    {{-- Hero Section - Menyatu dengan Header --}}
    <main class="relative flex flex-col lg:flex-row items-center justify-between px-12 lg:px-20 py-8 lg:py-16 gap-16 bg-gradient-to-br from-[#f8f8f5] to-[#ededea] flex-1 overflow-hidden">
        
        {{-- Semburat Cahaya Putih di Pojok Kiri Bawah --}}
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/30 rounded-full blur-[120px] opacity-60"></div>
        <div class="absolute bottom-10 left-10 w-[300px] h-[300px] bg-white/20 rounded-full blur-[100px] opacity-50"></div>
        
        {{-- Konten Kiri --}}
        <div class="max-w-xl space-y-6 flex-1 relative z-10">
            <div class="space-y-3">
                <h2 class="text-3xl md:text-4xl font-semibold text-[#2a2a3e] leading-tight">Periksa Kata, Sempurnakan Bahasa</h2>
                <h1 class="text-6xl md:text-7xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent" style="filter: drop-shadow(0 2px 8px rgba(30, 58, 138, 0.3));">Tata Kata.</h1>
            </div>
            
            <p class="text-lg md:text-xl text-[#3a3a4e] leading-relaxed">
                Tingkatkan kualitas tulisan tugas akhir Anda dengan koreksi otomatis tata bahasa, ejaan, dan gaya penulisan menggunakan teknologi AI.
            </p>

            {{-- Fitur Cards - Hexagon Style --}}
            <div class="relative mt-12 h-[300px] w-[420px]">
                {{-- Hexagon 1 - Kiri Atas --}}
                <div class="absolute top-0 left-0 w-[200px] h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="70" text-anchor="middle" fill="white" font-size="17" font-weight="600">Pemrosesan</text>
                        <text x="100" y="90" text-anchor="middle" fill="white" font-size="17" font-weight="600">Bahasa</text>
                        <text x="100" y="110" text-anchor="middle" fill="white" font-size="17" font-weight="600">Berbasis AI</text>
                        {{-- Ikon Centang --}}
                        <circle cx="100" cy="142" r="13" fill="white" fill-opacity="0.2"/>
                        <path d="M94,142 L98,146 L106,138" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                {{-- Hexagon 2 - Kanan Atas --}}
                <div class="absolute top-0 right-0 w-[200px] h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="73" text-anchor="middle" fill="white" font-size="17" font-weight="600">Semua Fitur</text>
                        <text x="100" y="93" text-anchor="middle" fill="white" font-size="17" font-weight="600">Dapat Diakses</text>
                        <text x="100" y="113" text-anchor="middle" fill="white" font-size="17" font-weight="600">Secara Gratis</text>
                        {{-- Ikon Centang --}}
                        <circle cx="100" cy="142" r="13" fill="white" fill-opacity="0.2"/>
                        <path d="M94,142 L98,146 L106,138" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                {{-- Hexagon 3 - Tengah Bawah --}}
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[200px] h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="90" text-anchor="middle" fill="white" font-size="17" font-weight="600">Kompatibilitas</text>
                        {{-- Ikon Centang --}}
                        <circle cx="100" cy="142" r="13" fill="white" fill-opacity="0.2"/>
                        <path d="M94,142 L98,146 L106,138" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Ilustrasi Kanan --}}
        <div class="flex-1 flex items-center justify-center lg:justify-end">
            <div class="relative group">
                {{-- Gambar Logo dengan Animasi Rotate saat Hover --}}
                <img src="{{ asset('images/logo-tatakata.png') }}" 
                     alt="Logo Tata Kata" 
                     class="relative w-[400px] md:w-[500px] lg:w-[550px] drop-shadow-2xl transition-all duration-[3000ms] ease-in-out group-hover:rotate-[360deg]"
                     style="transform-origin: center center;">
            </div>
        </div>
    </main>
</div>

<style>
@keyframes slowRotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
@endsection