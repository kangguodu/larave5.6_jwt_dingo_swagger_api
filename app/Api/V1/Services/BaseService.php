<?php
namespace App\Api\V1\Services;


use http\Env\Request;
use App\Api\Merchant\Services\MemberCreditsService;
use App\Common\Services\BaseServices as Service;
use App\Order;

class BaseService
{
    public static function image($image,$type='logo'){
        if(!$image && $type=='logo'){
            return url('/upload').'/logo.png';  //店铺logo
        }
        if(!$image && $type=='banner'){
            return url('/upload').'/banner/banner.jpg';  //店铺banner
        }
        if(strstr($image,'http') || strstr($image,'https')){
            return $image;
        }else if(strstr($image,'upload')){
            return url('/').'/'.$image;
        }else{
            return url('/upload').'/'.$image;
        }
    }

    /**
     * 營業情況獲取
     */
    public static function getservicetime($store_id,$routine_holiday,$special_holiday,$special_business_day){
        $w = date('w');
        $d = date('d');
        $date = date('Y-m-d');
        $data = (new \App\OpenHourRange())->where('store_id',$store_id)
            ->where('day_of_week',$w)
            ->get();
        $result['status'] = 0;

        if(isset($data{0})){
            $result['time'] = '';
            foreach ($data as $k=>$v){
                $result['time'] .= date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                if($k != count($data)-1){
                    $result['time'] .= ',';
                }
                $result['status'] = 1;
            }
        }else{
            $result['time'] = '休息中';
            $result['status'] = 0;
        }

        if($routine_holiday == $d){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_holiday == $date){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_business_day == $date){
            if(count($data)){
                $result['time'] = '';
                foreach ($data as $v){
                    $result['time'] .= date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                    $result['status'] = 1;
                }
            }else{
                $result['time'] = '特別營業日';
                $result['status'] = 0;
            }

        }
        return $result;
    }

