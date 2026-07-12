<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Ppdb;

class PpdbPengumumanController extends Controller
{
    public function index()
    {
        $ppdb = Ppdb::find(session('ppdb_id'));

        if (! $ppdb) {
            return redirect()->route('ppdb.login');
        }

        $announcements = Announcement::visibleFor('ppdb')->latest()->get();

        return view('ppdb.pengumuman', compact(
            'ppdb',
            'announcements'
        ));
    }
}