<?php
namespace App\Api\V1\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Common\Services\BaseNotificationService;

class NotificationService extends BaseNotificationService
{
    /**
     * onesignal App Id
     * @var string
     */
    protected $appId    = '89dfbfe8-ebd5-4fd4-8e8b-b840d3c4d466';

    /**
     * onesignal Rest Key,use to connect to rest server
     * @var string
     */
    protected $restKey  = 'MDU5ZmEwOTAtYjExYy00YzBmLWE5MGYtZjE5YWEwMzQ1NGFk';



    /**
     *
     * example: $data = array('store_id' => 10,'order_id; => 1);
     *
     * @param $data
     * @return array
     */
    private function memberCancelOrder($data){
        $fields = array(
            'app_id' => $this->appId,
            'data' => array(
                'msg_type' => 'order_cancel',
                'order_id' => $data['order_id']
            ),
            'headings' => [
                'en' => '訂單取消通知',
                'zh-Hant' => '會員取消交易'
            ],
            'contents' => [
                'en' => '會員取消交易',
                'zh-Hant' => '會員取消交易'
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => $data['store_id']
                )
            )
        );
        return $fields;
    }

    /**
     *
     * example: $data = ['store_id' => 10,'order_id' => 1];
     *
     * @param $data
     * @return array
     */
    private function memberAddOrder($data){
        $fields = array(
            'app_id' => $this->appId,
            'data' => array(
                'msg_type' => 'order_add',
                'order_id' => $data['order_id']
            ),
            'headings' => [
                'en' => '你有新的訂單',
                'zh-Hant' => '你有新的訂單'
            ],
            'contents' => [
                'en' => '你有新的訂單待處理',
                'zh-Hant' => '你有新的訂單待處理'
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => $data['store_id']
                )
            )
        );
        return $fields;
    }



    public function sendOrderNotification($data,$type='cancel'){
        $fields = array();
        if($type == 'add'){
            $fields = $this->memberAddOrder($data);
        }else if($type == 'cancel'){
            $fields = $this->memberCancelOrder($data);
        }
        self::sendNotification($fields);
    }

    /**
     * push notification to onesignal by Rest api
     */
    public static function sendNotification($fields){
        $notificationService = new NotificationService();
        $fields = count($fields)?$fields:array();
        $fields = json_encode($fields);
        try{
            $client = new Client();
            $promise = $client->requestAsync('POST','https://onesignal.com/api/v1/notifications',[
                'headers' =>array(
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Basic '.$notificationService->restKey
                ),
                'body'=>$fields
            ]);
            $promise->then(
                function (ResponseInterface $res){
                    $body = $res->getBody();
                    $stringBody = (string) $body;
                    \Log::debug("onesignal: ".$stringBody);
                },
                function (RequestException $e) {
                    \Log::debug("onesignal error: ".$e->getMessage().'  '.$e->getRequest()->getMethod());
                }
            );
            $promise->wait();
        }catch (RequestException $e) {
            \Log::error("send fail");
        }
    }
}