    //根據經緯度計算兩點距離
    public static function getDistance($lat1, $lng1, $lat2, $lng2){
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    //根據位置獲取周圍經緯度
    public static function getLatAndLngRange($Lat,$Lng,$size){

        $range = 180 / pi() * $size / 6367;  //周圍2千米
        $lngR = $range / cos($Lat * pi() / 180);


        $maxLat= $Lat + $range;//最大纬度
        $minLat= $Lat - $range;//最小纬度
        $maxLng = $Lng + $lngR;//最大经度
        $minLng = $Lng - $lngR;//最小经度

        $list = array('maxLat'=>$maxLat,'minLat'=>$minLat,'maxLng'=>$maxLng,'minLng'=>$minLng);
        return $list;
    }

    //會員推廣碼生成
    public static function inviteCode($member_id){
        $user = (new \App\User())->where('id',$member_id)->first();
        if (empty($user->invite_code)){
                \DB::beginTransaction();
                try{
                    $codeObj = (new \App\PromoCode())->where('used', 0)
                        ->orderBy(\DB::raw('RAND()'))
                        ->first();
                    $user->invite_code = $codeObj->code;
                    $user->save();
                    $codeObj->used = 1;
                    $codeObj->save();
                    \DB::commit();
                   return $user->invite_code;

                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::error("invite create fail: ".$e->getMessage().', '.$e->getLine());

                }

        }
        return $user->invite_code;
    }

    /**
     * 優惠券領取記錄
     */
    public static function coupons_receive_log($coupons_id,$member_id){
        $data = [
            'coupons_id'  => $coupons_id,
            'member_id'   => $member_id,
            'receive_at' => date('Y-m-d H:i:s'),
            'receive_date' => date('Y-m-d'),
            'type' => 1
        ];
        (new \App\CouponsReceiveLog())->insert($data);

    }

    public static function operation($member_id,$coupons_id){

        \DB::beginTransaction();
        try{
            $list = (new \App\CouponsRelease())->where('id',$coupons_id)->first();
            if($list) {
                if ($list->number <= 0) {
                    return ['success'=>false,'msg'=>'優惠券已領取完'];
                }
                if($list->start_at > date('Y-m-d')){
                    return ['success'=>false,'msg'=>'領取優惠券活動尚未開始'];
                }
                if($list->expire_at < date('Y-m-d') && $list->expire_at && $list->expire_at!='0000-00-00'){
                    return ['success'=>false,'msg'=>'優惠券領取已結束'];
                }
                $data = [
                    'coupons_id'  => $coupons_id,
                    'member_id'   => $member_id,
                    'status' => 1,
                    'receive_at'=>time(),
                    'start_at' => strtotime(date('Y-m-d')),
                    'expire_time'=> strtotime(date('Y-m-d')) +  $list->valid_time*24*3600
                ];

                (new \App\Coupons())->insertGetId($data);
                $list->number -= 1;
                $list->save();
                self:: coupons_receive_log($coupons_id,$member_id);
                \DB::commit();
                return ['success'=>true,'msg'=>'領取成功'];
            }
            return ['success'=>false,'msg'=>'優惠券尚未發行'];


        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("receive coupons fail by invite: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'系統出錯'];
        }
    }
    /**
     *下單會員number加1
     */
    public static function updateMemberNumber($id){
        (new \App\User())->where('id',$id)->increment('number',1);
    }
    /**
     * 更新店鋪人氣次數,一個月以內訂單數
     */
    public static function updateStoreOrderNumber($store_id){
        $count = Order::where('store_id',$store_id)
            ->where('status','>=',0)
            ->whereBetween('created_at',[date('Y-m-d H:i:s',time()-30*24*3600),date('Y-m-d H:i:s',time())])
            ->count();
        $data = (new \App\StoreData())->where('store_id',$store_id)->first();
        if($data){
            $data->order_number = $count;
            $data->save();
        }else{
            (new \App\StoreData())->insertGetId(['store_id'=>$store_id,'order_number'=>$count]);
        }
    }
    /**
     * 計算該店鋪今日第幾單
     */
    public static function total($store_id){
        $date = date('Y-m-d');
        $count = (new \App\Order())->where(['store_id'=>$store_id,'date'=>$date])->count();
        return ++$count;
    }

    /**
     * 未綁定會員，默認下單綁定店家
     */

    public static function bind_store($store,$member_id){
        $user = (new \App\User())->where('id',$member_id)->first();
        if(!$user->promo_code){
            $user->promo_code = $store->code;
            $user->code_type = 2; //1網紅或會員，2店家
            (new \App\Invitelog())->insert(['promo_uid'=>$store->id,'invite_uid'=>$member_id,'invite_date'=>date('Y-m-d H:i:s'),'invite_type'=>2]);
            $user->save();
        }
    }

    /**
     * 消费足迹
     */
    public static function contact_store($store_id,$member_id){
        $data = (new \App\ContactStore())->where(['member_id'=>$member_id,'store_id'=>$store_id])->first();
        if($data){
            $data->created_at = date('Y-m-d H:i:s');
            $data->number = $data->number + 1;
            $data->save();
        }else{
            (new \App\ContactStore())->insert(['member_id'=>$member_id,'store_id'=>$store_id,'number'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }
    }


    public static function after_order($order){
        \DB::beginTransaction();
        try{
            $member_id = $order->member_id;
            $store_id = $order->store_id;
            $store = (new \App\Store())->where('id',$store_id)->first();
            $account = (new \App\MemberCredits())->where('member_id',$member_id)->first();

            self::updateMemberNumber($member_id);
            self::updateStoreOrderNumber($store_id);
            self::bind_store($store,$member_id);//綁定店家邀請碼
            self::contact_store($store_id,$member_id);
            //  现在是下单就扣积分
            if($order->credits > 0){
                $memberTransData = array(
                    'member_id' => $order->member_id,
                    'type' => 2,
                    'trade_type' => '折抵',
                    'log_date' => date('Y-m-d H:i:s'),
                    'log_no' => MemberCreditsService::generateLogNo(),
                    'amount' => $order->credits,
                    'balance' => $account->total_credits,
                    'status' => 1,
                    'remark' => sprintf('在 %s 使用蜂幣折抵',$order->store_name),
                    'order_id' => $order->id,
                    'order_sn' => $order->order_sn,
                    'date' => date('Y-m-d')
                );
                $member = (new \App\Credits())->where('member_id',$member_id)->first();
                $member->freeze_credits += $order->credits;  //冻结金额
                $member->total_credits -= $order->credits;
                $member->save();

                (new \App\MemberCreditsLog())->insert($memberTransData);
            }

            if($order->order_type == 2 && $order->res_order_id && $order->pay_type==1){
                $url = "http://office.techrare.com:5681/diancan2/public/api/order/change?order_id=".$order->res_order_id."&status=4";
                $result = Service::curl_get_https($url);
                if(!$result){
                    \DB::rollback();
                    \Log::error("update diancan status failed and result==".$result);
                    return ['success'=>false,'msg'=>'下單出錯','flag'=>0];
                }
            }

            if($order->coupons_id){
                (new \App\Coupons())->where('id',$order->coupons_id)->update(['status'=>0]);
            }

            \DB::commit();

            return ['success'=>true,'msg'=>''];

        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("after order fail: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'下單出錯','flag'=>0];
        }


    }
}