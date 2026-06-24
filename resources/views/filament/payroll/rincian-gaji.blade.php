<table class="w-full">

    <thead class="border-b border-gray-200 dark:border-white/10">

        <tr>

            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Komponen
            </th>

            <th class="w-20 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Qty
            </th>

            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Tarif
            </th>

            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                Subtotal
            </th>

        </tr>

    </thead>

    <tbody class="divide-y divide-gray-100 dark:divide-white/5">

        @foreach($getRecord()->items as $item)

            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">

                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $item->nama_komponen }}
                </td>

                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ $item->qty }}
                </td>

                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    Rp {{ number_format($item->tarif,0,',','.') }}
                </td>

                <td class="px-4 py-2 text-right whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>