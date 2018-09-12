<?php
namespace controllers;

use Yansongda\Pay\Pay;

class AlipayController
{
    // bfuioj4214@sandbox.com
    public $config = [
        'app_id' => '2016091700530943',
        // 通知地址
        'notify_url' => 'http://lzx.tunnel.echomod.cn/alipay/notify',
        // 跳回地址
        'return_url' => 'http://lzx.tunnel.echomod.cn/alipay/return',
        // 支付宝公钥
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtVEj54bC2n5w2wHIthz8wkl0kiFXpvcHT3HLVRadZVVxLFi2tyWAVc8BZdYgfmHPaBYdkJYtv9IvGL0n6aHXDgW36cK37VNJjTcKcr2ZpemuvOHR9Dye/jpV1FTYswjlls8lDMfygIXfpkRUsOasRsFhXAmAugM+ypT0C7rPX2AYdIib7o91upkOdvVKGnuO03h5KByYz/IgdMh0OddadKWnKYN9ZSWqqy1RgP0b1MPoaHPb3wJech8P1GOLqYxAGc7IrF5fdnSeSiRCOecGQ8/J4KNsjq+LGhghbuyUUZs8TR773lcsk8j/RzebpgK3/wR2vHQvV/ncgYW1RBirhwIDAQAB',
        // 商户应用密钥
        'private_key' => 'MIIEowIBAAKCAQEAr25mMzWGK7DtPoxXfbWXCJaG5JLQLqScWFOazbiGbY5MiG9/S5jJMAbX4QvhrhsDmXOncwp/GhUYjfN5Dmr6Grp97sH3d+wE1T2RrZMdqUYFQnNE2Z/+U/UqqXQkQ/t0+iQEs3zU/S1xNJQC6cdXBlR8vlSQzTfTGw2ElwNjSl+pwbLS+fiEUAc+SCif1DrF6TmCKiaN6taS/qN8pzaUQmtVjfLFN4oyMseYG1dkm16MnW5O/35JrJxA+988DfM5IHujbojRfd24kKR80ZttDvrwE5d2TP2DUIWhBJP4j6mh6j/hZuAYzEEQltGgeT261AtosnUu5KZEXuRBIwRqyQIDAQABAoIBACXOCbIhZ6+EqiuffL83YbvVDG63gKt8h8C1C5gnmriDQNTqCimVXE3AO8dgkxq88ZKhDMXKzkcloqv8eurzfMBDzh2kqRqrvwLhCMK6AFtZHosKYhzv0JPxMmdgAuyvhK7fEjBHx3Jj15B4EQSmE4fmrxcpDCddT132FMuiTERba8a1JeI1812aX8qdZQid/0e3VMcljCxd+dGTsUk2M633coUSoRZgaZgDL9a2rbBuHsaFy5Vu31+3ds9vOVqjbPD1n5OdYjZnz/rRnA3kWHeUBp/EnKrx0V0ucudz1T55cznww2jgryhOk6ZcjyRdmmI46uxaNF5XZREBCUGF4wECgYEA3ElTZaXznD/3HPA+Dr1wLpQIV/rAkXt3mJBK9gt7CWjdAQ+ZsT2vh1bJ0iTM2TgqenN5f9P+5M7f/otmAlY1ZECCaDw8y5cnE3nGTHn+r2GqBE6/Ctys8a5D7RCn7zt+rPGQzB7w6sjuw4mHrEX2/mv0TjuV/J5718mjPd3C7qECgYEAy99sTePyPnGD/MhVTT3RLEPVfYWeu/X1D91XzaEpZAUX61B8PY7YOIvSauEf3OsZ1pa0Z22UFG2plGi2/HPqk0Ssh0l7PuBjCeWywph2JKeiMkQ3OzZWZmb36JgGj5qhgme1a8l22wxfFLdLDO0YZCJHoBHQhN3Q4+JYC/quUykCgYA9lN4Uj4T/zD33wA2JL1o6GzYN6lKFGSAA7W2XRPRbWA5YapULoHP+gLug3JrLqtDtNHTl0Ez0iATEcxHMW29HnGUYYKaaP6Iz5poR7RTO0J2n4J+1mKVcVtw0XLClx1sNW+afuZKeABgHX94mwdRfoaxFZpDycxep5nuVIvaIoQKBgQCE3/ecxIZsQFv/8yn1j+a27PdGOkJ8yw+P6XmCohlyAQfX7Lss+97c6/SWiGfV+rz6aXrnNjofHmI6Qg2ReJNYrJeuDSxwjJrAJp9dyTSnK0LBdEhzJTUmxNGrHPqZufSuI53QxJvcpLGLtPMLV7Rf/x+jqji3gELXY/xywk3AEQKBgGIjTiXkMLx/lnfNV8iuTbf+jSXHjEnJ+g/4pf0zrk1maTQHqVNL6+lk9DoD1NdsVvTmyNLRyjcU792zmKN75dkmGHId9CcTtSNn7l4xhaTpa64EhN0T+MMjgpFaWGb3fg1yLKkQMek4EZP12UpHnFV/ZuHtiftfsETx1o7vTfPs',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];


