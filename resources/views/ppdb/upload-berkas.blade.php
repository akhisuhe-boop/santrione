@extends('ppdb.layout.ppdb')
@section('title', 'Upload Berkas')
@section('content')

<div class="max-w-5xl mx-auto px-4 py-6">
    
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <div class="flex items-center gap-4">

        <div
            class="w-12 h-12 rounded-xl
                   bg-gradient-to-br
                   from-[#00A39D]/15
                   to-cyan-100
                   flex items-center justify-center shrink-0">

            <x-heroicon-o-document-arrow-up
                class="w-6 h-6 text-[#00A39D]" />

        </div>

        <div>

            <h1 class="text-base font-bold text-slate-800">
                Upload Berkas
            </h1>

            <p class="text-xs text-slate-500">
                Upload KK, Akta Kelahiran dan Ijazah.
            </p>

        </div>

    </div>
</div>

<div class="mt-4 bg-white rounded-2xl border border-slate-100 p-5">

    @php
        $total = 3;

        $uploaded = collect([
            $ppdb->scan_kk,
            $ppdb->scan_akta,
            $ppdb->scan_ijazah,
        ])->filter()->count();

        $percent = ($uploaded / $total) * 100;
    @endphp

    <div class="flex items-center justify-between">

        <div>

            <h2 class="text-sm font-semibold text-slate-800">
                Progress Upload
            </h2>

            <p class="text-xs text-slate-500 mt-0.5">
                {{ $uploaded }} dari {{ $total }} dokumen berhasil diupload
            </p>

        </div>

        <div
            class="px-3 py-1 rounded-full
                   bg-[#00A39D]/10
                   text-[#00A39D]
                   text-xs font-semibold">

            {{ $uploaded }}/{{ $total }}

        </div>

    </div>

    <div class="mt-4 w-full h-2 bg-slate-100 rounded-full overflow-hidden">

        <div
            class="h-full bg-[#00A39D] rounded-full transition-all duration-500"
            style="width: {{ $percent }}%">

        </div>

    </div>

</div>

<form
    action="{{ route('ppdb.upload-berkas.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="mt-6">

    @csrf

    {{-- ================================
     KARTU KELUARGA
    ================================ --}}
    <div
        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5"
        x-data="{
            preview: null,
            filename: '',
            filesize: '',
    
            pilihFile(event){
    
                const file = event.target.files[0];
    
                if(!file) return;
    
                this.filename = file.name;
                this.filesize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    
                if(file.type.startsWith('image/')){
                    this.preview = URL.createObjectURL(file);
                }else{
                    this.preview = null;
                }
            }
        }"
    >
    
        {{-- Header --}}
        <div class="flex items-start justify-between">
    
            <div class="flex items-center gap-3">
    
                <div class="w-12 h-12 rounded-xl bg-[#00A39D]/10 flex items-center justify-center">
    
                    <x-heroicon-o-identification
                        class="w-6 h-6 text-[#00A39D]" />
    
                </div>
    
                <div>
    
                    <h3 class="font-semibold text-slate-800">
                        Kartu Keluarga
                    </h3>
    
                    <p class="text-xs text-slate-500">
                        Upload scan atau foto Kartu Keluarga.
                    </p>
    
                </div>
    
            </div>
    
            <span
                class="px-3 py-1 rounded-full
                       bg-red-50
                       text-red-600
                       text-xs font-semibold">
    
                Wajib
    
            </span>
    
        </div>
    
        {{-- Upload Area --}}
        <div class="mt-5">
    
            <label
                for="scan_kk"
                class="block cursor-pointer">
    
                <div
                    class="border-2 border-dashed border-slate-200 rounded-2xl p-8
                           hover:border-[#00A39D]
                           hover:bg-[#00A39D]/5
                           transition">
    
                    {{-- ===========================
                         BELUM PILIH FILE
                    ============================ --}}
                    <div
                        x-show="filename==''"
                        class="text-center">
    
                        <x-heroicon-o-cloud-arrow-up
                            class="w-10 h-10 text-[#00A39D] mx-auto"/>
    
                        <p class="mt-3 font-medium text-slate-700">
                            Klik untuk memilih file
                        </p>
    
                        <p class="text-xs text-slate-500 mt-1">
                            JPG • PNG • PDF • Maksimal 2 MB
                        </p>
    
                    </div>
    
                    {{-- ===========================
                         PREVIEW GAMBAR
                    ============================ --}}
                    <div
                        x-show="preview"
                        class="text-center">
    
                        <img
                            :src="preview"
                            class="w-40 h-40 object-cover rounded-xl border mx-auto">
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                    {{-- ===========================
                         PREVIEW PDF
                    ============================ --}}
                    <div
                        x-show="filename!='' && !preview"
                        class="text-center">
    
                        <div
                            class="w-20 h-20 rounded-2xl
                                   bg-red-50
                                   flex items-center justify-center
                                   mx-auto">
    
                            <x-heroicon-o-document-text
                                class="w-10 h-10 text-red-500"/>
    
                        </div>
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                </div>
    
                <input
                    id="scan_kk"
                    type="file"
                    name="scan_kk"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="hidden"
                    @change="pilihFile">
    
            </label>
    
            @error('scan_kk')
    
                <p class="text-red-500 text-xs mt-2">
                    {{ $message }}
                </p>
    
            @enderror
    
        </div>
    
        {{-- FILE SUDAH TERSIMPAN --}}
        @if($ppdb->scan_kk)
    
            <div
                class="mt-5 rounded-xl
                       bg-emerald-50
                       border border-emerald-200
                       p-4 flex items-center justify-between">
    
                <div>
    
                    <p class="text-sm font-semibold text-emerald-700">
                        ✓ Berkas Kartu Keluarga sudah tersimpan
                    </p>
    
                    <p class="text-xs text-emerald-600 mt-1">
                        {{ basename($ppdb->scan_kk) }}
                    </p>
    
                </div>
    
                <a
                    href="{{ Storage::url($ppdb->scan_kk) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2
                           px-4 py-2 rounded-xl
                           bg-white border border-emerald-200
                           hover:bg-emerald-100
                           text-sm font-medium">
    
                    <x-heroicon-o-eye class="w-4 h-4"/>
    
                    Lihat
    
                </a>
    
            </div>
    
        @endif
    
    </div>

    {{-- ================================
     AKTA KELAHIRAN
    ================================ --}}
    <div
        class="mt-5 bg-white rounded-2xl border border-slate-100 shadow-sm p-5"
        x-data="{
            preview: null,
            filename: '',
            filesize: '',
    
            pilihFile(event){
    
                const file = event.target.files[0];
    
                if(!file) return;
    
                this.filename = file.name;
                this.filesize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    
                if(file.type.startsWith('image/')){
                    this.preview = URL.createObjectURL(file);
                }else{
                    this.preview = null;
                }
            }
        }"
    >
    
        {{-- Header --}}
        <div class="flex items-start justify-between">
    
            <div class="flex items-center gap-3">
    
                <div class="w-12 h-12 rounded-xl bg-[#00A39D]/10 flex items-center justify-center">
    
                    <x-heroicon-o-document-text
                        class="w-6 h-6 text-[#00A39D]" />
    
                </div>
    
                <div>
    
                    <h3 class="font-semibold text-slate-800">
                        Akta Kelahiran
                    </h3>
    
                    <p class="text-xs text-slate-500">
                        Upload scan atau foto Akta Kelahiran.
                    </p>
    
                </div>
    
            </div>
    
            <span
                class="px-3 py-1 rounded-full
                       bg-red-50
                       text-red-600
                       text-xs font-semibold">
    
                Wajib
    
            </span>
    
        </div>
    
        {{-- Upload Area --}}
        <div class="mt-5">
    
            <label
                for="scan_akta"
                class="block cursor-pointer">
    
                <div
                    class="border-2 border-dashed border-slate-200 rounded-2xl p-8
                           hover:border-[#00A39D]
                           hover:bg-[#00A39D]/5
                           transition">
    
                    {{-- BELUM PILIH FILE --}}
                    <div
                        x-show="filename==''"
                        class="text-center">
    
                        <x-heroicon-o-cloud-arrow-up
                            class="w-10 h-10 text-[#00A39D] mx-auto"/>
    
                        <p class="mt-3 font-medium text-slate-700">
                            Klik untuk memilih file
                        </p>
    
                        <p class="text-xs text-slate-500 mt-1">
                            JPG • PNG • PDF • Maksimal 2 MB
                        </p>
    
                    </div>
    
                    {{-- PREVIEW GAMBAR --}}
                    <div
                        x-show="preview"
                        class="text-center">
    
                        <img
                            :src="preview"
                            class="w-40 h-40 object-cover rounded-xl border mx-auto">
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                    {{-- PREVIEW PDF --}}
                    <div
                        x-show="filename!='' && !preview"
                        class="text-center">
    
                        <div
                            class="w-20 h-20 rounded-2xl
                                   bg-red-50
                                   flex items-center justify-center
                                   mx-auto">
    
                            <x-heroicon-o-document-text
                                class="w-10 h-10 text-red-500"/>
    
                        </div>
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                </div>
    
                <input
                    id="scan_akta"
                    type="file"
                    name="scan_akta"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="hidden"
                    @change="pilihFile">
    
            </label>
    
            @error('scan_akta')
                <p class="text-red-500 text-xs mt-2">
                    {{ $message }}
                </p>
            @enderror
    
        </div>
    
        {{-- FILE SUDAH TERSIMPAN --}}
        @if($ppdb->scan_akta)
    
            <div
                class="mt-5 rounded-xl
                       bg-emerald-50
                       border border-emerald-200
                       p-4 flex items-center justify-between">
    
                <div>
    
                    <p class="text-sm font-semibold text-emerald-700">
                        ✓ Berkas Akta Kelahiran sudah tersimpan
                    </p>
    
                    <p class="text-xs text-emerald-600 mt-1">
                        {{ basename($ppdb->scan_akta) }}
                    </p>
    
                </div>
    
                <a
                    href="{{ Storage::url($ppdb->scan_akta) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2
                           px-4 py-2 rounded-xl
                           bg-white border border-emerald-200
                           hover:bg-emerald-100
                           text-sm font-medium">
    
                    <x-heroicon-o-eye class="w-4 h-4"/>
    
                    Lihat
    
                </a>
    
            </div>
    
        @endif
    
    </div>

    {{-- ================================
     IJAZAH / SKL
    ================================ --}}
    <div
        class="mt-5 bg-white rounded-2xl border border-slate-100 shadow-sm p-5"
        x-data="{
            preview: null,
            filename: '',
            filesize: '',
    
            pilihFile(event){
    
                const file = event.target.files[0];
    
                if(!file) return;
    
                this.filename = file.name;
                this.filesize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    
                if(file.type.startsWith('image/')){
                    this.preview = URL.createObjectURL(file);
                }else{
                    this.preview = null;
                }
            }
        }"
    >
    
        {{-- Header --}}
        <div class="flex items-start justify-between">
    
            <div class="flex items-center gap-3">
    
                <div class="w-12 h-12 rounded-xl bg-[#00A39D]/10 flex items-center justify-center">
    
                    <x-heroicon-o-academic-cap
                        class="w-6 h-6 text-[#00A39D]" />
    
                </div>
    
                <div>
    
                    <h3 class="font-semibold text-slate-800">
                        Ijazah / SKL
                    </h3>
    
                    <p class="text-xs text-slate-500">
                        Upload Ijazah atau Surat Keterangan Lulus (SKL). Dokumen ini bersifat opsional.
                    </p>
    
                </div>
    
            </div>
    
            <span
                class="px-3 py-1 rounded-full
                       bg-slate-100
                       text-slate-600
                       text-xs font-semibold">
    
                Opsional
    
            </span>
    
        </div>
    
        {{-- Upload Area --}}
        <div class="mt-5">
    
            <label
                for="scan_ijazah"
                class="block cursor-pointer">
    
                <div
                    class="border-2 border-dashed border-slate-200 rounded-2xl p-8
                           hover:border-[#00A39D]
                           hover:bg-[#00A39D]/5
                           transition">
    
                    {{-- BELUM PILIH FILE --}}
                    <div
                        x-show="filename==''"
                        class="text-center">
    
                        <x-heroicon-o-cloud-arrow-up
                            class="w-10 h-10 text-[#00A39D] mx-auto"/>
    
                        <p class="mt-3 font-medium text-slate-700">
                            Klik untuk memilih file
                        </p>
    
                        <p class="text-xs text-slate-500 mt-1">
                            JPG • PNG • PDF • Maksimal 2 MB
                        </p>
    
                    </div>
    
                    {{-- PREVIEW GAMBAR --}}
                    <div
                        x-show="preview"
                        class="text-center">
    
                        <img
                            :src="preview"
                            class="w-40 h-40 object-cover rounded-xl border mx-auto">
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                    {{-- PREVIEW PDF --}}
                    <div
                        x-show="filename!='' && !preview"
                        class="text-center">
    
                        <div
                            class="w-20 h-20 rounded-2xl
                                   bg-red-50
                                   flex items-center justify-center
                                   mx-auto">
    
                            <x-heroicon-o-document-text
                                class="w-10 h-10 text-red-500"/>
    
                        </div>
    
                        <p
                            class="mt-4 font-semibold text-slate-700"
                            x-text="filename">
                        </p>
    
                        <p
                            class="text-xs text-slate-500 mt-1"
                            x-text="filesize">
                        </p>
    
                        <div
                            class="mt-3 inline-flex items-center gap-2
                                   px-3 py-1.5 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
    
                            File siap diupload
    
                        </div>
    
                        <p class="mt-3 text-xs text-[#00A39D]">
                            Klik kembali untuk mengganti file
                        </p>
    
                    </div>
    
                </div>
    
                <input
                    id="scan_ijazah"
                    type="file"
                    name="scan_ijazah"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="hidden"
                    @change="pilihFile">
    
            </label>
    
            @error('scan_ijazah')
                <p class="text-red-500 text-xs mt-2">
                    {{ $message }}
                </p>
            @enderror
    
        </div>
    
        {{-- FILE SUDAH TERSIMPAN --}}
        @if($ppdb->scan_ijazah)
    
            <div
                class="mt-5 rounded-xl
                       bg-emerald-50
                       border border-emerald-200
                       p-4 flex items-center justify-between">
    
                <div>
    
                    <p class="text-sm font-semibold text-emerald-700">
                        ✓ Berkas Ijazah / SKL sudah tersimpan
                    </p>
    
                    <p class="text-xs text-emerald-600 mt-1">
                        {{ basename($ppdb->scan_ijazah) }}
                    </p>
    
                </div>
    
                <a
                    href="{{ Storage::url($ppdb->scan_ijazah) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2
                           px-4 py-2 rounded-xl
                           bg-white border border-emerald-200
                           hover:bg-emerald-100
                           text-sm font-medium">
    
                    <x-heroicon-o-eye class="w-4 h-4"/>
    
                    Lihat
    
                </a>
    
            </div>
    
        @endif
    
    </div>

    <div
    class="mt-6
           rounded-2xl
           border border-amber-200
           bg-amber-50/70
           shadow-sm
           p-5">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">

        <div class="flex items-start gap-3">

            <div
                class="w-10 h-10 rounded-xl
                       bg-amber-100
                       flex items-center justify-center
                       shrink-0">

                <x-heroicon-o-exclamation-triangle
                    class="w-5 h-5 text-amber-600" />

            </div>

            <div>

                <h4 class="text-sm font-semibold text-amber-900">
                    Pastikan berkas sudah benar
                </h4>

                <p class="text-xs text-amber-800 mt-1 leading-5">
                    Kartu Keluarga dan Akta Kelahiran <strong>wajib diupload</strong>.
                    Setelah berkas dikirim, data akan masuk ke proses
                    verifikasi panitia PPDB dan tidak dapat diubah tanpa persetujuan panitia.
                </p>

            </div>

        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2
                   px-6 py-3
                   rounded-xl
                   bg-[#00A39D]
                   hover:bg-[#00857F]
                   text-white
                   font-semibold
                   transition">

            <x-heroicon-o-paper-airplane class="w-5 h-5"/>

            Kirim Berkas

        </button>

    </div>

</div>

</form>

</div>
@endsection