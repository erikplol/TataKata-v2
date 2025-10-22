@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#e8e8e8] p-4 sm:p-6 lg:p-0">
    <div class="w-full max-w-[95vw] sm:max-w-[90vw] lg:w-[90vw] lg:h-[90vh] 
                grid grid-cols-1 lg:grid-cols-[37%_63%] 
                rounded-2xl sm:rounded-3xl lg:rounded-[2.5rem] 
                overflow-hidden 
                border-2 sm:border-4 lg:border-[6px] border-[#3d3d5c] 
                shadow-lg sm:shadow-xl lg:shadow-[0_0_30px_rgba(0,0,0,0.3)]">

        {{-- Bagian Kiri (Silver metalik) - Hidden di mobile --}}
        <div class="hidden lg:flex relative bg-gradient-to-br from-[#e8e8e8] via-[#d0d0d0] to-[#c0c0c0] flex-col justify-between p-8 xl:p-16">
            {{-- Efek glossy dan semburat cahaya putih --}}
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.6)_0%,rgba(255,255,255,0)_50%)]"></div>
            <div class="absolute top-10 right-10 w-[200px] h-[200px] bg-white/40 rounded-full blur-[80px]"></div>
            <div class="absolute bottom-20 left-20 w-[180px] h-[180px] bg-white/30 rounded-full blur-[90px]"></div>
            
            {{-- Logo dan teks dengan gradasi warna --}}
            <div class="relative z-10">
                <h1 class="text-4xl xl:text-5xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent mb-8 xl:mb-10" style="opacity: 0.85;">Tata Kata.</h1>
                <div class="flex items-start gap-4 xl:gap-5">
                    <div class="w-12 h-12 xl:w-14 xl:h-14 flex-shrink-0 flex items-center justify-center">
                        <img src="{{ asset('images/ikonjam.png') }}" alt="Ikon" class="w-12 h-12 xl:w-14 xl:h-14">
                    </div>
                    <p class="text-base xl:text-lg font-medium leading-relaxed" style="color: rgba(10, 10, 46, 0.7);">
                        Cara tercepat untuk<br>memeriksa dokumen<br>anda
                    </p>
                </div>
            </div>

            {{-- Gambar spiral --}}
            <div class="absolute bottom-0 -left-20 w-80 xl:w-96 h-80 xl:h-96">
                <img src="{{ asset('images/spiral.png') }}" alt="Spiral" class="w-full h-full object-contain opacity-60" style="filter: brightness(1.3) contrast(1.2) drop-shadow(0 0 10px rgba(255,255,255,0.5));">
            </div>
        </div>

        {{-- Bagian Kanan (Form Login) --}}
        <div class="relative bg-gradient-to-br from-[#9a9aaa] via-[#8b92b8] to-[#7a90c8] 
                    flex flex-col justify-center 
                    p-6 sm:p-10 lg:p-12 xl:p-16 
                    min-h-[100vh] sm:min-h-[80vh] lg:min-h-0">
            
            {{-- Logo mobile only --}}
            <div class="lg:hidden text-center mb-6 sm:mb-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent" style="opacity: 0.85;">
                    Tata Kata.
                </h1>
            </div>

            {{-- Semburat cahaya putih bercahaya --}}
            <div class="absolute top-0 left-0 w-[200px] sm:w-[250px] lg:w-[300px] h-[200px] sm:h-[250px] lg:h-[300px] bg-white/20 rounded-full blur-[80px] sm:blur-[90px] lg:blur-[100px]"></div>
            <div class="absolute bottom-10 right-10 w-[180px] sm:w-[220px] lg:w-[250px] h-[180px] sm:h-[220px] lg:h-[250px] bg-white/15 rounded-full blur-[70px] sm:blur-[80px] lg:blur-[90px]"></div>
            <div class="absolute top-1/2 left-1/4 w-[150px] sm:w-[180px] lg:w-[200px] h-[150px] sm:h-[180px] lg:h-[200px] bg-white/10 rounded-full blur-[60px] sm:blur-[70px] lg:blur-[80px]"></div>
            
            {{-- Card form dengan border navy glossy --}}
            <div class="relative bg-gradient-to-br from-[#ebebeb] to-[#f5f5f5] 
                        p-6 sm:p-8 lg:p-10 
                        rounded-xl sm:rounded-2xl lg:rounded-[2rem] 
                        shadow-lg sm:shadow-xl lg:shadow-[0_8px_30px_rgba(0,0,0,0.15),0_0_20px_rgba(30,58,138,0.2)] 
                        border-2 sm:border-[3px] border-[#1E3A8A] 
                        max-w-md mx-auto w-full" 
                 style="box-shadow: 0 8px 30px rgba(0,0,0,0.15), 0 0 15px rgba(30,58,138,0.3), inset 0 1px 0 rgba(255,255,255,0.3);">
                
                <h2 class="text-lg sm:text-xl font-semibold text-[#1a1a2e] mb-6 sm:mb-8 text-center">
                    Masuk ke akun Tata Kata Anda
                </h2>

                {{-- Pesan Status --}}
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg mb-4 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside text-xs sm:text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-semibold text-[#1a1a2e] mb-1">
                            Alamat Email
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-md border border-gray-300 bg-white text-[#1a1a2e] placeholder-gray-400 focus:border-[#3d3d5c] focus:ring-1 focus:ring-[#3d3d5c] outline-none transition">
                    </div>

                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-[#1a1a2e] mb-1">
                            Kata Sandi
                        </label>
                        <input id="password" type="password" name="password" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-md border border-gray-300 bg-white text-[#1a1a2e] placeholder-gray-400 focus:border-[#3d3d5c] focus:ring-1 focus:ring-[#3d3d5c] outline-none transition">
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-gray-300 text-[#1a1a2e] focus:ring-[#3d3d5c] w-4 h-4">
                        <label for="remember_me" class="ml-2 text-xs sm:text-sm text-[#1a1a2e]/80">
                            Ingat saya
                        </label>
                    </div>

                    <div class="pt-1 sm:pt-2">
                        <button type="submit"
                            class="w-full py-2.5 sm:py-3 px-6 text-sm sm:text-base bg-[#1a1a2e] text-white rounded-full font-semibold shadow-md hover:bg-[#2d2d44] active:scale-95 transition duration-200">
                            Masuk
                        </button>
                    </div>
                </form>

                {{-- Link Tambahan --}}
                <div class="mt-6 sm:mt-8 text-center">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs sm:text-sm text-[#1a1a2e]/70 hover:text-[#1a1a2e]">
                            Lupa kata sandi Anda?
                        </a>
                    @endif
                    <div class="border-t border-[#1a1a2e]/20 my-3 sm:my-4"></div>
                    <p class="text-[#1a1a2e]/80 text-xs sm:text-sm">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-bold text-[#1a1a2e] hover:underline">Daftar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection