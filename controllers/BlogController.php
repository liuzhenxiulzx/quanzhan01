<?php
namespace controllers;

use models\Blog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    // 取出最新20条数据
    public function getnewblog(){
        $blog = new Blog;
        $blog->getNew();
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


    // 显示私有日志
    public function privateblog(){
        // 1. 接收ID，并取出日志信息
        $id = $_GET['id'];
        $model = new Blog;
        $blog = $model->find($id);

        // 2. 判断这个日志是不是我的日志
        if($_SESSION['id'] != $blog['user_id'])
            die('无权访问！');

        // 3. 加载视图
        view('blogs.content', [
            'blog' => $blog,
        ]);
    }
    

    // 生成excel 表
    public function excel(){
         // 获取当前标签页
        $spreadsheet = new Spreadsheet();
        // 获取当前工作
        $sheet = $spreadsheet->getActiveSheet();
        // 设置第一行内容
        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '内容');
        $sheet->setCellValue('C1', '发表时间');
        $sheet->setCellValue('D1', '是发公开');
        // 取出数据库中的日志
        $model = new \models\Blog;
        // 获取最新的20条日志
        $blogs = $model->getNew();
        $i = 2;
        foreach($blogs as $v){
            $sheet->setCellValue('A'.$i,$v['title']);
            $sheet->setCellValue('B'.$i,$v['content']);
            $sheet->setCellValue('C'.$i,$v['created_at']);
            $sheet->setCellValue('D'.$i,$v['is_show']);
            $i++;
        }
        $date = date('Ymd');
        // 生成 excel 文件
        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT . 'excel/'.$date.'.xlsx');

        // 下载excel
        // 调用header函数 设置协议头 告诉浏览器开始下载文件

        // 下载文件路径
        $file = ROOT.'excel/'.$date.'.xlsx';
        // 下载时文件名
        $fileName = '最新20条日志'.$date.'.xlsx';
        //告诉浏览器这是一个文件流格式的文件    
        Header ( "Content-type: application/octet-stream" ); 
        //请求范围的度量单位  
        Header ( "Accept-Ranges: bytes" );  
        //Content-Length是指定包含于请求或响应中数据的字节长度    
        Header ( "Accept-Length: " . filesize ( $file ) );  
        //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
        Header ( "Content-Disposition: attachment; filename=" . $fileName );    

        // 读取并输出文件内容
        readfile($file);

    }   

    // 点赞
    public function good_up(){
        // 获取日志ID
        $id = $_GET['id'];
        // echo $id;
        // 判断是否登录
        if(!isset($_SESSION['id'])){
            echo json_encode([
                'status_code' => '403',
                'message'=>'必须先登录'
            ]);
            exit;
        }

        // 开始点赞
        $blog = new \models\Blog;
        $ret =  $blog->thumbs_up($id);
        if($ret){
            echo json_encode([
                'status_code'=>'200',
            ]);
            exit;
        }else{
            echo json_encode([
                'status_code'=>'403',
                'message'=>'已经点赞过了'
            ]);
        }
    }


    // 点赞用户列表
    public function good_uplist(){
        $id = $_GET['id'];
        // 获取这个日志所有点赞的用户
        $blog = new \models\Blog;
        $data = $blog->gduplist($id);

        // 转成json返回
        echo json_encode([
            'status_code'=> 200,
            'data'=>$data,
        ]);
    }


}
