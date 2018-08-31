<?php
namespace controllers;

use models\Blog;

class BlogController
{
    // 日志列表
    public function index()
    {
       $blog = new Blog;
       $data = $blog->search();
       
        //   加载视图
        View('blogs.index',$data);
    }

    public function content_to_html()
    {
        $blog = new Blog;
        $blog->content_to_html();
    }

    public function index2html(){
        $blog = new Blog;
        $blog->index2html();
    }
}
