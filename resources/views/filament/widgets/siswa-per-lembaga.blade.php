<x-filament-widgets::widget>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        @foreach ($this->getLembagaData() as $lembaga)

            <div
                class="
                    fi-wi-stats-overview-stat
                    rounded-xl
                    bg-white
                    p-6
                    shadow-sm
                    ring-1
                    ring-gray-950/5
                "
            >

                <div class="flex items-center justify-between">

                    <div>

                        <div class="text-sm text-gray-500">
                            {{ $lembaga->nama}}
                        </div>

                        <div class="mt-3 text-3xl font-bold tracking-tight">
                            {{ $lembaga->siswas_count }}
                        </div>

                        <div class="mt-1 text-sm text-success-600">
                            Total Siswa
                        </div>

                    </div>

                    <div
                        class="
                            rounded-full
                            bg-primary-50
                            p-3
                            text-primary-600
                        "
                    >

                        <x-heroicon-m-user-group
                            class="w-10 h-10"
                        />

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</x-filament-widgets::widget>