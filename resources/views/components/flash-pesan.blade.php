{{-- Notifikasi sukses/peringatan melayang di kanan bawah --}}
@props(['pesan', 'tipe' => 'berhasil'])

<div x-data="{ tampil: true }" x-show="tampil" x-transition x-init="setTimeout(() => tampil = false, 4000)"
    class="fixed bottom-5 end-5 z-50 max-w-sm">
    <div @class([
        'flex items-start gap-3 rounded-kartu px-4 py-3 text-sm shadow-terangkat',
        'border border-sukses/30 bg-white text-sukses-teks' => $tipe === 'berhasil',
        'border border-bahaya/30 bg-white text-bahaya' => $tipe === 'peringatan',
    ])>
        @if ($tipe === 'berhasil')
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 h-5 w-5 shrink-0 text-sukses">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 h-5 w-5 shrink-0">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
            </svg>
        @endif
        <p class="font-medium">{{ $pesan }}</p>
        <button type="button" @click="tampil = false" class="ms-auto text-gray-400 hover:text-gray-600" aria-label="Tutup">&times;</button>
    </div>
</div>
