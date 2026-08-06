@extends('ppdb.layout.ppdb')

@section('content')
@php
    switch ($ppdb->status) {
        case 'draft':
            $statusTitle = 'Akun Berhasil Dibuat';
            $statusClass = 'bg-blue-100 text-blue-700';
            break;

        case 'menunggu_pembayaran':
            $statusTitle = 'Menunggu Pembayaran';
            $statusClass = 'bg-amber-100 text-amber-700';
            break;

        case 'formulir':
            $statusTitle = 'Lengkapi Formulir';
            $statusClass = 'bg-emerald-100 text-emerald-700';
            break;

        case 'upload_berkas':
            $statusTitle = 'Upload Berkas';
            $statusClass = 'bg-indigo-100 text-indigo-700';
            break;

        case 'verifikasi_berkas':
            $statusTitle = 'Verifikasi Berkas';
            $statusClass = 'bg-orange-100 text-orange-700';
            break;

        case 'tes':
            $statusTitle = 'Tes Seleksi';
            $statusClass = 'bg-purple-100 text-purple-700';
            break;

        case 'lulus':
            $statusTitle = 'Lulus Seleksi';
            $statusClass = 'bg-emerald-100 text-emerald-700';
            break;

        case 'daftar_ulang':
            $statusTitle = 'Daftar Ulang';
            $statusClass = 'bg-cyan-100 text-cyan-700';
            break;

        default:
            $statusTitle = 'Peserta Didik Aktif';
            $statusClass = 'bg-slate-100 text-slate-700';
            break;
    }
@endphp

@if(session('success'))
<div x-data="{ show: true }"
     x-init="setTimeout(() => show = false, 3000)"
     x-show="show"
     x-transition
     class="fixed top-5 right-5 z-50 bg-emerald-500 text-white px-4 py-3 rounded-2xl shadow-xl text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }"
     x-init="setTimeout(() => show = false, 3000)"
     x-show="show"
     x-transition
     class="fixed top-5 right-5 z-50 bg-red-500 text-white px-4 py-3 rounded-2xl shadow-xl text-sm">
    {{ session('error') }}
</div>
@endif

<div class="p-4 bg-gradient-to-b from-slate-50 via-white to-slate-100 min-h-screen space-y-6">

    {{-- HERO --}}
    <div class="relative rounded-3xl overflow-hidden border border-slate-100 shadow-sm bg-gradient-to-br from-[#00A39D] to-emerald-500">

        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top_left,white,transparent_60%)]"></div>

        <div class="relative p-5 flex items-center gap-4">

            <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-white/30 bg-white/10 shrink-0">

                @if($ppdb->foto)
                    <img src="{{ \App\Support\FileUrlResolver::public($ppdb->foto) }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-xs text-white/80">
                        No Photo
                    </div>
                @endif

            </div>

            <div class="flex-1">

                <div class="text-xs text-white/80">
                    Calon Siswa
                </div>

                <div class="text-xl font-bold text-white mt-1">
                    {{ $ppdb->nama_lengkap }}
                </div>

                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    
                    <span class="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-white">
                        NISN :
                        <b>{{ $ppdb->nisn ?? '-' }}</b>
                    </span>
                    
                    <span class="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-white">
                        {{ $ppdb->lembaga->nama ?? 'Belum Memilih Lembaga' }}
                    </span>

                </div>

            </div>

        </div>

    </div>


    <div class="space-y-5">

        {{-- DATA PERSONAL --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Data Personal
                    </h2>
                    <p class="text-xs text-slate-500">
                        Informasi identitas calon peserta didik
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600">
                    Identity
                </span>

            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Jenis Kelamin</span>
                    <span class="font-medium">
                        @switch($ppdb->jenis_kelamin)
                            @case('L')
                                Laki-laki
                                @break
                    
                            @case('P')
                                Perempuan
                                @break
                    
                            @default
                                -
                        @endswitch
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Tempat Lahir</span>
                    <span class="font-medium">
                        {{ $ppdb->tempat_lahir ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Tanggal Lahir</span>
                    <span class="font-medium">
                        {{ $ppdb->tanggal_lahir ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Alamat</span>
                    <span class="font-medium text-right max-w-[60%]">
                        {{ $ppdb->alamat_jalan ?? '-' }}
                    </span>
                </div>

            </div>

        </div>


        {{-- DATA PPDB --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">

                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Data Pendaftaran
                    </h2>
                    <p class="text-xs text-slate-500">
                        Informasi proses PPDB
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-teal-50 text-teal-600">
                    PPDB
                </span>

            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">NISN</span>
                    <span class="font-medium">
                        {{ $ppdb->nisn ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Asal Sekolah</span>
                    <span class="font-medium">
                        {{ $ppdb->asal_sekolah ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Pilihan Lembaga</span>
                    <span class="font-medium">
                        {{ $ppdb->lembaga->nama ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2 items-center">
                
                    <span class="text-slate-500">
                        Status
                    </span>
                
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                        {{ $statusTitle }}
                    </span>
                
                </div>

            </div>

        </div>


        {{-- ORANG TUA --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">

                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Data Orang Tua
                    </h2>

                    <p class="text-xs text-slate-500">
                        Ayah & Ibu
                    </p>

                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600">
                    Family
                </span>

            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Nama Ayah</span>
                    <span class="font-medium">
                        {{ $ppdb->nama_ayah ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">No. HP Ayah</span>
                    <span class="font-medium">
                        {{ $ppdb->wa_ayah ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Nama Ibu</span>
                    <span class="font-medium">
                        {{ $ppdb->nama_ibu ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">No. HP Ibu</span>
                    <span class="font-medium">
                        {{ $ppdb->wa_ibu ?? '-' }}
                    </span>
                </div>

            </div>

        </div>


        {{-- SECURITY --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">

                <div>

                    <h2 class="text-sm font-semibold text-slate-900">
                        Security Settings
                    </h2>

                    <p class="text-xs text-slate-500">
                        Update password akun PPDB
                    </p>

                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">
                    Secure
                </span>

            </div>

            <div class="p-5">

                <form action="{{ route('ppdb.profil.updatePassword') }}"
                      method="POST"
                      class="space-y-3">

                    @csrf

                    <input
                        type="password"
                        name="old_password"
                        placeholder="Password lama"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <input
                        type="password"
                        name="password"
                        placeholder="Password baru"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Konfirmasi password"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <button
                        type="submit"
                        class="w-full bg-[#00A39D] text-white py-2.5 rounded-xl font-semibold hover:bg-[#00857f]">

                        Update Password

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection