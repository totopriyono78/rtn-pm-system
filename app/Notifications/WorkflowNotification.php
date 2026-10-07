<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi generik untuk seluruh workflow approval & transaksi finansial
 * (SRS 4.21, keputusan scope eksplisit user 2026-10-06: channel In-app +
 * Email, TANPA WhatsApp -- menghindari dependensi API pihak ketiga & biaya
 * tambahan). Satu class dipakai ulang untuk semua konteks (bukan satu
 * class per modul) supaya konsisten dengan pola "reuse over duplication"
 * di seluruh app -- isi pesan ditentukan lewat parameter saat dibuat,
 * lihat App\Support\Notifier untuk titik-titik pemanggilan.
 *
 * TIDAK mengimplementasikan ShouldQueue -- dikirim sinkron (saat request
 * berjalan) karena belum ada queue worker (`php artisan queue:work`) yang
 * dipastikan berjalan terus-menerus di server produksi (QUEUE_CONNECTION
 * sudah diarahkan ke 'database' di .env, tapi worker-nya sendiri belum
 * disiapkan). Kalau ke depannya volume notifikasi makin besar dan queue
 * worker sudah disiapkan (mis. lewat Supervisor/Task Scheduler Windows),
 * tinggal tambahkan `implements ShouldQueue` di sini tanpa mengubah
 * pemanggil manapun.
 */
class WorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $level = 'info',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);

        if ($this->url) {
            $mail->action('Lihat Detail', $this->url);
        }

        return $mail->line('Email ini dikirim otomatis oleh '.config('app.name').'.');
    }
}
