<?php
namespace controllers;
use models\Blog;
class IndexController
{

    // 取出最新20条数据
    public function index(){
        $blog = new Blog;
        $data = $blog->getNew();
        view('index.index',[
            'blogs'=>$data
        ]);
    }
}

?>