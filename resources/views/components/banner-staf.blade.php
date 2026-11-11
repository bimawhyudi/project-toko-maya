{{-- Banner hero biru tua untuk dasbor staf (kasir/gudang/pemilik) --}}
@props(['badge', 'judul', 'sub'])

<section class="mx-auto max-w-halaman px-4 pt-6 sm:px-6">
    <div class="relative overflow-hidden rounded-[19px] bg-navy px-6 py-8 text-white shadow-terangkat sm:px-10 sm:py-10">
        <div class="pointer-events-none absolute -end-20 -top-24 h-72 w-72 rounded-full bg-primary/30 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 start-1/3 h-64 w-64 rounded-full bg-primary-dark/50 blur-2xl" aria-hidden="true"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="inline-block rounded-full bg-white/15 px-4 py-1.5 text-[13px] font-semibold text-primary-soft ring-1 ring-white/25">
                    {{ $badge }}
                </span>
                <h1 class="mt-3 font-sans text-3xl font-bold sm:text-4xl">{{ $judul }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-primary-soft/90 sm:text-base">{{ $sub }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                {{ $aksi ?? '' }}
            </div>
        </div>
    </div>
</section>
