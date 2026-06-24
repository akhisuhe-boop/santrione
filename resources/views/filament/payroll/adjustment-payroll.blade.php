<table class="w-full">

    <thead class="border-b border-gray-200 dark:border-white/10">

        <tr>

            <th class="px-2 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Komponen
            </th>

            <th class="w-32 px-2 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Tipe
            </th>

            <th class="px-2 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                Nominal
            </th>

        </tr>

    </thead>

    <tbody class="divide-y divide-gray-100 dark:divide-white/5">

        @foreach($getRecord()->adjustments as $item)

            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">

                <td class="px-2 py-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $item->nama_komponen }}
                </td>

                <td class="px-2 py-2">

                    @if($item->tipe === 'tambahan')

    <span
        style="
            background:#dcfce7;
            color:#15803d;
            border:1px solid #bbf7d0;
        "
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
    >
        Tambahan
    </span>

@else

    <span
        style="
            background:#fee2e2;
            color:#dc2626;
            border:1px solid #fecaca;
        "
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
    >
        Potongan
    </span>

@endif

                </td>

                <td class="px-2 py-2 text-right whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>