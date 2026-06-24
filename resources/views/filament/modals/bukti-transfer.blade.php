<div class="p-4">

    @if($record->bukti_transfer)

        <img
            src="{{ asset('storage/' . $record->bukti_transfer) }}"
            class="w-full rounded-xl border">

    @else

        <div class="text-center text-gray-500">
            Bukti transfer belum tersedia
        </div>

    @endif

</div>