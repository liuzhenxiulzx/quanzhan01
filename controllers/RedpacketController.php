<?php
    namespace controllers;

    class RedpacketController{

        // 初始化
        public function init(){
            $redis = \libs\Redis::getredis();
            // 初始化库存量
            $redis->set('redbag_stock',20);
            // 初始化空的集合
            $key = 'redbag_'.date('Ymd');
            $redis->sadd($key,'-1');
            // 设置过期
            $redis->expire($key,3900);  
        }

        // 监听队列 当有新的数据时就生成订单
        public function makeOrder(){
            $redis = \libs\Redis::getredis();
            $model = new \models\Redpacket;

            // 设置socket 用不过期
            ini_set('default_socket_timeout',-1);

            echo "开始监听红包队列\r\n";

            // 循环监听一个列表
            while(true){
                // 从队列中取数据，设置为永久不超时
                $data = $redis->brpop('redbag_orders',0);
                // 返回的数据是一个数组用户的ID
                // 处理数据
                echo $userId = $data[1];

                // 下订单
                $model->create($userId);

                echo "有人抢到红包了\r\n";
            }
        }


        // 抢红包
        public function grab(){
            // 1.判断是否登录
            if(!isset($_SESSION['id'])){
                echo json_encode([
                    'status_code' => '401',
                    'message'=>'必须先登录'
                ]);
                exit;
            }
            // 2.判断是否在时间段内
            if(date('H')<9 || date('H')>18){
                echo json_encode([
                    'status_code' => '403',
                    'message'=>'不在时间段内'
                ]);
                exit;
            }
            // 3.判断是否已经抢过
            $key = 'redbag_'.date('Ymd');
            $redis = \libs\Redis::getredis();
            // sismember() 判断元素是否属于集合
            $exists = $redis->sismember($key,$_SESSION['id']);
            if($exists){
                echo json_encode([
                    'status_code' => '403',
                    'message'=>'今天已经抢过了'
                ]);
                exit;
            }
            // 4.判断库存量
            // decr 先减少库存量，并返回减完之后的值
            $stock = $redis->decr('redbag_stock');
            if($stock <= 0){
                echo json_encode([
                    'status_code' => '403',
                    'message'=>'今天的红包以抢完'
                ]);
                exit; 
            }
            // 5.下订单 (放入队列)
            $redis->lpush('redbag_orders',$_SESSION['id']);

            // 6.把ID放到集合中（代表已经抢过了）
            $redis -> sadd($key,$_SESSION['id']);

            echo json_encode([
                'status_code' => '200',
                'message'=>'恭喜你，抢到了红包'
            ]);

        }

        // 显示抢红包页面
        public function rob_view(){
            view('redbag.grab');
        }



    }
?>