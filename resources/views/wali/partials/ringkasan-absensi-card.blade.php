{{-- Partial kartu ringkasan absensi.
     Variabel: $judul, $subjudul, $rows (array of ['label','keterangan','color','value']) --}}

<div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm mb-5">

    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/30">
        <div class="text-sm font-semibold text-slate-900">
            {{ $judul }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5">
            {{ $subjudul }}
        </div>
    </div>

    <div class="divide-y divide-slate-100">

        @foreach ($rows as $row)

            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
                <div class="flex items-center gap-3">

                    <div class="w-9 h-9 rounded-2xl bg-{{ $row['color'] }}-50 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-{{ $row['color'] }}-500"></div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-slate-900 leading-tight">{{ $row['label'] }}</div>
                        <div class="text-[11px] text-slate-500">{{ $row['keterangan'] }}</div>
                    </div>

                </div>

                <div class="text-base font-semibold text-slate-900">
                    {{ $row['value'] }}
                </div>
            </div>

        @endforeach

    </div>
</div>
