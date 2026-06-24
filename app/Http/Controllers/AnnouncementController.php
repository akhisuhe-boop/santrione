<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $announcements = Announcement::query()
            ->where(function ($q) use ($user) {

                // pengumuman umum
                $q->where('target', 'all');

                // khusus wali
                if ($user->role === 'wali') {
                    $q->orWhere('target', 'wali');
                }

                // kalau wali punya siswa
                if ($user->role === 'wali' && $user->siswa) {
                    $q->orWhere('kelas_id', $user->siswa->kelas_id);
                }

                // siswa
                if ($user->role === 'siswa') {
                    $q->orWhere('target', 'siswa');
                }

                // guru
                if ($user->role === 'guru') {
                    $q->orWhere('target', 'guru');
                }
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('wali.pengumuman.index', compact('announcements'));
    }
}