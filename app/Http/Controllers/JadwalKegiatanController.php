<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TemplateKegiatan;
use App\Models\JadwalKegiatan;

class JadwalKegiatanController extends Controller
{
    public function generate($bulan, $tahun)
    {
        $templates = TemplateKegiatan::where('aktif',1)->get();

        foreach ($templates as $template) {

            $tanggal = Carbon::create($tahun, $bulan, 1)->startOfMonth();

            while ($tanggal->month == $bulan) {

                if (strtolower($tanggal->locale('id')->dayName) == $template->hari) {

                    JadwalKegiatan::create([
                        'template_kegiatan_id' => $template->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam_mulai' => $template->jam_mulai,
                        'jam_selesai' => $template->jam_selesai,
                    ]);

                }

                $tanggal->addDay();
            }
        }

        return "Jadwal berhasil dibuat";
    }
}