    // 发起支付
    public function pay()
    {
        // 接收订单号
        $sn = $_POST['sn'];
        // 取出订单信息
        $order = new \models\Order;
        // 根据订单编号取出订单信息
        $data = $order->payment($sn);
        // 如果订单还未支付就跳到支付宝
        if($data['status'] == 0){
            // 跳转到支付宝
            $alipay = Pay::alipay($this->config)->web([
                'out_trade_no' => $sn,
                'total_amount' => $data['money'],
                'subject' => '智聊系统用户充值-'.$data['money'].'元',
            ]);
                $alipay->send();
      
        }else{
            die('订单状态不允许支付');
        }
    }

    // 支付完成跳回
    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        echo '<h1>支付成功!!!</h1> <hr>';
        echo "<pre>";
        var_dump( $data->all() );
    }


    // 接收支付完成的通知
    public function notify()
    {
        // 生成支付类的对象
        $alipay = Pay::alipay($this->config);
        try{
            
            // 判断消息是否是支付宝发过来的，以及判断这个消息有没有被中途串改，如果改了就抛出异常
            $data = $alipay->verify();  
      
            if($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status== 'TRADE_FINISHED'){

                // 更新订单状态
                $order = new \models\Order;
                // 获取订单信息
                $orderInfo  = $order->payment($data->out_trade_no);
                // 如果订单的状态为未支付状态，说明是第一次收到消息，更新订单状态
                if($orderInfo['status'] == 0 ){
                    // 开启事务
                    $order -> opentranct();

                    // 设置订单为已支付状态
                   $ret1 =  $order -> updataorder($data->out_trade_no);
                    // 更新用户余额
                    $user = new \models\User;
                   $ret2 =  $user->addmoney($orderInfo['money'],$orderInfo['user_id']);

                   if($ret1 && $ret2){
                       $order -> commits();
                   }else{
                       $order -> back();
                   }

                    $fp = fopen(ROOT.'logs/pay.log','a');
                    fwrite($fp,'成功'.'\r\n'); 
                }
            }
            
        } catch (\Exception $e) {
            $fp = fopen(ROOT.'logs/pay.log','a');
            fwrite($fp,'非法的消息'); 
            die('非法的消息');
        }
        // 回应支付宝服务器（如何不回应，支付宝会一直重复给你通知）
        $alipay->success()->send();
    }


    // 退款
    public function refund()
    {
        // 生成唯一退款订单号（以后使用这个订单号，可以到支付宝中查看退款的流程）
        $refundNo = md5( rand(1,99999) . microtime() );
        try{
            $order = [
                'out_trade_no' => '1536385290',    // 退款的本地订单号
                'refund_amount' => 0.01,              // 退款金额，单位元
                'out_request_no' => $refundNo,     // 生成 的退款订单号
            ];
            // 退款
            $ret = Pay::alipay($this->config)->refund($order);
            if($ret->code == 10000)
            {
                echo '退款成功！';
            }
            else
            {
                echo '失败' ;
                var_dump($ret);
            }
        }
        catch(\Exception $e)
        {
            var_dump( $e->getMessage() );
        }
    }





  

































}
