@extends('guru.layout.guru')

@section('content')

{{-- TOAST --}}
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

    {{-- ================= HERO PROFILE ================= --}}

<div
    class="
        relative
        overflow-hidden
        rounded-[28px]
        bg-gradient-to-br
        from-[#00A39D]
        via-[#00B4AC]
        to-[#14C8C0]
        p-5
        text-white
        shadow-xl
        mb-6
    ">

    {{-- ORNAMENT --}}
    <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -mr-16 -mt-16"></div>
    <div class="absolute bottom-0 left-0 w-28 h-28 rounded-full bg-white/5 -ml-10 -mb-10"></div>

    <div class="relative z-10">

        <div class="flex gap-4">

            {{-- FOTO --}}
            <div class="relative">

                <div
                    class="
                        w-[72px]
                        h-[72px]
                        rounded-2xl
                        overflow-hidden
                        bg-white/20
                        backdrop-blur-sm
                        border border-white/20
                        shadow-lg">

                    @if($pegawai->foto)

                        <img
                            src="{{ Storage::disk('r2-public')->url($pegawai->foto) }}"
                            class="w-full h-full object-cover">

                    @else

                        <div class="w-full h-full flex items-center justify-center">

                            <x-heroicon-o-user class="w-10 h-10 text-white"/>

                        </div>

                    @endif

                </div>

                {{-- STATUS DOT --}}
                <div
                    class="
                        absolute
                        -bottom-1
                        -right-1
                        w-5
                        h-5
                        rounded-full
                        bg-emerald-400
                        border-[3px]
                        border-[#00A39D]">
                </div>

            </div>

            {{-- PROFILE --}}
            <div class="flex-1">

                <div class="text-xs text-white/70">

                    Profil Guru / Pegawai

                </div>

                <div class="text-lg font-bold leading-tight">

                    {{ $pegawai->nama }}

                </div>

                <div class="flex flex-wrap gap-2 mt-2">

                    {{-- STATUS --}}
                    <span
                        class="
                            inline-flex items-center gap-1
                            px-3 py-1
                            rounded-full
                            bg-emerald-500/20
                            border border-emerald-300/30
                            text-[11px]
                            font-medium">

                        <x-heroicon-s-check-circle class="w-3.5 h-3.5"/>

                        Guru Aktif

                    </span>

                    {{-- JABATAN --}}
                    <span
                        class="
                            inline-flex items-center gap-1
                            px-3 py-1
                            rounded-full
                            bg-white/15
                            border border-white/20
                            text-[11px]
                            font-medium">

                        <x-heroicon-o-briefcase class="w-3.5 h-3.5"/>

                        {{ $pegawai->jabatan ?? 'Guru' }}

                    </span>

                </div>

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
                        Informasi identitas pegawai
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600">
                    Identity
                </span>
            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Nama</span>
                    <span class="font-medium">{{ $pegawai->nama }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Jenis Kelamin</span>
                    <span class="font-medium">
                        @if($pegawai->jenis_kelamin == 'L')
                            Laki-laki
                        @elseif($pegawai->jenis_kelamin == 'P')
                            Perempuan
                        @else
                            -
                        @endif
                    </span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">No. HP</span>
                    <span class="font-medium">{{ $pegawai->no_hp ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Email</span>
                    <span class="font-medium">{{ $pegawai->email ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Alamat</span>
                    <span class="font-medium text-right max-w-[60%]">
                        {{ $pegawai->alamat ?? '-' }}
                    </span>
                </div>

            </div>

        </div>


        {{-- DATA PEGAWAI --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Data Kepegawaian
                    </h2>
                    <p class="text-xs text-slate-500">
                        Informasi administrasi pegawai
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-teal-50 text-teal-600">
                    Employee
                </span>
            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">NIY</span>
                    <span class="font-medium">{{ $pegawai->niy ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">NIK</span>
                    <span class="font-medium">{{ $pegawai->nik ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Pendidikan</span>
                    <span class="font-medium">{{ $pegawai->pendidikan ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Universitas</span>
                    <span class="font-medium">{{ $pegawai->universitas ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Golongan</span>
                    <span class="font-medium">{{ $pegawai->golongan ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Tanggal Masuk</span>
                    <span class="font-medium">{{ $pegawai->tanggal_masuk ?? '-' }}</span>
                </div>

            </div>

        </div>


        {{-- LEMBAGA --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Penugasan Lembaga
                    </h2>
                    <p class="text-xs text-slate-500">
                        Lembaga & jabatan
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600">
                    Assignment
                </span>
            </div>

            <div class="p-5 text-sm">

                @forelse($pegawai->pegawaiLembagas as $item)
            
                    <div class="border-b border-slate-200 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
            
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-500">Lembaga</span>
                            <span class="font-medium">
                                {{ $item->lembaga->nama ?? '-' }}
                            </span>
                        </div>
            
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-500">Jabatan</span>
                            <span class="font-medium">
                                {{ $item->jabatan ?? '-' }}
                            </span>
                        </div>
            
                        <div class="flex justify-between py-2">
                            <span class="text-slate-500">Status</span>
                            <span class="font-medium">
                                {{ ucfirst($item->status ?? '-') }}
                            </span>
                        </div>
            
                    </div>
            
                @empty
            
                    <div class="text-center py-6 text-slate-500">
                        Belum terdaftar pada lembaga.
                    </div>
            
                @endforelse
            
            </div>

        </div>


        {{-- PASSWORD --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Security Settings
                    </h2>
                    <p class="text-xs text-slate-500">
                        Update password akun
                    </p>
                </div>

                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">
                    Secure
                </span>
            </div>

            <div class="p-5">

                <form action="{{ route('guru.profil.updatePassword') }}"
                      method="POST"
                      class="space-y-3">

                    @csrf

                    <input type="password"
                           name="old_password"
                           placeholder="Password lama"
                           class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <input type="password"
                           name="password"
                           placeholder="Password baru"
                           class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <input type="password"
                           name="password_confirmation"
                           placeholder="Konfirmasi password"
                           class="w-full rounded-xl border border-slate-200 p-2.5 text-sm">

                    <button
                        type="submit"
                        class="w-full bg-[#00A39D] hover:bg-[#018a85] text-white py-2.5 rounded-xl font-semibold">
                        Update Password
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection