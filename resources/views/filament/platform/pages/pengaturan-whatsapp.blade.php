<x-filament-panels::page>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center gap-3 mt-6">
            <x-filament::button type="submit">
                Simpan
            </x-filament::button>

            <x-filament::button type="button" wire:click="testKirim" color="gray" outlined>
                Simpan &amp; Kirim Pesan Uji
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
