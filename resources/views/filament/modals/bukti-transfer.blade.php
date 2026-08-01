<div class="p-4">

    @if($record->bukti_transfer)

        <img
            src="{{ Storage::disk('r2-private')->temporaryUrl($record->bukti_transfer, now()->addMinutes(10)) }}"
            class="w-full rounded-xl border">

    @else

        <div class="text-center text-gray-500">
            Bukti transfer belum tersedia
        </div>

    @endif

</div>