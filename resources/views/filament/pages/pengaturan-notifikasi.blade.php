<x-filament-panels::page>

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        Matikan jenis notifikasi WA tertentu kalau sekolah tidak mau mengirimkannya. Redaksi pesan bisa dikustomisasi di menu "Template Notifikasi Sekolah".
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @foreach ($this->getLembagas() as $lembaga)

            @php $jumlahAktif = $this->getJumlahAktif($lembaga->id); @endphp

            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">

                {{-- HEADER CARD --}}
                <div class="px-6 py-5 bg-gradient-to-br from-primary-50 to-white dark:from-primary-500/10 dark:to-gray-900 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-primary-600 p-2.5 shadow-sm">
                                <x-heroicon-o-building-office-2 class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $lembaga->nama }}</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jumlahAktif }} dari 22 notifikasi aktif</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">

                    @foreach ($this->getKatalogPerKategori() as $kategori => $items)

                        <div class="mb-7 last:mb-0">

                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 shadow-sm shadow-primary-600/20">
                                    <x-dynamic-component :component="$this->getKategoriIcon($kategori)" class="w-3.5 h-3.5 text-white" />
                                    <span class="text-xs font-bold uppercase tracking-wide text-white">{{ $kategori }}</span>
                                </div>
                                <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                            </div>

                            <div class="space-y-1.5">
                                @foreach ($items as $item)
                                    @php $aktif = $this->isEnabled($lembaga->id, $item['key']); @endphp

                                    <div class="flex items-center justify-between gap-3 rounded-xl px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
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
