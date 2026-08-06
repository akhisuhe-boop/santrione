@extends('ppdb.layout.ppdb')
@php

$penghasilanOptions = [
    'Tidak Berpenghasilan',
    '< Rp500.000',
    'Rp500.000 - Rp999.999',
    'Rp1.000.000 - Rp1.999.999',
    'Rp2.000.000 - Rp4.999.999',
    'Rp5.000.000 - Rp9.999.999',
    '≥ Rp10.000.000',
];

$pendidikanOptions = [
    'Tidak Sekolah',
    'SD / MI',
    'SMP / MTs',
    'SMA / MA / SMK',
    'S1',
    'S2',
    'S3',
];

$pekerjaanOptions = [
    'Belum Bekerja',
    'Mengurus Rumah Tangga',
    'PNS',
    'PPPK',
    'TNI',
    'POLRI',
    'Guru',
    'Dosen',
    'Pegawai Swasta',
    'Pegawai BUMN',
    'Pegawai BUMD',
    'Wiraswasta',
    'Pedagang',
    'Petani',
    'Peternak',
    'Nelayan',
    'Buruh',
    'Sopir / Driver',
    'Ojek Online',
    'Dokter',
    'Perawat',
    'Bidan',
    'Apoteker',
    'Pengacara',
    'Notaris',
    'Arsitek',
    'Konsultan',
    'Lainnya',
];

@endphp
@section('content')

