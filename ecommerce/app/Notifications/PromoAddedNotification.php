<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Promo;
use App\Models\User;

class PromoAddedNotification extends Notification
{
    use Queueable;
    private $promo;
    private $user;

    /**
     * Data promo yang akan digunakan dalam notifikasi.
     *
     * @var array
     */
    protected $promoData;

    /**
     * Create a new notification instance.
     *
     * @param array $promoData
     */
    public function __construct(Promo $promoData, User $user)
    {
        $this->promoData = $promoData->toArray();
        $this->promo = $promoData; // Tetapkan objek promo ke properti $this->promo
        $this->user = $user;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // return (new MailMessage)
        //     ->line('The introduction to the notification.')
        //     ->action('Notification Action', url('/'))
        //     ->line('Thank you for using our application!')
        //     ->line('Promo Title: ' . ($this->promo ? $this->promo->title : 'N/A'))
        //     ->line('Promo Description: ' . ($this->promo ? $this->promo->description : 'N/A'));
        $user = $notifiable;

        return (new MailMessage)
            ->markdown('emails.promo_added_notification', [
                'user' => $notifiable,
                'promo' => $this->promo,
            ]);
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'promo_id' => $this->promoData['id'],
            'message' => 'You have a new promo!',
        ];
    }
}
