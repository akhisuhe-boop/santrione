<x-filament::page>

    <x-filament::section>
        <form>
            {{ $this->form }}
        </form>
    </x-filament::section>

    {{-- ================= STAT UTAMA ================= --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1rem;">

        @php
            $lulus = collect($this->statusBreakdown)->firstWhere('key', 'lulus')['total'] ?? 0;
            $tidakLulus = collect($this->statusBreakdown)->firstWhere('key', 'tidak_lulus')['total'] ?? 0;
            $aktif = collect($this->statusBreakdown)->firstWhere('key', 'aktif')['total'] ?? 0;
        @endphp

        <x-filament::section>
            <div style="font-size:.75rem;color:rgb(100 116 139);">Total Pendaftar</div>
            <div style="font-size:1.875rem;font-weight:700;margin-top:.25rem;">{{ $this->totalPendaftar }}</div>
        </x-filament::section>

        <x-filament::section>
            <div style="font-size:.75rem;color:rgb(100 116 139);">Lulus Seleksi</div>
            <div style="font-size:1.875rem;font-weight:700;margin-top:.25rem;color:rgb(5 150 105);">{{ $lulus }}</div>
        </x-filament::section>

        <x-filament::section>
            <div style="font-size:.75rem;color:rgb(100 116 139);">Sudah Jadi Siswa Aktif</div>
            <div style="font-size:1.875rem;font-weight:700;margin-top:.25rem;color:rgb(2 132 199);">{{ $aktif }}</div>
        </x-filament::section>

        <x-filament::section>
            <div style="font-size:.75rem;color:rgb(100 116 139);">Tidak Lulus</div>
            <div style="font-size:1.875rem;font-weight:700;margin-top:.25rem;color:rgb(225 29 72);">{{ $tidakLulus }}</div>
        </x-filament::section>

    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;margin-top:1rem;">

        {{-- ================= STATUS BREAKDOWN ================= --}}
        <x-filament::section heading="Pendaftar per Status">
            <table style="width:100%;border-collapse:collapse;">
                <tbody>
                    @foreach($this->statusBreakdown as $item)
                        <tr style="border-bottom:1px solid rgb(241 245 249);">
                            <td style="padding:.6rem .25rem;font-size:.875rem;color:rgb(71 85 105);">{{ $item['label'] }}</td>
                            <td style="padding:.6rem .25rem;font-size:.875rem;font-weight:600;text-align:right;">{{ $item['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>

        {{-- ================= PER LEMBAGA ================= --}}
        <x-filament::section heading="Pendaftar per Lembaga">
            <table style="width:100%;border-collapse:collapse;">
                <tbody>
                    @forelse($this->perLembaga as $item)
                        <tr style="border-bottom:1px solid rgb(241 245 249);">
                            <td style="padding:.6rem .25rem;font-size:.875rem;color:rgb(71 85 105);">{{ $item['lembaga'] }}</td>
                            <td style="padding:.6rem .25rem;font-size:.875rem;font-weight:600;text-align:right;">{{ $item['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td style="padding:1.5rem .25rem;text-align:center;color:rgb(148 163 184);font-size:.875rem;">
                                Belum ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

    </div>

    {{-- ================= PEMBAYARAN ================= --}}
    <div style="margin-top:1rem;">
        <x-filament::section heading="Status Pembayaran Pendaftaran">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;text-align:center;">

                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:rgb(5 150 105);">{{ $this->pembayaranBreakdown['lunas'] }}</div>
                    <div style="font-size:.75rem;color:rgb(100 116 139);margin-top:.25rem;">Lunas</div>
                </div>

                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:rgb(217 119 6);">{{ $this->pembayaranBreakdown['sebagian'] }}</div>
                    <div style="font-size:.75rem;color:rgb(100 116 139);margin-top:.25rem;">Sebagian</div>
                </div>

                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:rgb(225 29 72);">{{ $this->pembayaranBreakdown['belum'] }}</div>
                    <div style="font-size:.75rem;color:rgb(100 116 139);margin-top:.25rem;">Belum Bayar</div>
                </div>

                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:rgb(148 163 184);">{{ $this->pembayaranBreakdown['belum_ada_tagihan'] }}</div>
                    <div style="font-size:.75rem;color:rgb(100 116 139);margin-top:.25rem;">Belum Ada Tagihan</div>
                </div>

            </div>
        </x-filament::section>
    </div>

    {{-- ================= PENDAFTAR TERBARU ================= --}}
    <div style="margin-top:1rem;">
        <x-filament::section heading="Pendaftar Terbaru">
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                    <thead>
                        <tr style="text-align:left;font-size:.75rem;color:rgb(100 116 139);border-bottom:1px solid rgb(226 232 240);">
                            <th style="padding:.5rem .5rem .5rem 0;">Nama</th>
                            <th style="padding:.5rem .5rem;">Lembaga</th>
                            <th style="padding:.5rem .5rem;">Status</th>
                            <th style="padding:.5rem 0 .5rem .5rem;">Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->pendaftarTerbaru as $p)
                            <tr style="border-bottom:1px solid rgb(241 245 249);">
                                <td style="padding:.6rem .5rem .6rem 0;font-weight:600;">{{ $p->nama_lengkap }}</td>
                                <td style="padding:.6rem .5rem;color:rgb(71 85 105);">{{ $p->lembaga?->nama ?? '-' }}</td>
                                <td style="padding:.6rem .5rem;color:rgb(71 85 105);">{{ ucwords(str_replace('_', ' ', $p->status)) }}</td>
                                <td style="padding:.6rem 0 .6rem .5rem;color:rgb(71 85 105);white-space:nowrap;">{{ $p->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding:2rem 0;text-align:center;color:rgb(148 163 184);">
                                    Belum ada pendaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

</x-filament::page>
