@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#e8e8e8]">
    <div class="w-[90vw] h-[90vh] grid grid-cols-[37%_63%] rounded-[2.5rem] overflow-hidden border-[6px] border-[#3d3d5c] shadow-[0_0_30px_rgba(0,0,0,0.3)]">

        {{-- Bagian Kiri (Silver metalik) --}}
        <div class="relative bg-gradient-to-br from-[#e8e8e8] via-[#d0d0d0] to-[#c0c0c0] flex flex-col justify-between p-16">
            {{-- Efek glossy dan semburat cahaya putih --}}
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.6)_0%,rgba(255,255,255,0)_50%)]"></div>
            <div class="absolute top-10 right-10 w-[200px] h-[200px] bg-white/40 rounded-full blur-[80px]"></div>
            <div class="absolute bottom-20 left-20 w-[180px] h-[180px] bg-white/30 rounded-full blur-[90px]"></div>
            
            {{-- Logo dan teks dengan gradasi warna --}}
            <div class="relative z-10">
                <h1 class="text-5xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent mb-10" style="opacity: 0.85;">Tata Kata.</h1>
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center">
                        <img src="{{ asset('images/ikonjam.png') }}" alt="Ikon" class="w-14 h-14">
                    </div>
                    <p class="text-lg font-medium leading-relaxed" style="color: rgba(10, 10, 46, 0.7);">
                        Cara tercepat untuk<br>memeriksa dokumen<br>anda
                    </p>
                </div>
            </div>

            {{-- Gambar spiral --}}
            <div class="absolute bottom-0 -left-20 w-96 h-96">
                <img src="{{ asset('images/spiral.png') }}" alt="Spiral" class="w-full h-full object-contain opacity-60" style="filter: brightness(1.3) contrast(1.2) drop-shadow(0 0 10px rgba(255,255,255,0.5));">
            </div>
        </div>

        {{-- Bagian Kanan (Gradasi abu-biru lembut dengan semburat cahaya) --}}
        <div class="relative bg-gradient-to-br from-[#9a9aaa] via-[#8b92b8] to-[#7a90c8] flex flex-col justify-center p-16">
            {{-- Semburat cahaya putih bercahaya --}}
            <div class="absolute top-0 left-0 w-[300px] h-[300px] bg-white/20 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-10 right-10 w-[250px] h-[250px] bg-white/15 rounded-full blur-[90px]"></div>
            <div class="absolute top-1/2 left-1/4 w-[200px] h-[200px] bg-white/10 rounded-full blur-[80px]"></div>
            
            {{-- Card form dengan border navy glossy --}}
            <div class="relative bg-gradient-to-br from-[#ebebeb] to-[#f5f5f5] p-10 rounded-[2rem] shadow-[0_8px_30px_rgba(0,0,0,0.15),0_0_20px_rgba(30,58,138,0.2)] border-[3px] border-[#1E3A8A] max-w-md mx-auto w-full" style="box-shadow: 0 8px 30px rgba(0,0,0,0.15), 0 0 15px rgba(30,58,138,0.3), inset 0 1px 0 rgba(255,255,255,0.3);">
                <h2 class="text-xl font-semibold text-[#1a1a2e] mb-8 text-center">Masuk ke akun Tata Kata Anda</h2>

                {{-- Pesan Status --}}
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#1a1a2e] mb-1">Alamat Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-md border border-gray-300 bg-white text-[#1a1a2e] placeholder-gray-400 focus:border-[#3d3d5c] focus:ring-1 focus:ring-[#3d3d5c] outline-none transition">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#1a1a2e] mb-1">Kata Sandi</label>
                        <input id="password" type="password" name="password" required
                            class="w-full px-4 py-3 rounded-md border border-gray-300 bg-white text-[#1a1a2e] placeholder-gray-400 focus:border-[#3d3d5c] focus:ring-1 focus:ring-[#3d3d5c] outline-none transition">
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-gray-300 text-[#1a1a2e] focus:ring-[#3d3d5c]">
                        <label for="remember_me" class="ml-2 text-sm text-[#1a1a2e]/80">Ingat saya</label>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-2.5 px-6 bg-[#1a1a2e] text-white rounded-full font-semibold shadow-md hover:bg-[#2d2d44] transition duration-200">
                            Masuk
                        </button>
                    </div>
                </form>

                {{-- Link Tambahan --}}
                <div class="mt-8 text-center">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-[#1a1a2e]/70 hover:text-[#1a1a2e]">Lupa kata sandi Anda?</a>
                    @endif
                    <div class="border-t border-[#1a1a2e]/20 my-4"></div>
                    <p class="text-[#1a1a2e]/80 text-sm">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-bold text-[#1a1a2e] hover:underline">Daftar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection