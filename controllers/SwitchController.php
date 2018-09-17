<?php
    namespace controllers;

    class SwitchController{

        public function getAllUsers(){
           $model =  new \models\User;
           $data =  $model->getAll();
           echo json_encode([
            'status_code' => 200,
            'data' => $data,
           ]);
        }



        public function login(){
            $email = $_GET['email'];
            // 退出
            $_SESSION = [];
            // 重新登录
            $user = new \models\User;
            $user->login($email,md5('123123'));
        }














    }

?>