@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#c8c8d8] via-[#a8b0c8] to-[#8fa8d0] px-4 relative overflow-hidden">
    {{-- Semburat cahaya putih --}}
    {{-- Kiri atas - sangat besar dan terang --}}
    <div class="absolute top-[-15%] left-[-10%] w-[600px] h-[600px] bg-white/35 rounded-full blur-[150px]"></div>
    <div class="absolute top-[5%] left-[0%] w-[450px] h-[450px] bg-white/25 rounded-full blur-[130px]"></div>
    
    {{-- Kanan bawah - gradasi glossy --}}
    <div class="absolute bottom-[-10%] right-[-5%] w-[500px] h-[500px] bg-white/30 rounded-full blur-[140px]"></div>
    <div class="absolute bottom-[0%] right-[5%] w-[400px] h-[400px] bg-white/20 rounded-full blur-[120px]"></div>
    
    {{-- Kanan atas pojok - glossy corner --}}
    <div class="absolute top-[-5%] right-[-5%] w-[350px] h-[350px] bg-white/25 rounded-full blur-[110px]"></div>
    
    {{-- Tengah untuk depth --}}
    <div class="absolute top-[30%] left-[20%] w-[300px] h-[300px] bg-white/15 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-[25%] right-[20%] w-[280px] h-[280px] bg-blue-100/20 rounded-full blur-[95px]"></div>

    <div class="w-full max-w-lg relative z-10">
        {{-- Card Container dengan border navy glossy yang lebih besar --}}
        <div class="bg-gradient-to-br from-[#f0f0f5] via-[#ebebeb] to-[#e5e5ea] rounded-[2.5rem] shadow-[0_10px_40px_rgba(0,0,0,0.2),0_0_25px_rgba(30,58,138,0.25)] border-[2px] border-[#1E3A8A] p-12" style="box-shadow: 0 10px 40px rgba(0,0,0,0.2), 0 0 20px rgba(30,58,138,0.35), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -1px 0 rgba(0,0,0,0.05);">
            
            {{-- Efek glossy pada card --}}
            <div class="absolute inset-0 rounded-[2.5rem] bg-gradient-to-br from-white/40 via-white/10 to-transparent pointer-events-none"></div>
            <div class="absolute top-0 left-0 w-[200px] h-[200px] bg-white/30 rounded-full blur-[70px] pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-[180px] h-[180px] bg-white/25 rounded-full blur-[65px] pointer-events-none"></div>
            
            <div class="relative z-10">
                {{-- Title --}}
                <h2 class="text-3xl font-bold text-center text-[#1a1a2e] mb-8">
                    Lupa Kata Sandi
                </h2>

                {{-- Session Status (Success Message) --}}
                @if (session('status'))
                    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-400 text-green-700 rounded-xl text-sm font-medium shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    {{-- Email Input --}}
                    <div class="mb-8">
                        <label for="email" class="block text-sm font-light italic text-[#1a1a2e]/70 mb-2">*Masukkan email yang terdaftar</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            placeholder="nama@email.com"
                            class="w-full px-5 py-4 text-base rounded-xl border-2 border-gray-300 bg-white text-[#1a1a2e] placeholder-gray-400 focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 outline-none transition-all duration-200 shadow-sm @error('email') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror"
                        />
                        
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 font-medium">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="mb-10">
                        <button 
                            type="submit"
                            class="w-full bg-gradient-to-r from-[#1a1a2e] to-[#2d2d44] hover:from-[#2d2d44] hover:to-[#3d3d5c] text-white font-semibold text-base py-3 px-6 rounded-full transition-all duration-300 ease-in-out shadow-[0_4px_15px_rgba(26,26,46,0.4)] hover:shadow-[0_6px_20px_rgba(26,26,46,0.5)] hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-[#3d3d5c]/30"
                        >
                            Kirim Kode Reset
                        </button>
                    </div>
                </form>

                {{-- Link Back --}}
                <div class="text-center pt-6 border-t-2 border-[#1a1a2e]/15">
                    <a href="{{ route('login') }}" class="inline-block text-base text-[#1a1a2e]/70 hover:text-[#1a1a2e] hover:underline transition-all duration-200 font-medium">
                        Kembali ke <span class="font-bold">Masuk</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection