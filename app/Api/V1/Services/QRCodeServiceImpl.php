<?php

namespace App\Api\V1\Services;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use App\User;

class QRCodeServiceImpl
{
    private function getMemberInfo($id){
        return User::where('id',$id)->first();
    }

    public static function generateMemberCode($member_id){
        $serviceImpl = new QRCodeServiceImpl();
        $memberInfo = $serviceImpl->getMemberInfo($member_id);

        $logoPath = $memberInfo->avatar;
        $logoPath = empty($logoPath) ? public_path('/images/avatar/').'/avatar.png': public_path('/').'/'.$logoPath;
        if(!file_exists($logoPath)){
            $logoPath = public_path('/images/avatar/').'/avatar.png';
        }
        $qrCode = new QrCode();
        $qrCode->setText('https://office.techrare.com/memecoins-register-h5/#/register/'.$memberInfo->invite_code.'/1');
        $qrCode->setSize(180);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(10);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);

        $qrCode->setLogoWidth(80);
        //生成图片
        //ob_start();
//        header("Cache-Control: no-cache, must-revalidate");
//        header('Content-Type: image/png');
        $qrCodePath = public_path('/upload/temp/'.$memberInfo->id.'.png');

        $qrCode->writeFile($qrCodePath);
        $image = url('upload/temp').'/'.$memberInfo->id.'.png';
        return array('image'=>$image);
    }

}