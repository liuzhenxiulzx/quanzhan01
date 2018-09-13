<?php
    namespace controllers;

    use Yansongda\Pay\Pay;

    class WxpayController {
        protected $config = [
            'app_id' => 'wx426b3015555a46be', // 公众号 APPID
            'mch_id' => '1900009851',
            'key' => '8934e7d15453e97507ef794cf7b0519d',
             // 通知地址
            'notify_url' => ' http://lzx.tunnel.echomod.cn/wxpay/notify',
        ];

        public function pay(){
            // phpinfo();
            $order = [
                'out_trade_no' => time(),
                'total_fee' => '1', // **单位：分**
                'body' => 'test body - 测试',
                // 'openid' => 'onkVf1FjWS5SBIixxxxxxx',
            ];
    
            $pay = Pay::wechat($this->config)->scan($order);
    
            echo $pay->return_code , '<hr>';
            echo $pay->return_msg , '<hr>';
            echo $pay->appid , '<hr>';
            echo $pay->result_code , '<hr>';
            echo $pay->code_url , '<hr>';
        }

        public function notify()
        {
            $log = new \libs\Log('wxpay');
            // 记录日志
            $log->log('接收到微信的消息');

            $pay = Pay::wechat($this->config);
    
            try{
                $data = $pay->verify(); // 是的，验签就这么简单！
                $log->log('验证成功，接受的数据是：'.file_get_contents('php://input'));

                if($data->result_code == 'SUCCESS' && $data->return_code == 'SUCCESS')
                {
                    // 记录日志
                    $log->log('支付成功');
                    // 更新订单状态
                    $order = new \models\Order;
                    // 获取订单信息
                    $orderInfo  = $order->payment($data->out_trade_no);
                    
                    echo '共支付了：'.$data->total_fee.'分';
                    echo '订单ID：'.$data->out_trade_no;
                }
    
            } catch (Exception $e) {
                var_dump( $e->getMessage() );
            }
            
            $pay->success()->send();
        }
    }
?>