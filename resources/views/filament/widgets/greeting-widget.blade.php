<x-filament-widgets::widget class="px-1 py-0">

    @php
        $user = auth()->user();

        $role =
            $user->roles->first()?->name
            ?? 'User';
    @endphp

    <div
        class="
            rounded-[40px]
            bg-[#f3f3f3]
            px-8
            py-2
        "
    >

        <div class="flex items-center justify-between">

            {{-- LEFT --}}
            <div>

                {{-- Greeting --}}
                <div
                    class="
                        text-[18px]
                        font-medium
                        text-gray-700
                    "
                >
                    Selamat Datang
                </div>

                {{-- Name --}}
                <div
                    class="
                        mt-1
                        text-[72px]
                        font-black
                        text-emerald-600
                        leading-none
                    "
                >
                    {{ strtoupper($user->name) }}
                </div>

                {{-- Role --}}
                <div
                    class="
                        mt-1
                        text-[22px]
                        text-gray-700
                    "
                >
                    Sebagai
                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                </div>

            </div>

{{-- RIGHT --}}
<div class="flex items-center gap-4">

    {{-- Hari --}}
    <div
        class="
            rounded-xl
            bg-white
            px-6
            py-2
            shadow-sm
            ring-1
            ring-gray-300
        "
    >

        <div class="text-center text-[13px] text-gray-500">
            Hari
        </div>

        <div class="mt-[2px] text-center text-[20px] font-bold text-black">
            {{ now()->translatedFormat('l') }}
        </div>

    </div>

    {{-- Tanggal --}}
    <div
        class="
            rounded-xl
            bg-white
            px-8
            py-2
            shadow-sm
            ring-1
            ring-gray-300
        "
    >

        <div class="text-center text-[13px] text-gray-500">
            Tanggal
        </div>

        <div
            class="
                mt-[2px]
                whitespace-nowrap
                text-center
                text-[18px]
                font-bold
                text-black
            "
        >
            {{ now()->translatedFormat('d F Y') }}
        </div>

    </div>

    {{-- Jam --}}
    <div
        class="
            rounded-xl
            bg-white
            px-6
            py-2
            shadow-sm
            ring-1
            ring-gray-300
        "
    >

        <div class="text-center text-[13px] text-gray-500">
            Jam
        </div>

        <div class="mt-[2px] text-center text-[20px] font-bold text-black">
            {{ now()->format('H:i') }}
        </div>

    </div>

</div>

        </div>

    </div>

</x-filament-widgets::widget>