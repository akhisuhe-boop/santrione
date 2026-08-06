<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Tagihan extends Model
{
    use BelongsToTenant;

    // Tagihan tidak punya lembaga_id langsung — scoping lewat siswa.lembaga.
    // Beberapa tagihan berasal dari PPDB (siswa_id kosong), jadi ikut cek
    // jalur ppdb.lembaga juga.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where(function (Builder $q) use ($yayasanId) {
            $q->whereHas('siswa.lembaga', fn ($sub) => $sub->where('yayasan_id', $yayasanId))
              ->orWhereHas('ppdb.lembaga', fn ($sub) => $sub->where('yayasan_id', $yayasanId));
        });
    }

    protected $fillable = [
    'siswa_id',
    'ppdb_id',
    'kode',
    'jenis_tagihan_id', // 🔥 WAJIB
    'judul',
    'nominal',
    'nominal_terbayar',
    'jatuh_tempo',
    'status',
    'keterangan',
    'rekening_id', // 🔥
    'tahun_ajaran_id', // 🔥
    'periode_tahun_ajaran_id', // 🔥
    'bulan', // 🔥
    ];

    protected $casts = [
        'jatuh_tempo' => 'date',
    ];

    // 🔗 Relasi ke siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Pastikan tagihan "Biaya Pendaftaran PPDB" sudah ada untuk PPDB ini.
     * Kalau belum ada (mis. pendaftar lama dari sebelum fitur ini dibuat,
     * atau Jenis Tagihan-nya baru diaktifkan setelah dia daftar), buat
     * sekarang juga. Dipanggil dari dashboard, halaman pembayaran, dan
     * guard kunci Formulir/Berkas -- supaya konsisten di mana pun dicek.
     */
    public static function pastikanTagihanPendaftaranPpdb(\App\Models\Ppdb $ppdb): ?self
    {
        // Kalau PPDB ini sudah resmi jadi siswa aktif, tagihannya SUDAH
        // dipindahkan ke siswa_id (lihat aksi "Aktifkan Siswa") -- jangan
        // buat lagi tagihan baru yang nyangkut ke ppdb_id ini.
        if ($ppdb->status === 'aktif') {
            return null;
        }

        $jenisTagihan = \App\Models\JenisTagihan::where('tipe_sistem', 'pendaftaran_ppdb')->first();

        if (!$jenisTagihan) {
            return null; // lembaga ini tidak pakai fitur biaya pendaftaran otomatis
        }

        $tagihan = self::where('ppdb_id', $ppdb->id)
            ->where('jenis_tagihan_id', $jenisTagihan->id)
            ->first();

        if ($tagihan) {

            if (!$tagihan->rekening_id) {
                $rekening = \App\Models\Rekening::where('lembaga_id', $ppdb->lembaga_id)
                    ->where('keperluan', 'pendaftaran_ppdb')
                    ->where('is_active', true)
                    ->first();

                if ($rekening) {
                    $tagihan->update(['rekening_id' => $rekening->id]);
                }
            }

            return $tagihan;
        }

        $rekening = \App\Models\Rekening::where('lembaga_id', $ppdb->lembaga_id)
            ->where('keperluan', 'pendaftaran_ppdb')
            ->where('is_active', true)
            ->first();

        return self::create([
            'ppdb_id'          => $ppdb->id,
            'jenis_tagihan_id' => $jenisTagihan->id,
            'judul'            => $jenisTagihan->nama,
            'nominal'          => $jenisTagihan->default_nominal,
            'nominal_terbayar' => 0,
            'status'           => 'belum',
            'jatuh_tempo'      => now()->addDays(7),
            'tahun_ajaran_id'  => $ppdb->tahun_ajaran_id,
            'rekening_id'      => $rekening?->id,
        ]);
    }

    // 🔗 Relasi ke ppdb
    public function ppdb()
    {
        return $this->belongsTo(\App\Models\Ppdb::class);
    }

    // 🔗 Relasi ke pembayaran
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // 🔗 Relasi ke rekening
    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    // 🔗 Relasi ke jenis tagihan
    public function jenisTagihan()
    {
        return $this->belongsTo(JenisTagihan::class);
    }

    //relasi ke tahun ajaran dan periode tahun ajaran
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function getNamaAttribute()
    {
        return optional($this->siswa)->nama_lengkap
            ?? optional($this->ppdb)->nama_lengkap
            ?? '-';
    }

    public function getLembagaNamaAttribute()
    {
        return optional(optional($this->siswa)->lembaga)->nama
            ?? optional(optional($this->ppdb)->lembaga)->nama
            ?? '-';
    }

    public function getNamaPembayarAttribute()
    {
        if ($this->siswa) {
            return $this->siswa->nama_lengkap;
        }

        if ($this->ppdb) {
            return $this->ppdb->nama_lengkap;
        }

        return '-';
    }

    public function getKelasNamaAttribute()
    {
        return optional(optional($this->siswa)->kelas)->nama
            ?? '-';
    }

    public function periodeTahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'periode_tahun_ajaran_id');
    }

    //Auto generate kode dan jatuh tempo tagihan
    protected static function booted()
    {
    static::creating(function ($model) {

        // 🔥 AUTO KODE ANTI DUPLIKAT
        do {
            $kode =
                'INV-' .
                now()->format('YmdHis') .
                '-' .
                rand(100, 999);

        } while (self::where('kode', $kode)->exists());
        $model->kode = $kode;

        // 🔥 AUTO JATUH TEMPO
        $jenis = \App\Models\JenisTagihan::find($model->jenis_tagihan_id);
        if ($jenis && $jenis->is_bulanan) {
            if ($model->bulan) {
                $bulan = is_array($model->bulan)
                    ? ($model->bulan[0] ?? now()->month)
                    : $model->bulan;

                $model->jatuh_tempo = \Carbon\Carbon::create(
                    now()->year,
                    $bulan,
                    10
                );
            } else {
                $model->jatuh_tempo = now()->day(10);
            }
        } else {
            if (!$model->jatuh_tempo) {
                throw new \Exception(
                    'Jatuh tempo wajib diisi untuk tagihan non bulanan'
                );
            }
        }
            });
        }
}
