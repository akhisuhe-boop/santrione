<x-filament-panels::page>

    {{-- Class custom (bukan Tailwind dark:) dengan alasan yang sama
         seperti komentar asli di bawah: class Tailwind terbukti tidak
         konsisten muncul di build CSS aplikasi ini, jadi dipakai style
         eksplisit. Supaya tetap ikut dark mode, dipisah lewat selector
         .dark di sini alih-alih inline style tanpa syarat seperti
         sebelumnya (ditemukan tidak ikut gelap, 6 Sep 2026). --}}
    <style>
        .qn-card {
            border-radius: 1rem;
            background-color: #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .dark .qn-card {
            background-color: #1f2937;
            border-color: rgba(255,255,255,0.08);
        }

        .qn-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            background: linear-gradient(135deg, rgba(15,156,148,0.07) 0%, rgba(255,255,255,0) 100%);
        }
        .dark .qn-header {
            border-bottom-color: rgba(255,255,255,0.08);
            background: linear-gradient(135deg, rgba(15,156,148,0.18) 0%, rgba(31,41,55,0) 100%);
        }

        .qn-divider {
            flex: 1;
            height: 1px;
            background-color: #e5e7eb;
        }
        .dark .qn-divider {
            background-color: rgba(255,255,255,0.1);
        }

        .qn-group {
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .dark .qn-group {
            border-color: rgba(255,255,255,0.1);
        }

        .qn-item {
            background-color: #ffffff;
        }
        .dark .qn-item {
            background-color: #1f2937;
        }

        .qn-item-border {
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .qn-item-border {
            border-bottom-color: rgba(255,255,255,0.08);
        }

        .qn-section-divider {
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f1f3;
        }
        .dark .qn-section-divider {
            border-top-color: rgba(255,255,255,0.1);
        }

        .qn-toggle-knob {
            background-color: #ffffff;
        }
    </style>

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        Matikan jenis notifikasi WA tertentu kalau sekolah tidak mau mengirimkannya. Redaksi pesan bisa dikustomisasi di menu "Template Notifikasi Sekolah".
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2" style="gap: 1.5rem;">

        @foreach ($this->getLembagas() as $lembaga)

            @php $jumlahAktif = $this->getJumlahAktif($lembaga->id); @endphp

            <div class="qn-card">

                {{-- HEADER CARD --}}
                <div class="qn-header">
                    <div class="flex items-center" style="gap: 0.75rem;">
                        <div style="border-radius: 0.75rem; background-color: #0f9c94; width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.08);">
                            <x-heroicon-o-building-office-2 style="width: 1.75rem; height: 1.75rem; color: #ffffff;" />
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white" style="font-size: 1rem;">{{ $lembaga->nama }}</h2>
                            <p class="text-gray-500 dark:text-gray-400" style="font-size: 0.75rem;">{{ $jumlahAktif }} dari 22 notifikasi aktif</p>
                        </div>
                    </div>
                </div>

                <div style="padding: 1.5rem;">

                    @foreach ($this->getKatalogPerKategori() as $kategori => $items)

                        <div class="{{ $loop->first ? '' : 'qn-section-divider' }}">

                            <div class="flex items-center" style="gap: 0.5rem; margin-bottom: 1rem;">
                                <div class="flex items-center" style="gap: 0.375rem; border-radius: 0.5rem; background-color: #0f9c94; padding: 0.375rem 0.75rem; box-shadow: 0 1px 2px rgba(15,156,148,0.2); flex-shrink: 0;">
                                    <x-dynamic-component :component="$this->getKategoriIcon($kategori)" style="width: 0.875rem; height: 0.875rem; color: #ffffff;" />
                                    <span class="font-bold uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; color: #ffffff;">{{ $kategori }}</span>
                                </div>
                                <div class="qn-divider"></div>
                            </div>

                            {{-- Grup item jadi SATU container rounded dengan pemisah antar baris,
                                 gaya persis Filament Table. --}}
                            <div class="qn-group">
                                @foreach ($items as $item)
                                    @php $aktif = $this->isEnabled($lembaga->id, $item['key']); @endphp

                                    <div
                                        class="qn-item flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $loop->last ? '' : 'qn-item-border' }}"
                                        style="gap: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;"
                                    >
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['nama'] }}</span>

                                        <button
                                            type="button"
                                            wire:click="toggleNotifikasi({{ $lembaga->id }}, '{{ $item['key'] }}')"
                                            role="switch"
                                            aria-checked="{{ $aktif ? 'true' : 'false' }}"
                                            class="relative inline-flex shrink-0 cursor-pointer outline-none transition-colors duration-200 ease-in-out focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-1 dark:focus-visible:ring-offset-gray-900"
                                            style="height: 1.5rem; width: 2.75rem; border-radius: 9999px; border: 2px solid transparent; background-color: {{ $aktif ? '#0f9c94' : '#e5e7eb' }};"
                                        >
                                            <span
                                                class="qn-toggle-knob pointer-events-none relative inline-block transform transition duration-200 ease-in-out"
                                                style="height: 1.25rem; width: 1.25rem; border-radius: 9999px; box-shadow: 0 1px 2px rgba(0,0,0,0.15); transform: translateX({{ $aktif ? '1.25rem' : '0' }});"
                                            ></span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

</x-filament-panels::page>
