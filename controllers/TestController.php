<?php
    namespace controllers;
    
    class TestController{
        public function testLog(){
            $log = new \libs\log('email');
            $log->log('发表成功');
        }

    }
?>