<div
    x-data="wizardForm()"
    class="max-w-3xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="mb-5">
        <h1 class="text-base font-bold text-slate-800">
            Formulir PPDB
        </h1>
    
        <p class="mt-0.5 text-xs text-slate-500">
            Lengkapi data calon peserta didik.
        </p>
    
    </div>

    {{-- CARD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    
        {{-- PROGRESS --}}
        <div class="p-4 border-b border-slate-100">
    
            <div class="flex items-center justify-between mb-3">
    
                <div>
    
                    <h3 class="text-sm font-semibold text-slate-800">
                        Step
                        <span x-text="step"></span>
                        dari 4
                    </h3>
    
                    <p class="text-xs text-slate-500">
                        Lengkapi seluruh data.
                    </p>
    
                </div>
    
                <div class="text-xs font-bold text-[#00A39D]">
                    <span x-text="progress"></span>%
                </div>
    
            </div>
    
            {{-- BAR --}}
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
    
                <div
                    class="h-full bg-[#00A39D] transition-all duration-500"
                    :style="'width:'+progress+'%'">
                </div>
    
            </div>
    
            {{-- STEP INDICATOR --}}
            <div class="flex justify-between mt-4">
    
                <template x-for="i in 4">
    
                    <div class="flex flex-col items-center flex-1">
    
                        <div
                            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
    
                            :class="step >= i
                                ? 'bg-[#00A39D] text-white shadow-sm'
                                : 'bg-slate-100 text-slate-400'">
    
                            <span x-text="i"></span>
    
                        </div>
    
                        <p
                            class="mt-1.5 text-[11px] text-center text-slate-500 leading-tight"
    
                            x-text="titles[i-1]">
    
                        </p>
    
                    </div>
    
                </template>
    
            </div>
    
        </div>
    
        {{-- FORM --}}
        <form
            action="{{ route('ppdb.formulir.store') }}"
            method="POST"
            enctype="multipart/form-data">
    
            @csrf
    
            <div class="p-5 min-h-[380px]">

                {{-- STEP 1 --}}
                <div
                    id="step-1"
                    x-show="step==1"
                    x-transition>
                
                    <div class="mb-5">
                
                        <h2 class="text-base font-semibold text-slate-800">
                            Data Pribadi
                        </h2>
                
                        <p class="text-xs text-slate-500">
                            Lengkapi identitas calon peserta didik.
                        </p>
                
                    </div>
                
                    {{-- FOTO --}}
                    <div class="mb-6 flex justify-center">
                        <div
                        x-data="{
                            preview:'{{ $ppdb->foto ? \App\Support\FileUrlResolver::public($ppdb->foto) : '' }}'
                        }">
                
                            <label class="cursor-pointer">
                
                                <div
                                    class="w-28 h-28 rounded-2xl overflow-hidden
                                           border-2 border border-slate-300
                                           bg-slate-50 flex items-center justify-center">
                
                                    <template x-if="preview">
                
                                        <img
                                            :src="preview"
                                            class="w-full h-full object-cover">
                
                                    </template>
                
                                    <template x-if="!preview">
                
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-10 h-10 text-slate-400"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M15.75 6.75h.008v.008h-.008V6.75zm-7.5 0h.008v.008H8.25V6.75zM4.5 7.5A2.25 2.25 0 016.75 5.25h1.318a1.5 1.5 0 001.06-.44l.62-.62a1.5 1.5 0 011.06-.44h2.384a1.5 1.5 0 011.06.44l.62.62a1.5 1.5 0 001.06.44h1.318A2.25 2.25 0 0119.5 7.5v9A2.25 2.25 0 0117.25 18.75H6.75A2.25 2.25 0 014.5 16.5v-9zm7.5 8.25a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z"/>
                
                                        </svg>
                
                                    </template>
                
                                </div>
                
                                <input
                                type="file"
                                name="foto"
                                class="hidden"
                                accept=".jpg,.jpeg,.png,.webp,image/*"
                                @change="
                                    const file = $event.target.files[0];
                            
                                    if(!file) return;
                            
                                    if(file.size > 2 * 1024 * 1024){
                                        alert('Ukuran foto maksimal 2 MB');
                                        $event.target.value='';
                                        preview=null;
                                        return;
                                    }
                            
                                    preview = URL.createObjectURL(file);
                                ">
                            </label>
                
                            <p class="mt-2 text-center text-xs text-slate-500">
                                Upload Foto
                            </p>
                
                        </div>
                    </div>
                
                    <div class="grid md:grid-cols-2 gap-4">
                
                        {{-- Nama --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Nama Lengkap
                            </label>
                
                            <input
                                type="text"
                                name="nama_lengkap"
                                readonly
                                value="{{ old('nama_lengkap',$ppdb->nama_lengkap) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                                
                                <p class="mt-1 text-xs text-slate-500">
                                    Data pada saat registrasi dan tidak dapat diubah.
                                </p>
                
                        </div>
                
                        {{-- NISN --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                NISN
                            </label>
                
                            <input
                                type="text"
                                name="nisn"
                                readonly
                                value="{{ old('nisn',$ppdb->nisn) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                                
                                <p class="mt-1 text-xs text-slate-500">
                                    Data pada saat registrasi dan tidak dapat diubah.
                                </p>
                
                        </div>
                        
                
                        {{-- Asal Sekolah --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Asal Sekolah
                            </label>
                
                            <input
                                type="text"
                                name="asal_sekolah"
                                required
                                value="{{ old('asal_sekolah',$ppdb->asal_sekolah) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                        {{-- NIK --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                NIK
                            </label>
                
                            <input
                                type="text"
                                name="nik"
                                required
                                value="{{ old('nik',$ppdb->nik) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                        {{-- Jenis Kelamin --}}
                        <div>
                
                            <label class="block mb-2 text-xs font-medium text-slate-600">
                                Jenis Kelamin
                            </label>
                
                            <div class="flex gap-5">
                
                                <label class="flex items-center gap-2 text-sm">
                
                                    <input
                                        type="radio"
                                        name="jenis_kelamin"
                                        required
                                        value="L"
                                        @checked(old('jenis_kelamin',$ppdb->jenis_kelamin)=='L')>
                
                                    Laki-laki
                
                                </label>
                
                                <label class="flex items-center gap-2 text-sm">
                
                                    <input
                                        type="radio"
                                        name="jenis_kelamin"
                                        required
                                        value="P"
                                        @checked(old('jenis_kelamin',$ppdb->jenis_kelamin)=='P')>
                
                                    Perempuan
                
                                </label>
                
                            </div>
                
                        </div>
                
                        {{-- Gol Darah --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Golongan Darah
                            </label>
                
                            <select
                                name="golongan_darah"
                                required
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                                <option value="">Pilih</option>
                
                                @foreach(['A','B','AB','O'] as $g)
                
                                    <option
                                        value="{{ $g }}"
                                        @selected(old('golongan_darah',$ppdb->golongan_darah)==$g)>
                
                                        {{ $g }}
                
                                    </option>
                
                                @endforeach
                
                            </select>
                
                        </div>
                
                        {{-- Tempat Lahir --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Tempat Lahir
                            </label>
                
                            <input
                                type="text"
                                name="tempat_lahir"
                                required
                                value="{{ old('tempat_lahir',$ppdb->tempat_lahir) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                        {{-- Tanggal Lahir --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Tanggal Lahir
                            </label>
                
                            <input
                                type="date"
                                name="tanggal_lahir"
                                required
                                value="{{ old('tanggal_lahir',$ppdb->tanggal_lahir) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                        {{-- Tinggi --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Tinggi Badan (cm)
                            </label>
                
                            <input
                                type="number"
                                name="tinggi_badan"
                                required
                                value="{{ old('tinggi_badan',$ppdb->tinggi_badan) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                        {{-- Berat --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Berat Badan (kg)
                            </label>
                
                            <input
                                type="number"
                                name="berat_badan"
                                required
                                value="{{ old('berat_badan',$ppdb->berat_badan) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                        </div>
                
                    </div>
                
                </div>

                {{-- STEP 2 --}}
                <div
                    id="step-2"
                    x-show="step==2"
                    x-transition>
                
                    <div class="mb-5">
                
                        <h2 class="text-base font-semibold text-slate-800">
                            Data Alamat
                        </h2>
                
                        <p class="text-xs text-slate-500">
                            Lengkapi alamat tempat tinggal calon peserta didik.
                        </p>
                
                    </div>
                
                    <div class="grid md:grid-cols-2 gap-4">
                
                        {{-- Alamat --}}
                        <div class="md:col-span-2">
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Alamat Jalan
                            </label>
                
                            <textarea
                                name="alamat_jalan"
                                required
                                rows="3"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 resize-none">{{ old('alamat_jalan',$ppdb->alamat_jalan) }}</textarea>
                
                        </div>
                
                        {{-- Provinsi --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Provinsi
                            </label>
                
                            <input
                                type="text"
                                name="provinsi"
                                required
                                value="{{ old('provinsi',$ppdb->provinsi) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- Kabupaten --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Kabupaten / Kota
                            </label>
                
                            <input
                                type="text"
                                name="kabupaten"
                                required
                                value="{{ old('kabupaten',$ppdb->kabupaten) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- Kecamatan --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Kecamatan
                            </label>
                
                            <input
                                type="text"
                                name="kecamatan"
                                required
                                value="{{ old('kecamatan',$ppdb->kecamatan) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- Desa --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Desa / Kelurahan
                            </label>
                
                            <input
                                type="text"
                                name="desa"
                                required
                                value="{{ old('desa',$ppdb->desa) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- RT --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                RT
                            </label>
                
                            <input
                                type="text"
                                name="rt"
                                required
                                value="{{ old('rt',$ppdb->rt) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- RW --}}
                        <div>
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                RW
                            </label>
                
                            <input
                                type="text"
                                name="rw"
                                required
                                value="{{ old('rw',$ppdb->rw) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                        {{-- Kode Pos --}}
                        <div class="md:col-span-2">
                
                            <label class="block mb-1 text-xs font-medium text-slate-600">
                                Kode Pos
                            </label>
                
                            <input
                                type="text"
                                name="kode_pos"
                                required
                                value="{{ old('kode_pos',$ppdb->kode_pos) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                       focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                        </div>
                
                    </div>
                
                </div>
    
                {{-- STEP 3 --}}
                <div
                    id="step-3"
                    x-show="step==3"
                    x-transition>
                
                    <div class="mb-5">
                
                        <h2 class="text-base font-semibold text-slate-800">
                            Data Orang Tua
                        </h2>
                
                        <p class="text-xs text-slate-500">
                            Lengkapi data orang tua sesuai Kartu Keluarga.
                        </p>
                
                    </div>
                
                    {{-- Nomor KK --}}
                    <div class="mb-5">
                
                        <label class="block mb-1 text-xs font-medium text-slate-600">
                            Nomor Kartu Keluarga
                        </label>
                
                        <input
                            type="text"
                            name="no_kartu_keluarga"
                            required
                            value="{{ old('no_kartu_keluarga',$ppdb->no_kartu_keluarga) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                   focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                    </div>
                
                    {{-- DATA AYAH --}}
                    <div class="rounded-2xl border border-slate-200 p-4 mb-5">
                
                        <h3 class="font-semibold text-slate-800 mb-4">
                            Data Ayah
                        </h3>
                
                        <div class="grid md:grid-cols-2 gap-4">
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    NIK Ayah
                                </label>
                
                                <input
                                    type="text"
                                    name="nik_ayah"
                                    required
                                    value="{{ old('nik_ayah',$ppdb->nik_ayah) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Nama Ayah
                                </label>
                
                                <input
                                    type="text"
                                    name="nama_ayah"
                                    required
                                    value="{{ old('nama_ayah',$ppdb->nama_ayah) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Status
                                </label>
                
                                <select
                                    name="status_ayah"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                                    <option value="">Pilih Status</option>
                
                                    <option value="Hidup" @selected(old('status_ayah',$ppdb->status_ayah)=='Hidup')>Hidup</option>
                
                                    <option value="Meninggal" @selected(old('status_ayah',$ppdb->status_ayah)=='Meninggal')>Meninggal</option>
                
                                </select>
                
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pekerjaan
                                </label>
                                
                                    <select
                                    name="pekerjaan_ayah"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pekerjaan</option>
                                
                                    @foreach($pekerjaanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pekerjaan_ayah',$ppdb->pekerjaan_ayah)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                    </select>
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pendidikan
                                </label>
                
                                <select
                                    name="pendidikan_ayah"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pendidikan</option>
                                
                                    @foreach($pendidikanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pendidikan_ayah',$ppdb->pendidikan_ayah)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                </select>
                            </div>
                
                            <div>

                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Penghasilan
                                </label>
                            
                                <select
                                    name="penghasilan_ayah"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                            
                                    <option value="">Pilih Penghasilan</option>
                            
                                    @foreach($penghasilanOptions as $item)
                            
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('penghasilan_ayah', $ppdb->penghasilan_ayah) == $item)>
                            
                                            {{ $item }}
                            
                                        </option>
                            
                                    @endforeach
                            
                                </select>
                            
                            </div>
                
                            <div class="md:col-span-2">
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    WhatsApp Ayah
                                </label>
                
                                <input
                                    type="text"
                                    name="wa_ayah"
                                    required
                                    value="{{ old('wa_ayah',$ppdb->wa_ayah) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                        </div>
                
                    </div>
                
                    {{-- DATA IBU --}}
                    <div class="rounded-2xl border border-slate-200 p-4">
                
                        <h3 class="font-semibold text-slate-800 mb-4">
                            Data Ibu
                        </h3>
                
                        <div class="grid md:grid-cols-2 gap-4">
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    NIK Ibu
                                </label>
                
                                <input
                                    type="text"
                                    name="nik_ibu"
                                    required
                                    value="{{ old('nik_ibu',$ppdb->nik_ibu) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Nama Ibu
                                </label>
                
                                <input
                                    type="text"
                                    name="nama_ibu"
                                    required
                                    value="{{ old('nama_ibu',$ppdb->nama_ibu) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Status
                                </label>
                
                                <select
                                    name="status_ibu"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                
                                    <option value="">Pilih Status</option>
                
                                    <option value="Hidup" @selected(old('status_ibu',$ppdb->status_ibu)=='Hidup')>Hidup</option>
                
                                    <option value="Meninggal" @selected(old('status_ibu',$ppdb->status_ibu)=='Meninggal')>Meninggal</option>
                
                                </select>
                
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pekerjaan
                                </label>
                
                                <select
                                    name="pekerjaan_ibu"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pekerjaan</option>
                                
                                    @foreach($pekerjaanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pekerjaan_ibu',$ppdb->pekerjaan_ibu)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                </select>
                            </div>
                
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pendidikan
                                </label>
                
                                <select
                                    name="pendidikan_ibu"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pendidikan</option>
                                
                                    @foreach($pendidikanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pendidikan_ibu',$ppdb->pendidikan_ibu)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                </select>
                            </div>
                
                            <div>

                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Penghasilan
                                </label>
                            
                                <select
                                    name="penghasilan_ibu"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                            
                                    <option value="">Pilih Penghasilan</option>
                            
                                    @foreach($penghasilanOptions as $item)
                            
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('penghasilan_ibu', $ppdb->penghasilan_ibu) == $item)>
                            
                                            {{ $item }}
                            
                                        </option>
                            
                                    @endforeach
                            
                                </select>
                            
                            </div>
                
                            <div class="md:col-span-2">
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    WhatsApp Ibu
                                </label>
                
                                <input
                                    type="text"
                                    name="wa_ibu"
                                    required
                                    value="{{ old('wa_ibu',$ppdb->wa_ibu) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                            </div>
                
                        </div>
                
                    </div>
                
                </div>
    
                {{-- STEP 4 --}}
                <div
                    id="step-4"
                    x-show="step==4"
                    x-transition>
                
                    <div class="mb-5">
                
                        <h2 class="text-base font-semibold text-slate-800">
                            Data Wali
                        </h2>
                
                        <p class="text-xs text-slate-500">
                            Isi data wali apabila calon peserta didik tinggal bersama wali atau orang tua tidak dapat menjadi penanggung jawab.
                        </p>
                
                    </div>
                
                    <div class="rounded-2xl border border-slate-200 p-4">
                
                        <div class="grid md:grid-cols-2 gap-4">
                
                            {{-- NIK --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    NIK Wali
                                </label>
                
                                <input
                                    type="text"
                                    name="nik_wali"
                                    value="{{ old('nik_wali',$ppdb->nik_wali) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                            </div>
                
                            {{-- Nama --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Nama Wali
                                </label>
                
                                <input
                                    type="text"
                                    name="nama_wali"
                                    value="{{ old('nama_wali',$ppdb->nama_wali) }}"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                            </div>
                
                            {{-- Hubungan --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Hubungan Dengan Siswa
                                </label>
                
                                <input
                                    type="text"
                                    name="hubungan_wali"
                                    value="{{ old('hubungan_wali',$ppdb->hubungan_wali) }}"
                                    placeholder="Contoh : Paman, Bibi, Kakek"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                            </div>
                
                            {{-- Status --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Status Wali
                                </label>
                
                                <select
                                    name="status_wali"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                                    <option value="">Pilih Status</option>
                
                                    <option value="Hidup"
                                        @selected(old('status_wali',$ppdb->status_wali)=='Hidup')>
                                        Hidup
                                    </option>
                
                                    <option value="Meninggal"
                                        @selected(old('status_wali',$ppdb->status_wali)=='Meninggal')>
                                        Meninggal
                                    </option>
                
                                </select>
                
                            </div>
                
                            {{-- Pekerjaan --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pekerjaan
                                </label>
                
                                <select
                                    name="pekerjaan_wali"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pekerjaan</option>
                                
                                    @foreach($pekerjaanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pekerjaan_wali',$ppdb->pekerjaan_wali)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                </select>
                
                            </div>
                
                            {{-- Pendidikan --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Pendidikan Terakhir
                                </label>
                
                                <select
                                    name="pendidikan_wali"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                                
                                    <option value="">Pilih Pendidikan</option>
                                
                                    @foreach($pendidikanOptions as $item)
                                
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('pendidikan_wali',$ppdb->pendidikan_wali)==$item)>
                                
                                            {{ $item }}
                                
                                        </option>
                                
                                    @endforeach
                                
                                </select>
                
                            </div>
                
                            {{-- Penghasilan --}}
                            <div>
                            
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    Penghasilan
                                </label>
                            
                                <select
                                    name="penghasilan_wali"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                            
                                    <option value="">Pilih Penghasilan</option>
                            
                                    @foreach($penghasilanOptions as $item)
                            
                                        <option
                                            value="{{ $item }}"
                                            @selected(old('penghasilan_wali', $ppdb->penghasilan_wali) == $item)>
                            
                                            {{ $item }}
                            
                                        </option>
                            
                                    @endforeach
                            
                                </select>
                            
                            </div>
                
                            {{-- WA --}}
                            <div>
                
                                <label class="block mb-1 text-xs font-medium text-slate-600">
                                    WhatsApp Wali
                                </label>
                
                                <input
                                    type="text"
                                    name="wa_wali"
                                    value="{{ old('wa_wali',$ppdb->wa_wali) }}"
                                    placeholder="08xxxxxxxxxx"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm
                                           focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20">
                
                            </div>
                
                        </div>
                
                    </div>
                
                    {{-- Informasi --}}
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                
                        <div class="flex gap-3">
                
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                
                            </svg>
                
                            <div>
                
                                <h4 class="text-sm font-semibold text-amber-700">
                                    Informasi
                                </h4>
                
                                <p class="mt-1 text-xs leading-5 text-amber-700">
                                    Apabila calon peserta didik tinggal bersama orang tua,
                                    maka data wali boleh dikosongkan.
                                </p>
                
                            </div>
                
                        </div>
                
                    </div>
                
                </div>
    
            </div>
    
            {{-- FOOTER --}}
            <div
                class="border-t border-slate-100
                       bg-slate-50
                       px-5 py-4
                       flex items-center justify-between">
    
                <button
                    type="button"
                    @click="previous()"
                    x-show="step>1"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
    
                    ← Kembali
    
                </button>
    
                <div x-show="step==1"></div>
    
                <button
                    x-show="step<4"
                    type="button"
                    @click="next()"
                    class="px-5 py-2.5 rounded-xl bg-[#00A39D] text-sm font-medium text-white hover:bg-[#00857f] transition">
    
                    Lanjut →
    
                </button>
    
                <button
                    x-show="step==4"
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-[#00A39D] text-sm font-medium text-white hover:bg-[#00857f] transition">
    
                    Simpan Formulir
    
                </button>
    
            </div>
    
        </form>
    
    </div>

</div>

<script>
    
function wizardForm(){
    return{
        step:1,
        titles:[
            'Pribadi',
            'Alamat',
            'Orang Tua',
            'Wali'
        ],

        get progress(){
            return Math.round((this.step / 4) * 100);
        },

        validateStep(){
            const container = document.getElementById('step-' + this.step);
            if(!container){
                return true;
            }

            // semua field wajib
            const fields = container.querySelectorAll('[required]');
            for(const field of fields){

                // RADIO
                if(field.type === 'radio'){
                    const checked = container.querySelector(
                        'input[name="'+field.name+'"]:checked'
                    );

                    if(!checked){
                        field.focus();
                        alert('Silakan lengkapi semua data yang wajib diisi.');
                        return false;
                    }
                    continue;
                }

                // FILE
                if(field.type === 'file'){
                    if(field.files.length === 0){
                        field.focus();
                        alert('Silakan upload foto terlebih dahulu.');
                        return false;
                    }
                    continue;
                }

                // FIELD BIASA
                if(!field.checkValidity()){
                    field.reportValidity();
                    field.focus();
                    field.scrollIntoView({
                        behavior:'smooth',
                        block:'center'
                    });
                    return false;
                }
            }
            return true;
        },

        next(){
            if(!this.validateStep()){
                return;
            }
            if(this.step < 4){
                this.step++;
                window.scrollTo({
                    top:0,
                    behavior:'smooth'
                });
            }
        },

        previous(){
            if(this.step > 1){
                this.step--;
                window.scrollTo({
                    top:0,
                    behavior:'smooth'

                });
            }
        }
    }
}

</script>

@endsection