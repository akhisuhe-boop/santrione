<x-filament-panels::page>

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        Matikan jenis notifikasi WA tertentu kalau sekolah tidak mau mengirimkannya. Redaksi pesan bisa dikustomisasi di menu "Template Notifikasi Sekolah".
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @foreach ($this->getLembagas() as $lembaga)

            @php $jumlahAktif = $this->getJumlahAktif($lembaga->id); @endphp

            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">

                {{-- HEADER CARD --}}
                <div
                    class="px-6 py-5 border-b border-gray-100 dark:border-gray-800"
                    style="background: linear-gradient(135deg, rgba(15,156,148,0.06) 0%, rgba(255,255,255,0) 100%);"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-xl p-2.5 shadow-sm shrink-0"
                            style="background-color: #0f9c94;"
                        >
                            <x-heroicon-o-building-office-2 style="width: 1.25rem; height: 1.25rem; color: #ffffff;" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $lembaga->nama }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jumlahAktif }} dari 22 notifikasi aktif</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">

                    @foreach ($this->getKatalogPerKategori() as $kategori => $items)

                        <div class="mb-10 last:mb-0 pt-8 first:pt-0 border-t-2 border-gray-100 dark:border-gray-800 first:border-0">

                            <div class="flex items-center gap-2 mb-4">
                                <div
                                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 shadow-sm shrink-0"
                                    style="background-color: #0f9c94;"
                                >
                                    <x-dynamic-component :component="$this->getKategoriIcon($kategori)" style="width: 0.875rem; height: 0.875rem; color: #ffffff;" />
                                    <span class="text-xs font-bold uppercase tracking-wide" style="color: #ffffff;">{{ $kategori }}</span>
                                </div>
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            </div>

                            {{-- Grup item jadi SATU container rounded dengan pemisah antar baris,
                                 gaya persis Filament Table (bukan kotak-kotak lepas per baris) --}}
                            <div class="rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden">
                                @foreach ($items as $item)
                                    @php $aktif = $this->isEnabled($lembaga->id, $item['key']); @endphp

                                    <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['nama'] }}</span>

                                        <button
                                            type="button"
                                            wire:click="toggleNotifikasi({{ $lembaga->id }}, '{{ $item['key'] }}')"
                                            role="switch"
                                            aria-checked="{{ $aktif ? 'true' : 'false' }}"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent outline-none transition-colors duration-200 ease-in-out focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-1 dark:focus-visible:ring-offset-gray-900 {{ $aktif ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                        >
                                            <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $aktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
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
