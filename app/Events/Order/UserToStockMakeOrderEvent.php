<?php

namespace App\Events\Order;

use Illuminate\Support\Facades\Log;

class UserToStockMakeOrderEvent
{
    public array $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */


    public function __construct(array $data)
    {
        Log::info('Event Called');
        $this->data = $data;
    }
}
