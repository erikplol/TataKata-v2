@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-3 sm:py-5 px-4 sm:px-8 flex justify-between items-center">
            <div class="flex items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12">
            </div>

            <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            <div class="flex items-center gap-2 sm:gap-4">
                <a class="relative flex items-center group">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="hidden sm:block absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="relative w-full min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-76px)] md:min-h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden pb-8 sm:pb-12">
        <div class="absolute -top-24 sm:-top-32 md:-top-48 -right-24 sm:-right-32 md:-right-48 w-[400px] sm:w-[600px] md:w-[900px] h-[400px] sm:h-[600px] md:h-[900px] 
                    bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 
                    rounded-full blur-[120px] sm:blur-[180px] md:blur-[220px] pointer-events-none"></div>

        <div class="absolute -bottom-12 sm:-bottom-16 md:-bottom-20 -left-12 sm:-left-16 md:-left-20 w-[300px] sm:w-[400px] md:w-[550px] h-[300px] sm:h-[400px] md:h-[550px] bg-white/50 rounded-full blur-[80px] sm:blur-[120px] md:blur-[150px] pointer-events-none"></div>

        <div class="absolute top-0 right-0 w-[150px] sm:w-[220px] md:w-[280px] lg:w-[320px] h-[150px] sm:h-[220px] md:h-[280px] lg:h-[320px] opacity-20 sm:opacity-25 md:opacity-30 pointer-events-none">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain object-right">
        </div>

        <div class="absolute bottom-0 left-0 w-[250px] sm:w-[350px] md:w-[450px] lg:w-[550px] h-[250px] sm:h-[350px] md:h-[450px] lg:h-[550px] opacity-20 sm:opacity-25 md:opacity-30 pointer-events-none -translate-x-4 sm:-translate-x-6 md:-translate-x-8 mb-[-2px]">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        <main class="relative z-10 px-4 sm:px-6 md:px-8 lg:px-12 py-6 sm:py-8">
            <a href="{{ route('dashboard') }}" class="inline-block text-base sm:text-xl md:text-2xl lg:text-3xl font-semibold text-[#1a1a2e]/80 hover:underline mb-6 sm:mb-8 underline">
                ← Beranda
            </a>

            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-[#1a1a2e] mb-8 sm:mb-10 md:mb-12 text-center">
                Hasil Pemeriksaan
            </h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 max-w-7xl mx-auto mb-8 sm:mb-10 md:mb-12">
                <div class="relative">
                    <div class="flex justify-center mb-3 sm:mb-4">
                        <span class="bg-[#556080] text-white px-5 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 rounded-full font-semibold text-sm sm:text-base md:text-lg shadow-lg">
                            Teks Asli
                        </span>
                    </div>
                    <div class="bg-[#faf8f3] border-2 sm:border-3 border-[#1a1a2e] rounded-2xl sm:rounded-3xl md:rounded-[2.5rem] p-5 sm:p-6 md:p-8 min-h-[400px] sm:min-h-[500px] md:min-h-[600px] shadow-2xl">
                        <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-5 md:mb-6 pb-3 sm:pb-4 border-b-2 border-gray-300">
                            <h5 class="text-base sm:text-lg md:text-xl font-bold text-gray-800">Teks Asli</h5>
                        </div>
                        <div class="prose max-w-none text-gray-800 leading-relaxed text-sm sm:text-base overflow-auto max-h-[calc(100vh-400px)] sm:max-h-none">
                            <p class="whitespace-pre-wrap">{{ $originalText ?? $original_text ?? 'Teks asli akan muncul di sini.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="flex justify-center mb-3 sm:mb-4">
                        <span class="bg-[#556080] text-white px-5 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 rounded-full font-semibold text-sm sm:text-base md:text-lg shadow-lg">
                            Koreksi AI
                        </span>
                    </div>
                    <div class="bg-[#faf8f3] border-2 sm:border-3 border-[#1a1a2e] rounded-2xl sm:rounded-3xl md:rounded-[2.5rem] p-5 sm:p-6 md:p-8 min-h-[400px] sm:min-h-[500px] md:min-h-[600px] shadow-2xl">
                        <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-5 md:mb-6 pb-3 sm:pb-4 border-b-2 border-gray-300">
                            <h5 class="text-base sm:text-lg md:text-xl font-bold text-gray-800">Hasil Koreksi</h5>
                        </div>
                        <div class="prose max-w-none text-gray-800 leading-relaxed text-sm sm:text-base overflow-auto max-h-[calc(100vh-400px)] sm:max-h-none">
                            <div id="corrected-markdown" class="whitespace-pre-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-between items-stretch sm:items-center max-w-7xl mx-auto px-2 sm:px-4">
                <a href="{{ route('history') }}" 
                   class="w-full sm:w-auto px-8 sm:px-10 md:px-12 py-3 sm:py-3.5 md:py-4 bg-white text-[#1a1a2e] rounded-full font-bold text-base sm:text-lg hover:bg-gray-100 transition-all duration-200 shadow-xl border-2 border-[#1a1a2e] text-center">
                    Riwayat
                </a>
                
                <button onclick="applyAndDownload()" 
                        class="w-full sm:w-auto px-8 sm:px-10 md:px-12 py-3 sm:py-3.5 md:py-4 bg-white text-[#1a1a2e] rounded-full font-bold text-base sm:text-lg hover:bg-gray-100 transition-all duration-200 shadow-xl border-2 border-[#1a1a2e]">
                    Terapkan Semua & Unduh
                </button>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js"></script>

<script>
    const correctedRaw = @json($correctedText ?? $corrected_text ?? 'Hasil koreksi akan ditampilkan di sini.');

    const introPhraseRegex = /^(Tentu,\s*)?Berikut adalah perbaikan(nya)?(\s*tata bahasa dan ejaan( untuk kalimat-kalimat tersebut)?)? tanpa mengubah makna(nya)?[:\s]*/i;

    let textToDisplay = correctedRaw;
    
    // Hanya coba bersihkan jika teks bukan placeholder
    if (textToDisplay && textToDisplay !== 'Hasil koreksi akan ditampilkan di sini.') {
        // Hapus frasa pengantar yang cocok
        textToDisplay = textToDisplay.replace(introPhraseRegex, '');
    }

    try {
        const mdHtml = marked.parse(textToDisplay); // Parse teks yang sudah bersih
        const safeHtml = DOMPurify.sanitize(mdHtml);
        document.getElementById('corrected-markdown').innerHTML = safeHtml;
    } catch (e) {
        document.getElementById('corrected-markdown').textContent = textToDisplay;
    }

    function applyAndDownload() {
        // Gunakan teks yang sudah bersih untuk unduhan
        try {
            const content = textToDisplay ?? ''; 
            const blob = new Blob([content], { type: 'text/markdown;charset=utf-8' });
            const timestamp = new Date().toISOString().slice(0,19).replace(/[:T]/g, '-');
            const filename = `corrected-${timestamp}.md`;

            if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveOrOpenBlob(blob, filename);
                return;
            }

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        } catch (err) {
            console.error('Download failed', err);
            alert('Gagal membuat unduhan: ' + (err && err.message ? err.message : err));
        }
    }
</script>
@endsection