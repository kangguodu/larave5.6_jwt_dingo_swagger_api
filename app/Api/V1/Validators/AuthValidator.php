<?php
namespace App\Api\V1\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class AuthValidator extends LaravelValidator
{
    protected $rules = [
        'login' => [
            'phone' => 'required',
            'password' => 'required'
        ],
       
        'sign_up' => [
            'phone' => 'required|unique:member,phone',
            'password' => 'required|min:6'
        ],

    ];

    protected $attributes = [
        'phone' => '手機',
        'code' => '驗證碼',
        'secure_password' => '安全碼',
        'password' => '密碼',
        'gender' => '性別',
        'nickname' => '暱稱'
    ];

}