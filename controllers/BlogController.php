<?php
namespace controllers;

use models\Blog;

class BlogController
{
    // 写日志
    public function create(){
        
        view('blogs.write');
    }
    // 日志列表
    public function index()
    {
       $blog = new Blog;
       $data = $blog->search();
       
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

    public function display(){
        // 接收日志Id
        $id = (int)$_GET['id'];
        $blog = new Blog;
        echo $blog->getDisplay($id);
    }

    public function displayToDb(){
        $blog = new Blog;
        $blog ->displayToDb();
    }

    public function update_display(){
        //接收日志ID
        $id = (int)$_GET['id'];
        // 连接redis
        $redis = \libs\Redis::getredis();

        // 判断 blog_displays 这个 hash 中有没有一个键是 blog-$id 
        $key = "blog-{$id}";   // 拼出日志的键
        // 判断 hash 中是否有这个值
        if($redis->hexists('blog_displays', $key))
        {
            // 累加 并且 返回添加完之后的值
            $newNum = $redis->hincrby('blog_displays', $key, 1);
            echo $newNum;
        }
        else
        {
            // 从数据库中取出浏览量
            $blog = new Blog;
            $display = $blog->getDisplay($id);
            $display++;
            // 加到 redis
            $redis->hset('blog_displays', $key, $display);
            echo $display;
        }
    }
    // 发表日志
    public function write(){
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $id = $blog -> dowrite($title,$content,$is_show);
        
        // 如果日志是公开的就生成静态页
        if($is_show == 1)
        {
            $blog->makeHtml($id);
        }

        // 跳转
        message('发表成功',2,'/blog/index');
    }

    // 删除日志
    public function delete(){
        $id = $_POST['id'];
        $blog = new Blog;
        $blog->delete($id);

        // 静态页删除掉
        $blog->deleteHtml($id);
        message('删除成功',2,'/blog/index');
    }

    // 1.修改日志
    public function edit(){
        // 获取日志ID
        $id = $_GET['id'];
        $blog = new Blog;
        $data = $blog->find($id);

        view('blogs.edit',[
            'data'=>$data,
        ]);

    }
// 2.
    public function update(){
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $id = $_POST['id'];

        $blog = new Blog;
        $data = $blog->update($title,$content,$is_show,$id);

        // 如果日志是公开的就生成静态页
        if($is_show == 1)
        {
            $blog->makeHtml($id);
        }
        else
        {
            // 如果改为私有，就要将原来的静态页删除掉
            $blog->deleteHtml($id);
        }

        message('修改成功',0,'/blog/index');
    }
}
