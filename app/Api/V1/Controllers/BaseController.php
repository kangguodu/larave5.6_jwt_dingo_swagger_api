<?php
namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Cache;

class BaseController extends Controller {

	use Helpers;

	public $status_success                           = 200;
	public $status_unkown                            = 100000; //程序内部错误：未知错误
    public $status_mysql_disconnect                = 100001; //程序内部错误: mysql 连接失败
    public $status_redis_disconnect                = 100002; //程序内部错误: redis 连接失败
    public $status_mongo_disconnect                = 100003; //程序内部错误: mongodb 连接失败

    public $status_captcha_error                    = 200000;  //验证码错误
    public $status_mobile_verification_error      = 200001; //验证码和手机号码不匹配
    public $status_signup_error                     = 200002; //用户注册失败
    public $status_login_error                      = 200003; //用户登录失败
    public $status_complete_shop_error             = 200004; //店铺完善失败
    public $status_store_image_upload_fail        = 200005; //店铺logo上传失败
    public $status_bad_request                      = 200006; //手机号码和token中的手机号码不一致，保存失败
    public $status_shop_not_found                  = 300001; //店铺不存在
    public $status_jwt_invalidate                  = 400001; // jwt token 超时，已失效
    public $status_validator_error                 = 422001; //数据验证失败
    public $status_not_found_error                 = 404001; //数据未找到
    public $status_save_fail_error                 = 500001; //数据保存失败
    public $status_remove_fail_error               = 500002; //数据删除失败

    public $in_test_api = false;
    public $size = 10000; //默认距离

    //分頁屬性
    public $current_page=1;//當前頁
    public $per_page=10;//每頁顯示條數
    public $zone = '886';
	protected $response_data = [
	    'success'=> true,
		'error_code' => 0,
		'data'   => [],
		'error_msg' => ''
	];

	protected function success($data)
	{
		return $this->response->array(array(
			'error_code' => 0,
			'data' => $data,
			'error_msg' => ''
		));
	}

	public function responseSuccess($data = array()){
		return response()->json($data);
	}

	public function notFoundResponse($object){
        return $this->responseError(
            sprintf(trans("messages.can_not_found"),$object),
            $this->status_not_found_error,
            400);
    }

    public function saveFailResponse($object){
        return $this->responseError(
            sprintf(trans("messages.object_save_fail"),$object),
            $this->status_save_fail_error,
            400);
    }

    public function removeFailResponse($object){
        return $this->responseError(
            sprintf(trans("messages.object_save_fail"),$object),
            $this->status_remove_fail_error,
            400);
    }

	public function responseError($message,$code = 1,$status_code = 400){
        $this->response_data['success'] = false;
		$this->response_data['error_msg'] = $message;
		$this->response_data['error_code'] = $code;
		if($code == 422){
			$status_code = 422;
		}
		return response()->json($this->response_data,$status_code);
	}

	private function getCacheOptionValue($obj,$default){
		if($obj->count()){
			$res = $obj[0];
			return $res;
		}else{
			return $default;
		}
	}

	public function getOption($cache_key,$default_value){

        $result = Options::where('option_name',$cache_key)->pluck('option_value');
        return $this->getCacheOptionValue($result,$default_value);
	}

	public function validateJwtToken($token){
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return $this->responseError('token 已超时',$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError('token 刷新失败',$this->status_jwt_invalidate,401);
        }
        return true;
    }

    public function getAuthUser(){
	    return  $this->auth->user();
    }

    public function getAuthUserId(){
        $user = $this->auth->user();
        return $user->id;
    }
    public function getAuthUserStatus($id){
        $user = (new \App\User())->where('id',$id)->first(['status']);
        if($user->status != 1){
            return false;
        }
        return true;
    }
    public function noneLoginResponse(){
        return $this->responseError("目前您的帳號已經被凍結，請與客服info@memecoins.com.tw聯繫",$this->status_bad_request,401);
    }

    public function getUserinfo($code){
        if(!$code){
            return null;
        }
        $data = (new \App\User())->where('invite_code',$code)->first();
        return $data;
    }

}
