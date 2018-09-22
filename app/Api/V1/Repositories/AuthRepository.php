<?php

namespace App\Api\V1\Repositories;


use App\Api\V1\Services\BaseService;
use App\User;
use App\Verification;
use App\Credits;
use App\Api\Merchant\Services\NotificationService;

use Config;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class AuthRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

  
    public function signUp($request,$zone){
        $userData = array(
            'phone' => trim($request['phone']),
            'user_type'=>1,
            'zone' => $zone,
        );

        if(isset($request['zone']) && trim($request['zone'])){
            $userData['zone'] = trim($request['zone']);
        }
        User::unguard();
        $user = User::create($userData);
        User::reguard();
        if(!$user->id) {
            return ['success'=>false];
        }else{
            $this->initCredit($user->id);
            return ['success'=>true,'id'=>$user->id];
        }
    }

    public function is_singup($request){

        $where = array(
            'phone' => trim($request['phone']),
        );

        $user = User::where($where)->first();
        if($user){
            return true;
        }
        return false;
    }

    public function login($credentials){
        $where = array(
            'phone'  => trim($credentials['phone']),
        );
        return User::where($where)->first();
    }

    public function fillInfo($credentials){
        $data = [
            'phone' => trim($credentials['phone']),
            'username'  => (isset($credentials['username']) && trim($credentials['username'])) ? trim($credentials['username']) : trim($credentials['phone']),
            'nickname'  => (isset($credentials['nickname']) && trim($credentials['nickname'])) ? trim($credentials['nickname']) : trim($credentials['phone']),
            'password' => $credentials['password'],
        ];

        User::unguard();
        $user = User::create($data);
        User::reguard();

        return $user;
    }


    public function getById($id){
        return $this->user->where('id', $id)->first();
       
    }

}