<x-filament::page>

    <div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-full p-1 mb-4">
        <button
            type="button"
            wire:click="pilihTipe('siswa')"
            class="px-5 py-2 rounded-full text-sm font-bold transition-all {{ $tipe === 'siswa' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500' }}"
        >
            Siswa
        </button>
        <button
            type="button"
            wire:click="pilihTipe('guru')"
            class="px-5 py-2 rounded-full text-sm font-bold transition-all {{ $tipe === 'guru' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500' }}"
        >
            Pegawai
        </button>
    </div>

    <x-filament::section>

        <form wire:submit="filter">
            {{ $this->form }}
        </form>

    </x-filament::section>

    <div class="mt-4">
        {{ $this->table }}
    </div>

</x-filament::page>
