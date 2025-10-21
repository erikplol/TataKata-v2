@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#e8e8e8] relative overflow-hidden">
    
    <header class="bg-gradient-to-r from-[#4a4a6a] via-[#5a6080] to-[#6a7a9a] shadow-lg relative z-20">
        <div class="max-w-full mx-auto py-5 px-8 flex justify-between items-center relative">
            <div class="flex items-center gap-3 relative">
                <img src="{{ asset('images/ikon-logo.png') }}" alt="Logo" class="w-12 h-12">
            </div>

            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-[#0A0A2E] via-[#1E3A8A] to-[#3B82F6] bg-clip-text text-transparent drop-shadow-md tracking-wide">
                Tata Kata.
            </h1>

            <div class="flex items-center gap-4">
                <a href="{{ route('profile.edit') }}" class="relative flex items-center group">
                    <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Profil' }}
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="relative group">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-9 h-9 hover:bg-white/10 rounded-full transition">
                        <svg class="w-9 h-9 text-white group-hover:text-blue-200 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                    <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        Keluar
                        <div class="absolute -top-1 right-3 w-2 h-2 bg-gray-800 transform rotate-45"></div>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <div class="relative w-full min-h-[calc(100vh-88px)] bg-gradient-to-br from-[#f1f1f8] via-[#e6e8f0] to-[#d6dae8] overflow-hidden">
        <div class="absolute -top-48 -right-48 w-[900px] h-[900px] 
                    bg-gradient-to-br from-[#FFEAF1]/70 via-[#FFD9E0]/50 to-[#FFF4F7]/40 
                    rounded-full blur-[220px] pointer-events-none"></div>

        <div class="absolute -bottom-20 -left-20 w-[550px] h-[550px] bg-white/50 rounded-full blur-[150px] pointer-events-none"></div>

        <div class="absolute top-0 right-0 w-[320px] h-[320px] opacity-30 pointer-events-none">
            <img src="{{ asset('images/elemen-1.png') }}" alt="Elemen 1" class="w-full h-full object-contain object-right">
        </div>

        <div class="absolute bottom-0 left-0 w-[550px] h-[550px] opacity-30 pointer-events-none -translate-x-8 mb-[-2px]">
            <img src="{{ asset('images/elemen-2.png') }}" alt="Elemen 2" class="w-full h-full object-contain object-left-bottom">
        </div>

        <main class="relative z-10 max-w-6xl mx-auto px-8 sm:px-12 lg:px-16 py-12 flex flex-col min-h-[calc(100vh-88px)]">
            <a href="{{ route('dashboard') }}" 
                class="block mb-8 text-2xl font-semibold text-[#1a1a2e]/80 hover:underline">
                Beranda
            </a>

            <h1 class="text-4xl md:text-5xl font-bold text-[#1a1a2e] mb-10 text-center">
                Riwayat Pemeriksaan
            </h1>

            <div class="bg-gradient-to-br from-[#d9d9d9]/90 via-[#e4e4e4]/80 to-[#cfcfcf]/90 backdrop-blur-md 
                        rounded-[2rem] p-10 md:p-12 shadow-2xl border-[2px] border-[#2a3a5a]/70
                        text-gray-900 flex-1 flex flex-col">

                @if(isset($history) && $history->count() > 0)
                
                <form id="bulk-action-form" method="POST" action="{{ route('history.bulk-delete') }}" onsubmit="return confirmBulkAction(this);">
                    @csrf
                    
                    <div class="mb-6 flex flex-wrap gap-4 items-center p-4 bg-white/70 rounded-xl shadow-inner border border-gray-300">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                            <span class="text-sm font-semibold">Pilih Semua</span>
                        </label>

                        <select name="action" id="bulk-action-select" class="form-select border-gray-300 rounded-lg text-sm disabled:opacity-50" disabled>
                            <option value="">Pilih Aksi...</option>
                            <option value="download">Unduh File</option>
                            <option value="delete">Hapus</option>
                        </select>

                        <button type="submit" id="bulk-action-button" class="px-4 py-2 bg-[#4a4a6a] text-white rounded-lg text-sm font-semibold hover:bg-[#6a7a9a] transition disabled:bg-gray-400" disabled>
                            Terapkan
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($history as $item)
                        
                            @php
                                $isDocument = $item['type'] === 'document';
                                $name = $item['name'];
                                $status = ucfirst($item['upload_status'] ?? 'Selesai');
                                $createdAt = $item['created_at']->format('d M Y, H:i');
                                $itemIdKey = "{$item['type']}_{$item['id']}";
                                $isCompleted = strtolower($item['upload_status'] ?? '') === 'completed';
                                $downloadable = $isDocument && $isCompleted; 
                                
                                if ($isDocument) {
                                    $correctionUrl = route('correction.show', $item['id']);
                                    $downloadUrl = route('document.download', $item['id']);
                                    $typeBadge = 'Dokumen';
                                } else {
                                    $correctionUrl = route('text.correction.show', $item['id']);
                                    $downloadUrl = null; 
                                    $typeBadge = 'Teks';
                                }
                            @endphp

                        <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 border border-[#2a3a5a]/50 flex items-start gap-4">
                            
                            <div class="mt-1">
                                <input type="checkbox" name="selected_items[]" value="{{ $itemIdKey }}" 
                                       class="item-checkbox form-checkbox h-5 w-5 text-blue-600 rounded mt-1"
                                       data-downloadable="{{ $downloadable ? 'true' : 'false' }}">
                            </div>

                            <div class="flex flex-col gap-3 flex-1">
                                <span class="text-xs font-bold uppercase text-blue-600/80">{{ $typeBadge }}</span>
                                <h3 class="text-xl font-semibold text-[#1a1a2e] truncate">{{ $name }}</h3>
                                
                                <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $createdAt }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                        </svg>
                                        {{ $status }}
                                    </span>
                                </div>
                                
                                <div class="flex gap-3 mt-2">
                                    <a href="{{ $correctionUrl }}" 
                                       class="px-5 py-2 bg-white text-gray-900 rounded-full hover:bg-gray-100 transition font-semibold text-sm">
                                        Lihat Hasil
                                    </a>
                                    
                                    @if($downloadable)
                                    <a href="{{ $downloadUrl }}" 
                                       class="px-5 py-2 bg-gray-100 text-gray-900 rounded-full hover:bg-gray-200 transition font-semibold text-sm">
                                        Unduh File
                                    </a>
                                    @endif

                                    <form method="POST" action="{{ route('history.delete') }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat \'{{ $name }}\'? Tindakan ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="item_type" value="{{ $item['type'] }}">
                                        
                                        <button type="submit"
                                                class="px-3 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h10"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            @else
                <div class="flex flex-col items-center justify-center text-center text-[#1a1a2e] h-full">
                    <svg class="w-20 h-20 text-gray-500/60 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg mb-6 italic">Belum ada riwayat pemeriksaan. Silakan unggah atau periksa teks.</p>
                    <a href="{{ route('upload') }}" 
                       class="px-8 py-2 bg-white text-gray-900 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all duration-200 shadow-lg">
                        Mulai Pemeriksaan
                    </a>
                </div>
            @endif
        </div>

        <div class="fixed bottom-8 right-8 z-50">
            <a href="{{ route('upload') }}" 
               class="flex items-center gap-3 px-6 py-3 bg-white border-2 border-[#2a3a5a] text-gray-900 rounded-full hover:bg-gray-50 transition-all duration-200 font-semibold shadow-xl hover:shadow-2xl">
                <span class="hidden sm:inline">Unggah dokumen</span>
                <span class="sm:hidden">Unggah</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const bulkActionButton = document.getElementById('bulk-action-button');
    const bulkActionForm = document.getElementById('bulk-action-form');

    const routes = {
        delete: '{{ route('history.bulk-delete') }}',
        download: '{{ route('history.bulk-download') }}'
    };

    function updateBulkControls() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        const checkedCount = checkedItems.length;
        const action = bulkActionSelect.value;

        const enableButton = checkedCount > 0 && action !== '';
        bulkActionButton.disabled = !enableButton;
        bulkActionSelect.disabled = checkedCount === 0;
        
        let canDownload = true;
        if (action === 'download') {
            checkedItems.forEach(checkbox => {
                if (checkbox.dataset.downloadable !== 'true') {
                    canDownload = false;
                }
            });
            bulkActionButton.disabled = !canDownload;
            if (!canDownload) {
                bulkActionButton.title = 'Aksi Unduh Massal hanya untuk dokumen yang sudah selesai.';
            } else {
                bulkActionButton.title = '';
            }
        } else {
             bulkActionButton.title = '';
        }
        
        bulkActionButton.disabled = !enableButton || (action === 'download' && !canDownload);
        
        bulkActionForm.querySelector('input[name="_method"]')?.remove();

        if (action === 'download') {
            bulkActionForm.method = 'GET';
            bulkActionForm.action = routes.download;
        } else if (action === 'delete') {
            bulkActionForm.method = 'POST';
            bulkActionForm.action = routes.delete;
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            bulkActionForm.appendChild(methodField);
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkControls();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkControls);
    });

    bulkActionSelect.addEventListener('change', updateBulkControls);

    window.confirmBulkAction = function(form) {
        const action = form.querySelector('#bulk-action-select').value;
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        if (action === 'delete') {
            return confirm(`Anda akan menghapus ${count} item terpilih. Apakah Anda yakin?`);
        }
        return true;
    };
    
    updateBulkControls();
});
</script>
@endsection
