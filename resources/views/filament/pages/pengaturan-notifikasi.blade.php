<x-filament-panels::page>

    <p class="text-sm text-gray-500 -mt-2">
        Matikan jenis notifikasi WA tertentu kalau sekolah tidak mau mengirimkannya. Redaksi pesan bisa dikustomisasi di menu "Template Notifikasi Sekolah".
    </p>

    @foreach ($this->getLembagas() as $lembaga)

        <x-filament::section :heading="'Notifikasi — ' . $lembaga->nama" icon="heroicon-o-building-office-2">

            @foreach ($this->getKatalogPerKategori() as $kategori => $items)

                <div class="mb-6 last:mb-0">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $kategori }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($items as $item)
                            @php $aktif = $this->isEnabled($lembaga->id, $item['key']); @endphp

                            <label class="flex items-center justify-between gap-3 rounded-xl border {{ $aktif ? 'border-gray-200 dark:border-gray-700' : 'border-danger-300 bg-danger-50 dark:bg-danger-500/10' }} px-4 py-3 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['nama'] }}</span>

                                <input
                                    type="checkbox"
                                    wire:click="toggleNotifikasi({{ $lembaga->id }}, '{{ $item['key'] }}')"
                                    {{ $aktif ? 'checked' : '' }}
                                    class="rounded text-primary-600 focus:ring-primary-500"
                                >
                            </label>
                        @endforeach
                    </div>
                </div>

            @endforeach

        </x-filament::section>

    @endforeach

</x-filament-panels::page>
