<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiHealthAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $service;
    protected $status;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($service, $status, $message)
    {
        $this->service = $service;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    protected function translateService($service)
    {
        $map = [
            'twilio' => 'تويليو',
            'openai' => 'أوبن إيه آي',
            'elevenlabs' => 'إيليفن لابس',
        ];
        return $map[strtolower($service)] ?? $service;
    }

    protected function translateStatus($status)
    {
        $map = [
            'healthy' => 'سليم',
            'degraded' => 'ضعيف',
            'unhealthy' => 'معطل',
        ];
        return $map[strtolower($status)] ?? $status;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $translatedService = $this->translateService($this->service);
        $translatedStatus = $this->translateStatus($this->status);

        return (new MailMessage)
                    ->error()
                    ->subject("تنبيه النظام: {$translatedService} في حالة {$translatedStatus}")
                    ->line("اكتشف النظام مشكلة في واجهة برمجة تطبيقات {$translatedService}.")
                    ->line("التفاصيل: {$this->message}")
                    ->action('تحقق من لوحة التحكم', url('/dashboard'))
                    ->line('يرجى اتخاذ إجراء فوري لتجنب انقطاع الخدمة.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'service' => $this->service,
            'status' => $this->status,
            'message' => $this->message,
        ];
    }
}
