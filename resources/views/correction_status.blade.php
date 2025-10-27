@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden flex items-center justify-center p-4 sm:p-6">

    <div class="bg-white p-6 sm:p-8 md:p-10 lg:p-12 rounded-xl sm:rounded-2xl shadow-2xl max-w-lg w-full text-center z-10">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#1a1a2e] mb-3 sm:mb-4" id="main-title">
            Sedang Memproses Dokumen ⏳
        </h1>
        <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 sm:mb-8" id="doc-info">
            Dokumen <strong>{{ $document->file_name }}</strong> sedang diperiksa dan dikoreksi oleh AI.
        </p>

        <div class="flex flex-col items-center">
            <div id="status-display">
                <svg id="processing-spinner" class="animate-spin h-10 w-10 sm:h-12 sm:w-12 text-[#1a1a2e] mb-3 sm:mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <p id="status-message" class="text-base sm:text-lg font-semibold text-[#556080]">
                Status: {{ $document->upload_status }}...
            </p>
            <p id="status-details" class="text-sm sm:text-base text-gray-500 mt-2">{{ $document->details ?? '' }}</p>
            <div class="w-full mt-4 flex gap-3 justify-center">
                <a href="{{ route('correction.original', $document->id) }}" target="_blank" rel="noopener noreferrer"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md shadow-sm hover:bg-gray-50">
                    Lihat Dokumen Asli
                </a>
            </div>
            <div id="progress-panel" class="w-full mt-4 text-left">
                <h4 class="text-sm font-semibold text-[#556080] mb-2">Progres</h4>
                <ul id="progress-list" class="text-sm text-gray-600 space-y-1 max-h-40 overflow-auto pr-2">
                    @foreach(array_slice($document->progress_log ?? [], -10) as $entry)
                        <li class="flex gap-2 items-start">
                            <span class="text-xs text-gray-400">[{{ $entry['ts'] ?? '' }}]</span>
                            <span>{{ $entry['message'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div id="error-message" class="mt-4 text-red-600 font-bold hidden"></div>
    </div>

    <script>
        function redirectToCorrection(url) {
            window.location.replace(url);
        }

        document.addEventListener('DOMContentLoaded', function() {
              const checkUrl = {!! json_encode(route('correction.check-status', $document->id)) !!};
            const statusMessage = document.getElementById('status-message');
            const statusDisplay = document.getElementById('status-display');
            const mainTitle = document.getElementById('main-title');
            const docInfo = document.getElementById('doc-info');
            
            const intervalDuration = 15000; 
            let pollingIntervalId = null;

            function createRedirectButton(url) {
                return `<button onclick="redirectToCorrection('${url}')"
                            class="px-6 sm:px-8 py-2.5 sm:py-3 bg-[#4a4a6a] text-white rounded-lg font-bold text-lg sm:text-xl hover:bg-[#6a7a9a] transition duration-200 shadow-lg w-full sm:w-auto">
                            Lihat Hasil Koreksi
                        </button>`;
            }

            function checkProcessingStatus() {
                fetch(checkUrl, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(data => {
                    statusMessage.innerText = `Status: ${data.status}...`;

                    if (data.done) { 
                        if (pollingIntervalId !== null) {
                            clearInterval(pollingIntervalId); 
                            pollingIntervalId = null; 
                        }
                        
                        mainTitle.innerText = "Pemrosesan Selesai! 🎉";
                        statusDisplay.innerHTML = createRedirectButton(data.redirect_url);
                        statusMessage.innerText = "Dokumen siap. Klik tombol di atas untuk melihat perubahannya.";

                    } else if (data.status === 'Failed') {
                        if (pollingIntervalId !== null) { clearInterval(pollingIntervalId); }
                        
                        // Hide document info when failed
                        docInfo.classList.add('hidden');
                        
                        mainTitle.innerText = "Pemrosesan Gagal ❌";
                        statusDisplay.innerHTML = `
                            <a href="{{ route('upload') }}"
                               class="inline-block px-6 sm:px-8 py-2.5 sm:py-3 bg-[#4a4a6a] text-white rounded-lg font-bold text-lg sm:text-xl hover:bg-[#6a7a9a] transition duration-200 shadow-lg w-full sm:w-auto">
                                Kembali ke Unggah Dokumen
                            </a>
                        `;
                        statusMessage.innerText = "Terjadi kesalahan. Silakan coba unggah dokumen lagi.";
                        // show any details from the server (error info)
                        const errorEl = document.getElementById('error-message');
                        if (data.details) {
                            errorEl.innerText = data.details;
                            errorEl.classList.remove('hidden');
                        }
                    }
                    else if (data.status === 'Deleted') {
                        if (pollingIntervalId !== null) { clearInterval(pollingIntervalId); }
                        mainTitle.innerText = "Dokumen Dihapus 🗑️";
                        statusDisplay.innerHTML = `
                            <a href="{{ route('history') }}"
                               class="inline-block px-6 sm:px-8 py-2.5 sm:py-3 bg-[#4a4a6a] text-white rounded-lg font-bold text-lg sm:text-xl hover:bg-[#6a7a9a] transition duration-200 shadow-lg w-full sm:w-auto">
                                Kembali ke Riwayat
                            </a>
                        `;
                        statusMessage.innerText = data.details || 'Dokumen telah dihapus.';
                        docInfo.classList.add('hidden');
                    }
                    // always update details if present
                    const detailsEl = document.getElementById('status-details');
                    if (data.details && detailsEl) {
                        detailsEl.innerText = data.details;
                    }

                    // update progress list if provided
                    if (data.progress && Array.isArray(data.progress)) {
                        const list = document.getElementById('progress-list');
                        if (list) {
                            list.innerHTML = '';
                            data.progress.forEach(entry => {
                                const li = document.createElement('li');
                                li.className = 'flex gap-2 items-start';
                                const ts = document.createElement('span');
                                ts.className = 'text-xs text-gray-400';
                                ts.innerText = '[' + (entry.ts || '') + ']';
                                const msg = document.createElement('span');
                                msg.innerText = entry.message || '';
                                li.appendChild(ts);
                                li.appendChild(msg);
                                list.appendChild(li);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                    statusMessage.innerText = 'Gagal terhubung ke server. Mencoba lagi...';
                });
            }
            
            checkProcessingStatus(); 
            pollingIntervalId = setInterval(checkProcessingStatus, intervalDuration);

        });
    </script>
</div>
@endsection