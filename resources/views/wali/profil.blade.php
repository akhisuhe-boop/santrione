@extends('wali.layout.wali')

@section('content')

{{-- TOAST NOTIFICATION --}}
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

    {{-- ================= HERO ================= --}}
    <div class="relative rounded-3xl overflow-hidden border border-slate-100 shadow-sm bg-gradient-to-br from-[#00A39D] to-emerald-500">

        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top_left,white,transparent_60%)]"></div>

        <div class="relative p-5 flex items-center gap-4">

            {{-- FOTO --}}
            <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-white/30 bg-white/10 shrink-0">
                @if($siswa->foto)
                    <img src="{{ Storage::disk('r2-public')->url($siswa->foto) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-xs text-white/80">
                        No Photo
                    </div>
                @endif
            </div>

            {{-- INFO --}}
            <div class="flex-1">
                <div class="text-xs text-white/80">Nama Siswa</div>
                <div class="text-xl font-bold text-white mt-1">
                    {{ $siswa->nama_lengkap }}
                </div>

                <div class="mt-3 flex gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-white">
                        NIS: <b>{{ $siswa->nis ?? '-' }}</b>
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-white">
                        NISN: <b>{{ $siswa->nisn ?? '-' }}</b>
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= GRID ================= --}}
    <div class="space-y-5">

        {{-- ================= PERSONAL ================= --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Data Personal</h2>
                    <p class="text-xs text-slate-500">Informasi identitas santri</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600">Identity</span>
            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Tempat Lahir</span>
                    <span class="font-medium">{{ $siswa->tempat_lahir ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Tanggal Lahir</span>
                    <span class="font-medium">{{ $siswa->tanggal_lahir ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Alamat</span>
                    <span class="font-medium text-right max-w-[60%]">
                        {{ $siswa->alamat_jalan ?? '-' }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ================= AKADEMIK ================= --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Data Akademik</h2>
                    <p class="text-xs text-slate-500">Kelas & identitas akademik</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-teal-50 text-teal-600">Academic</span>
            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">NIS</span>
                    <span class="font-medium">{{ $siswa->nis ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">NISN</span>
                    <span class="font-medium">{{ $siswa->nisn ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Kelas</span>
                    <span class="font-medium">{{ $siswa->kelas->nama ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Asal Sekolah</span>
                    <span class="font-medium">{{ $siswa->asal_sekolah ?? '-' }}</span>
                </div>

            </div>
        </div>

        {{-- ================= ORANG TUA ================= --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Data Orang Tua</h2>
                    <p class="text-xs text-slate-500">Ayah & Ibu</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600">Family</span>
            </div>

            <div class="p-5 text-sm divide-y divide-slate-100">

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Nama Ayah</span>
                    <span class="font-medium">{{ $siswa->nama_ayah ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">WA Ayah</span>
                    <span class="font-medium">{{ $siswa->wa_ayah ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Nama Ibu</span>
                    <span class="font-medium">{{ $siswa->nama_ibu ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-2">
                    <span class="text-slate-500">WA Ibu</span>
                    <span class="font-medium">{{ $siswa->wa_ibu ?? '-' }}</span>
                </div>

            </div>
        </div>

        {{-- ================= SECURITY ================= --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Security Settings</h2>
                    <p class="text-xs text-slate-500">Update password akun</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">Secure</span>
            </div>

            <div class="p-5">

                <form action="{{ route('wali.profil.updatePassword') }}"
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

                    <button type="submit"
                            class="w-full bg-[#00A39D] text-white py-2.5 rounded-xl font-semibold text-sm hover:bg-[#018a85]">
                        Update Password
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection