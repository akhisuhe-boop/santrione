<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Pegawai;
use App\Models\Ppdb;
use App\Models\Siswa;
use Illuminate\Support\Collection;

class AnnouncementService
{
    public function __construct(
        protected NotificationService $notification
    ) {}

    /**
     * Broadcast pengumuman
     */
    public function broadcast(Announcement $announcement): void
    {
        // Tidak perlu broadcast
        if (! $announcement->send_whatsapp) {
            return;
        }

        $message = $this->buildMessage($announcement);

        $recipients = $this->resolveRecipients($announcement);

        foreach ($recipients as $recipient) {

            NotificationService::sendAnnouncement(
                $recipient,
                $message
            );

        }
    }

    /**
     * Tentukan siapa penerimanya
     */
    protected function resolveRecipients(
        Announcement $announcement
    ): Collection {

        return match ($announcement->target_type) {

            'all' => collect()
                ->merge(Pegawai::where('is_active', true)->get())
                ->merge(Siswa::all())
                ->merge(Ppdb::where('status', '!=', 'ditolak')->get()),

            'role' => $this->resolveRole($announcement),

            'kelas' => Siswa::where(
                'kelas_id',
                $announcement->kelas_id
            )->get(),

            default => collect(),

        };
    }

    /**
     * Berdasarkan portal
     */
    protected function resolveRole(
        Announcement $announcement
    ): Collection {

        return match ($announcement->target_role) {

            'guru'
                => Pegawai::where('is_active', true)->get(),

            'wali'
                => Siswa::all(),

            'ppdb'
                => Ppdb::where('status', '!=', 'ditolak')->get(),

            default
                => collect(),

        };
    }

    /**
     * Format pesan
     */
    protected function buildMessage(
        Announcement $announcement
    ): string {

        $message = "📢 *PENGUMUMAN SEKOLAH*\n\n";

        $message .= "*{$announcement->title}*\n\n";

        $message .= trim(strip_tags($announcement->content));

        if ($announcement->attachment) {

            $message .= "\n\n";

            $message .= "📎 Lampiran\n";

            $message .= asset('storage/'.$announcement->attachment);

        }

        return $message;
    }
}