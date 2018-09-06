<?php
    namespace libs;
    // 三私一公
    class Redis{

        private static $redis = null ;
        // 私有的克隆，防止克隆
        private function __clone(){}
        // 私有的构造，防止在类外部 new 对象
        private function __construct(){}
         // 唯一对外公共的方法，用来获取唯一的 redis 对象
        public static function getredis(){
            // 如果还没有 redis 就生成一个
           // 只有每 一次 才会连接
            if(self::$redis === null){

                self::$redis = new \Predis\Client([
                    'scheme' => 'tcp',
                    'host'   => '127.0.0.1',
                    // 'port'   => 32768,
                    'port'=> '6379',
                ]);

            }
            // 返回已有的redis对象
            return self::$redis;
        }


            
    }
?>