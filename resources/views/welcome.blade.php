@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#5a5d7a] via-[#6b7094] to-[#7a8db8] flex flex-col">
    
    {{-- Navbar --}}
    <header class="flex justify-between items-center px-4 sm:px-8 lg:px-12 py-4 lg:py-6">
        <div class="flex items-center gap-2 sm:gap-3">
            <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14">
        </div>
        <div class="flex gap-2 sm:gap-4">
            <a href="{{ route('login') }}" class="px-4 sm:px-6 lg:px-8 py-2 sm:py-2.5 border-2 border-white/80 text-white rounded-full hover:bg-white hover:text-[#5a5d7a] transition-all duration-300 font-semibold text-xs sm:text-sm">Masuk</a>
            <a href="{{ route('register') }}" class="px-4 sm:px-6 lg:px-8 py-2 sm:py-2.5 bg-white text-[#5a5d7a] rounded-full hover:bg-white/90 transition-all duration-300 font-semibold text-xs sm:text-sm shadow-md">Daftar</a>
        </div>
    </header>

    {{-- Hero Section --}}
    <main class="relative flex flex-col lg:flex-row items-center justify-between px-4 sm:px-8 lg:px-20 py-6 sm:py-10 lg:py-16 gap-8 sm:gap-12 lg:gap-16 bg-gradient-to-br from-[#f8f8f5] to-[#ededea] flex-1 overflow-hidden">
        
        {{-- Semburat Cahaya Putih --}}
        <div class="absolute bottom-0 left-0 w-[200px] sm:w-[300px] lg:w-[400px] h-[200px] sm:h-[300px] lg:h-[400px] bg-white/30 rounded-full blur-[80px] sm:blur-[100px] lg:blur-[120px] opacity-60"></div>
        <div class="absolute bottom-5 sm:bottom-10 left-5 sm:left-10 w-[150px] sm:w-[250px] lg:w-[300px] h-[150px] sm:h-[250px] lg:h-[300px] bg-white/20 rounded-full blur-[60px] sm:blur-[80px] lg:blur-[100px] opacity-50"></div>
        
        {{-- Konten Kiri --}}
        <div class="w-full lg:max-w-xl space-y-4 sm:space-y-6 flex-1 relative z-10 text-center lg:text-left">
            <div class="space-y-2 sm:space-y-3">
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-semibold text-[#2a2a3e] leading-tight">Periksa Kata, Sempurnakan Bahasa</h2>
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent" style="filter: drop-shadow(0 2px 8px rgba(30, 58, 138, 0.3));">Tata Kata.</h1>
            </div>
            
            <p class="text-base sm:text-lg md:text-xl text-[#3a3a4e] leading-relaxed max-w-2xl mx-auto lg:mx-0">
                Tingkatkan kualitas tulisan tugas akhir Anda dengan koreksi otomatis tata bahasa, ejaan, dan gaya penulisan menggunakan teknologi AI.
            </p>

            {{-- Fitur Cards - Hexagon Style dengan Efek Hover --}}
            <div class="relative mt-8 sm:mt-12 h-[220px] sm:h-[280px] lg:h-[300px] w-full max-w-[320px] sm:max-w-[380px] lg:max-w-[420px] mx-auto lg:mx-0">
                {{-- Hexagon 1 - Kiri Atas --}}
                <div class="hexagon-container absolute top-0 left-0 w-[140px] sm:w-[180px] lg:w-[200px] h-[130px] sm:h-[160px] lg:h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg hexagon-svg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" class="hexagon-shape" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="70" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Pemrosesan</text>
                        <text x="100" y="88" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Bahasa</text>
                        <text x="100" y="106" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Berbasis AI</text>
                        <circle cx="100" cy="140" r="12" class="hexagon-check-bg" fill="white" fill-opacity="0.2"/>
                        <path d="M94,140 L98,144 L106,136" class="hexagon-check" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                {{-- Hexagon 2 - Kanan Atas --}}
                <div class="hexagon-container absolute top-0 right-0 w-[140px] sm:w-[180px] lg:w-[200px] h-[130px] sm:h-[160px] lg:h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg hexagon-svg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" class="hexagon-shape" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="73" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Semua Fitur</text>
                        <text x="100" y="91" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Dapat Diakses</text>
                        <text x="100" y="109" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Secara Gratis</text>
                        <circle cx="100" cy="140" r="12" class="hexagon-check-bg" fill="white" fill-opacity="0.2"/>
                        <path d="M94,140 L98,144 L106,136" class="hexagon-check" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                {{-- Hexagon 3 - Tengah Bawah --}}
                <div class="hexagon-container absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[140px] sm:w-[180px] lg:w-[200px] h-[130px] sm:h-[160px] lg:h-[180px]">
                    <svg viewBox="0 0 200 180" class="w-full h-full drop-shadow-lg hexagon-svg">
                        <polygon points="100,15 170,52 170,128 100,165 30,128 30,52" class="hexagon-shape" fill="#4a4d6e" stroke="#3a3d5e" stroke-width="2"/>
                        <text x="100" y="73" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Format File</text>
                        <text x="100" y="91" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Word & PDF</text>
                        <text x="100" y="109" text-anchor="middle" fill="white" font-size="15" font-weight="600" class="hexagon-text sm:text-[17px]">Didukung</text>
                        <circle cx="100" cy="140" r="12" class="hexagon-check-bg" fill="white" fill-opacity="0.2"/>
                        <path d="M94,140 L98,144 L106,136" class="hexagon-check" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Ilustrasi Kanan --}}
        <div class="flex-1 flex items-center justify-center w-full lg:justify-end mt-6 lg:mt-0">
            <div class="relative group">
                <img src="{{ asset('images/logo-tatakata.png') }}" 
                     alt="Logo Tata Kata" 
                     class="relative w-[280px] sm:w-[350px] md:w-[450px] lg:w-[550px] drop-shadow-2xl transition-all duration-[3000ms] ease-in-out group-hover:rotate-[360deg]"
                     style="transform-origin: center center;">
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-[#2a2d4a] text-white py-8 px-4 sm:px-8 lg:px-12">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                {{-- Kolom 1: Logo & Deskripsi --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-10 h-10">
                        <span class="text-xl font-bold">Tata Kata</span>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        Platform koreksi tata bahasa berbasis AI untuk menyempurnakan tulisan tugas akhir Anda.
                    </p>
                </div>

                {{-- Kolom 2: Kontak --}}
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold">Hubungi Kami</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <span>info@tatakata.com</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Surabaya, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="border-t border-gray-600 pt-6 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Tata Kata. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
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

/* Hexagon Hover Effects */
.hexagon-container {
    cursor: pointer;
    transition: all 0.3s ease;
}

.hexagon-container:hover {
    transform: scale(1.1) translateY(-5px);
    filter: drop-shadow(0 10px 20px rgba(74, 77, 110, 0.4));
}

/* Hexagon 3 - Tidak bergeser horizontal */
.hexagon-container:nth-child(3):hover {
    transform: translateX(-50%) scale(1.1) translateY(-5px);
}

.hexagon-container:hover .hexagon-shape {
    fill: #5a5d7e;
    stroke: #6a6d8e;
    stroke-width: 3;
    transition: all 0.3s ease;
}

.hexagon-container:hover .hexagon-text {
    fill: #ffffff;
    font-size: 16px;
    transition: all 0.3s ease;
}

.hexagon-container:hover .hexagon-check-bg {
    fill: #4ade80;
    fill-opacity: 0.3;
    r: 14;
    transition: all 0.3s ease;
}

.hexagon-container:hover .hexagon-check {
    stroke: #22c55e;
    stroke-width: 3;
    transition: all 0.3s ease;
}

/* Animasi Pulse pada Hexagon */
@keyframes hexagonPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.hexagon-container:hover .hexagon-svg {
    animation: hexagonPulse 1.5s ease-in-out infinite;
}

/* Glow Effect pada Hover */
.hexagon-container:hover .hexagon-shape {
    filter: drop-shadow(0 0 10px rgba(74, 222, 128, 0.5));
}
</style>
@endsection