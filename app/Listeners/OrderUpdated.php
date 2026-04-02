<?php

namespace App\Listeners;

use App\Events\OrderUpdated as OrderUpdatedEvent;
use App\Service\OrderNotificationService;

class OrderUpdated
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderUpdatedEvent $event)
    {
        app(OrderNotificationService::class)->sendOrderStatusMail($event->order);
    }
}
