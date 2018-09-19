<?php
namespace controllers;
use models\Blog;
class IndexController
{

    // 取出最新20条数据
    public function index(){
        $blog = new Blog;
        $data = $blog->getNew();

        // 取出活跃用户
        $user = new \models\User;
        $users = $user->getActiveUser();

        view('index.index',[
            'blogs'=>$data,
            'users'=>$users
        ]);
    }
}

?>