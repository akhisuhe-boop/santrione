<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Pegawai;

class GuruPengumumanController extends Controller
{
    public function index()
    {
        $guru = Pegawai::find(session('guru_id'));

        if (! $guru) {
            return redirect()->route('guru.login');
        }

        $announcements = Announcement::visibleFor('guru')->get();

        return view('guru.pengumuman', compact(
            'guru',
            'announcements'
        ));
    }
}