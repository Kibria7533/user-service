<?php


namespace App\Services;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * Class YouthAddressService
 * @package App\Services\YouthManagementServices
 */
class UserService
{

    /**
     * @return array
     */
    public function getUser(): array
    {


        /** @var Builder $userBuilder */
        $user = User::select([
            'users.id',
            'users.user_code',
            'users.user_name'

        ]);

        $response['data'] = $user->get();
        return $response;
    }



    /**
     * @param array $data
     * @return User
     * @throws Throwable
     */
    public function store(array $data): User
    {
        /** @var User $address */
        $address = app(User::class);
        $address->fill($data);
        throw_if(!$address->save(), 'RuntimeException', 'Youth Address has not been Saved to db.', 500);
        return $address;
    }



    /**
     * @param User $user
     * @return bool
     * @throws Throwable
     */
    public function destroy(User $user): bool
    {
        throw_if(!$user->delete(), 'RuntimeException', 'User has not been deleted.', 500);
        return true;
    }


}
