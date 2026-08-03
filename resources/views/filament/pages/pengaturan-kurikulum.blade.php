<x-filament::page>

    <x-filament::section heading="Daftar Lembaga">
        {{ $this->table }}
    </x-filament::section>

    <div class="mt-6">

        <form wire:submit="save">

            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit">
                    Simpan Pengaturan
                </x-filament::button>
            </div>

        </form>

    </div>

</x-filament::page>
