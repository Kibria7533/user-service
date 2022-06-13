<?php

namespace App\Providers;

use App\Events\Order\UserToOrderMakeOrderRollbackEvent;
use App\Events\Order\UserToStockMakeOrderEvent;
use App\Listeners\Order\UserToOrderMakeOrderRollbackListener;
use App\Listeners\Order\UserToStockMakeOrderListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserToOrderMakeOrderRollbackEvent::class => [
            UserToOrderMakeOrderRollbackListener::class
        ],
        UserToStockMakeOrderEvent::class => [
            UserToStockMakeOrderListener::class
        ]
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
