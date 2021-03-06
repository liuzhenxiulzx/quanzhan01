<?php
    namespace models;
    use PDO;
    class Base{

           // 保存PDO
           public static $pdo = null;

           public function  __construct()
           {
                if(self::$pdo === null){
                     // 取日志的数据
                    // self::$pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','');
                    // self::$pdo->exec('SET NAMES UTF8');

                    // 读取配置文件
                    $config = config('db');
                    // 取日志的数据
                    self::$pdo = new \PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'], $config['username'], $config['password']);
                    self::$pdo->exec('SET NAMES '.$config['charset']);
                }
                
           }

          //  开启事务
           public function opentranct(){
               self::$pdo -> exec('start transaction');
           }
        
           //  提交事务
           public function commits(){
               self::$pdo -> exec('commit');
           }

           //回滚事务
           public function back(){
               self::$pdo -> exec('rollback');
           }







    }   
?>