<?php
namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Prettus\Validator\Exceptions\ValidatorException;
use JWTFactory;
use App\Api\V1\Validators\AuthValidator;
use App\Api\V1\Transformers\UserTransformer;
use App\Api\V1\Repositories\AuthRepository;
use Validator;
use Carbon\Carbon;


class UserController extends BaseController
{

    protected $authRepository;
    protected $authValidator;
    protected $toolsrepository;

    use  AuthenticatesUsers;



    public function __construct(AuthRepository $authRepository,AuthValidator $authValidator){
        $this->authRepository = $authRepository;
        $this->authValidator = $authValidator;
    }

    public function login(Request $request){
        $credentials = $request->only(['phone','password']);
        try{
            $this->authValidator->with($credentials)->passesOrFail('login');
            $user = $this->authRepository->login($credentials);
            if(!$user){
                return $this->responseError('手機號不存在');
            }

            $hashed_password = $user->password;
            $password = trim($credentials['password']);

            if(!\Hash::check($password,$hashed_password)){
                return $this->responseError('賬戶名密碼錯誤');
            }

            $token = JWTAuth::fromUser($user);
            $user = $this->authRepository->getById($user->id);
            $user->token = $token;

            return $this->response()->item($user,new UserTransformer());
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    public function signUp(Request $request)
    {

        $credentials = $request->all();
        try{
            $this->authValidator->with($credentials)->passesOrFail('sign_up');

            $checked = $this->authRepository->is_singup($credentials,$this->zone);
            if($checked){
                return $this->responseError('您已經註冊，可直接登錄');
            }

            return $this->authRepository->fillInfo($credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }


    public function userInfo(Request $request){
        $user = $this->auth->user();
        $user_id = $user->id;
        if(!$this->getAuthUserStatus($user_id)){
            return $this->noneLoginResponse();
        }
        $userInfo = $this->authRepository->getById($user_id);
        if($userInfo){
            unset($userInfo->password);
            unset($userInfo->token);
        }
        return $this->response()->item($userInfo,new UserTransformer());
    }


    public function logout(Request $request){
        $token = JWTAuth::getToken();
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (TokenBlacklistedException $e){
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (TokenInvalidException $e){
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        }
        return array();
    }


}