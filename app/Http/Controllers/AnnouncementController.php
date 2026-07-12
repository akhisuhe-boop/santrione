<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();

    $announcements = Announcement::visibleFor(
        'wali',
        $user->siswa?->kelas_id
    )->get();
    
    return view('wali.pengumuman', compact('announcements'));
    }
}