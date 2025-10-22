<div class="min-h-screen flex items-center justify-center bg-[#0E1320] px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-b from-[#3E4B5B]/30 to-[#3E4B5B]/10 backdrop-blur-xl rounded-2xl shadow-2xl px-6 sm:px-8 md:px-10 py-6 sm:py-8 w-full max-w-xs sm:max-w-sm md:max-w-md">

            {{-- Logo --}}
            <div class="flex justify-center mb-4 sm:mb-6">
                <x-application-logo class="w-10 h-10 sm:w-12 sm:h-12 text-gray-300" />
            </div>

            {{-- Judul --}}
            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 text-center mb-2">
                Atur Ulang Kata Sandi
            </h2>

            <p class="text-[10px] sm:text-xs text-gray-600 italic text-center mb-4 sm:mb-6">
                *Masukkan kode dan kata sandi baru Anda
            </p>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-3 sm:space-y-4">
                @csrf
                <!-- Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div>
                    <input id="email"
                        class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E0E3C] focus:outline-none"
                        type="email" name="email" value="{{ old('email', $request->email) }}" required
                        autocomplete="username" placeholder="Email Anda">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password Baru -->
                <div>
                    <input id="password"
                        class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E0E3C] focus:outline-none"
                        type="password" name="password" required autocomplete="new-password"
                        placeholder="Kata sandi baru">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <input id="password_confirmation"
                        class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E0E3C] focus:outline-none"
                        type="password" name="password_confirmation" required autocomplete="new-password"
                        placeholder="Konfirmasi kata sandi">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Tombol -->
                <button type="submit"
                    class="w-full bg-[#0E0E3C] text-white py-2 sm:py-2.5 rounded-full text-xs sm:text-sm font-medium hover:bg-[#181863] transition-all duration-200 shadow-md">
                    Simpan
                </button>
            </form>

            {{-- Link Kembali --}}
            <div class="mt-4 sm:mt-6 text-center text-xs sm:text-sm">
                <a href="{{ route('login') }}" class="text-gray-700 hover:underline">
                    Kembali ke <span class="font-semibold">Masuk</span>
                </a>
            </div>
        </div>
    </div>