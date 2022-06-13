<?php

namespace App\Listeners\Order;


use App\Events\Order\UserToOrderMakeOrderRollbackEvent;
use App\Events\Order\UserToStockMakeOrderEvent;
use App\Models\BaseModel;
use App\Models\User;
use App\Services\RabbitMQService;
use App\Services\UserService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderToUserMakeOrderListener implements ShouldQueue
{
    public UserService $youthService;
    public RabbitMQService $rabbitMQService;

    /**
     * @param UserService $youthService
     * @param RabbitMQService $rabbitMQService
     */
    public function __construct(UserService $youthService, RabbitMQService $rabbitMQService)
    {
        $this->youthService = $youthService;
        $this->rabbitMQService = $rabbitMQService;
    }

    /**
     * @param $event
     * @return void
     * @throws Exception|Throwable
     */
    public function handle($event)
    {
        $eventData = json_decode(json_encode($event), true);
        $data = $eventData['data'] ?? [];
        Log::info('me', $data);
        try {
            $this->rabbitMQService->receiveEventSuccessfully(
                BaseModel::SAGA_INSTITUTE_SERVICE,
                BaseModel::SAGA_YOUTH_SERVICE,
                get_class($this),
                json_encode($event)
            );

            $alreadyConsumed = $this->rabbitMQService->checkEventAlreadyConsumed();
            if (!$alreadyConsumed) {
                DB::beginTransaction();

                if (!empty($data['user_id'])) {
                    Log::info('hello');
                    $user = User::where('user_code', '=', $data['user_id'])->first();
                    Log::info('User Not found');
                    throw_if(!$user, 'RuntimeException', 'User not found');


                    DB::commit();

                    /** Trigger EVENT to MailSms Service to send Mail via RabbitMQ */
                    //$this->youthService->sendMailCourseEnrollmentSuccess($data);

                    /** Trigger EVENT to Institute Service via RabbitMQ */
                    event(new UserToStockMakeOrderEvent($data));

                    /** Store the event as a Success event into Database */
                    $this->rabbitMQService->sagaSuccessEvent(
                        BaseModel::SAGA_INSTITUTE_SERVICE,
                        BaseModel::SAGA_YOUTH_SERVICE,
                        get_class($this),
                        json_encode($data)
                    );
                } else {
                    throw new Exception("youth_id not provided!");
                }
            }
        } catch (Throwable $e) {
            Log::info('I am in exception');
            if ($e instanceof QueryException && $e->getCode() == BaseModel::DATABASE_CONNECTION_ERROR_CODE) {
                /** Technical Recoverable Error Occurred. RETRY mechanism with DLX-DLQ apply now by sending a rejection */
                throw new Exception("Database Connectivity Error");
            } else {
                /** Trigger EVENT to Institute Service via RabbitMQ to Rollback */
                $data['publisher_service'] = BaseModel::SAGA_YOUTH_SERVICE;
               event(new UserToOrderMakeOrderRollbackEvent($data));

                /** Technical Non-recoverable Error "OR" Business Rule violation Error Occurred. Compensating Transactions apply now */
                /** Store the event as an Error event into Database */
                $this->rabbitMQService->sagaErrorEvent(
                    BaseModel::SAGA_INSTITUTE_SERVICE,
                    BaseModel::SAGA_YOUTH_SERVICE,
                    get_class($this),
                    json_encode($data),
                    $e
                );
            }
        }
    }
}
