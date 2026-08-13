<x-filament-panels::page>

    <p class="text-sm text-gray-500 -mt-2">
        Matikan jenis notifikasi WA tertentu kalau sekolah tidak mau mengirimkannya. Redaksi pesan bisa dikustomisasi di menu "Template Notifikasi Sekolah".
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @foreach ($this->getLembagas() as $lembaga)

            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">

                <div class="flex items-center gap-3 mb-6">
                    <div class="rounded-xl bg-primary-50 dark:bg-primary-500/10 p-2.5">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600" />
                    </div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $lembaga->nama }}</h2>
                </div>

                @foreach ($this->getKatalogPerKategori() as $kategori => $items)

                    <div class="mb-7 last:mb-0">

                        <span class="inline-block rounded-full bg-primary-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white mb-3">
                            {{ $kategori }}
                        </span>

                        <div class="space-y-2">
                            @foreach ($items as $item)
                                @php $aktif = $this->isEnabled($lembaga->id, $item['key']); @endphp

                                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 text-sm hover:border-gray-200 dark:hover:border-gray-700 transition-colors">
                                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['nama'] }}</span>

                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleNotifikasi({{ $lembaga->id }}, '{{ $item['key'] }}')"
                                            {{ $aktif ? 'checked' : '' }}
                                            class="sr-only peer"
                                        >
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-primary-600 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:after:translate-x-full"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @endforeach

            </div>

        @endforeach

    </div>

</x-filament-panels::page>
