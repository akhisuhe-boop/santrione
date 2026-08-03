<x-filament::page>

    <x-filament::section>

        <form wire:submit="filter">
            {{ $this->form }}
        </form>

    </x-filament::section>

    <div class="mt-4">
        {{ $this->table }}
    </div>

</x-filament::page>
