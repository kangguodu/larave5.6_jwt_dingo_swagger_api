<?php
namespace App\Api\V1\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;
use App\Collection;
use App\Api\V1\Services\BaseService;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $object){
        $object->avatar = empty($object->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($object->avatar);
        $result = array(
            'id' => $object->id,
            'phone' => $object->phone,
            'zone' => $object->zone,
            'avatar' =>  $object->avatar,
            'status' => intval($object->status),
            'gender' => $object->gender,
            'email'=> $object->email,
            'username'=> $object->username,
            'nickname' => $object->nickname,
        );
        if(isset($object->token)){
            $result['token'] = $object->token;
        }


        return $result;
    }

}