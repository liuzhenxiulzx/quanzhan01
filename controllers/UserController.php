<?php
namespace controllers;

// 引入模型类
use models\User;
use models\Order;
use Intervention\Image\ImageManagerStatic as Image;
class UserController
{
    public function hello()
    {
        // 取数据
        $user = new User;
        $name = $user->getName();

        // 加载视图
        view('users.hello', [
            'name' => $name
        ]);
    }

    public function world()
    {
        echo 'world';
    }

    // 注册
    public function regist(){
        view('users.add');
    }

    public function store(){
        // 1.接收表单
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        // 生成激活码
        $code = md5(rand(1,99999));
        // echo $code;
        
        // 保存到redis中
        $redis = \libs\Redis::getredis();

        // 序列化
        $value = json_encode([
            'email'=>$email,
            'password'=>$password,
        ]);

        // 键名
        $key = "temp_user:{$code}";
        $redis->setex($key,300,$value);
        
        // 2.插入数据库
        // $user = new User;
        // $ret = $user->add($email,$password);

        // if(!$ret){
        //     die('注册失败');
        // }
        
        // 3.把消息放到队列中
        // 从邮箱地址中取出姓名  fortheday @ 126.com    fortheday
        $name = explode('@',$email);
        // echo "<pre>";
        // var_dump($name);
        // echo $name;
        // 构造收件地址
        $from = [$email,$name[0]];
        // echo "<pre>";
        // var_dump($from);
       // 构造消息数组
        $message = [
            'title' => '智聊系统-账号激活',
            'content' => "点击以下链接进行激活：<br> 点击激活：
            <a href='http://localhost:9999/user/active_user?code={$code}'>
            http://localhost:9999/user/active_user?code={$code}</a><p>
            如果按钮不能点击，请复制上面链接地址，在浏览器中访问来激活账号！</p>",
            'from' => $from,
        ];
        // var_dump($message);
        // 把消息转成字符串(JSON ==> 序列化)
      $message = json_encode($message);

        // 放到队列中
        $redis = \libs\Redis::getredis();
        $redis->lpush('email', $message);

        echo 'ok';

    }

    public function active_user()
    {
        // 1. 接收激活码
        $code = $_GET['code'];

        // 2. 到 Redis 取出账号
        $redis = \libs\Redis::getredis();
        // 拼出名字
        $key = 'temp_user:'.$code;
        // var_dump($key);
        // 取出数据
        $data = $redis->get($key);
        // var_dump($data);
        // 判断有没有
        if($data)
        {
            // 从 redis 中删除激活码
            $redis->del($key);
            // 反序列化（转回数组）
            $data = json_decode($data, true);
            // 插入到数据库中
            $user = new \models\User;
            $user->add($data['email'], $data['password']);
            // 跳转到登录页面
            header('Location:/user/login');
        }
        else
        {
            die('激活码无效！');
        }
    }

      // 登录
    public function login(){
        view('users.login');
    }
    
    public function dologin(){
        $email = $_POST['email'];
        $password = md5($_POST['password']);
        $user = new \models\User;
        if($user->login($email,$password)){
            // header('Location:/blog/index');
            message('登录成功',2,'/blog/index');
        }else{
            message('密码或邮箱错误',1,'/user/login');
        }
    }

    //退出
    public function logout(){
        $_SESSION = [];
        message('退出成功！',0,'/blog/index');
    }
    
    public function recharge(){
        view('users.Recharge');
    }

    public function dorecharge(){
        // 生成订单
        $money = $_POST['money'];
        $order = new Order;
        $order->create($money);
        message('充值订单已生成，请立即支付',2,'/user/orders');
    }
    
    // 订单列表
    public function orders()
    {
        $order = new Order;
        // 搜索数据
        $data = $order->search();

        // 加载视图
        view('users.order', $data);
    }

    // 查询用户余额
    public function balance(){
        $userId = $_SESSION['id'];

        $user  = new User;
        $data =  $user->selecmoney($userId);
        view('users.balance', [
            'data'=>$data['money']
        ]);
    }


    // 设置头像
    public function face(){
        view('users.face');
    }

    public function Agfaces(){
        $uploade =  \libs\Uploade::getuploads();
        $path = $uploade->uploade('face','face');   //face/20180917/15e3fa16a64ee06c0ed42c1466f08a43.jpeg
        // 裁切图片
        $image = Image::make(ROOT.'public/uploads/'.$path);
        
        // 注意：Crop 参数必须是整数，所以需要转成整数：(int)
        $image->crop((int)$_POST['w'],(int)$_POST['h'],(int)$_POST['x'],(int)$_POST['y']);
        // 保存时覆盖原图
        $image->save(ROOT.'public/uploads/'.$path);

        // 调用模型 保存新的头像
        $user = new \models\User;
        $user->setface('/uploads/'.$path);
        
        // 如果有头像则删除头像
        @unlink(ROOT.'public'.$_SESSION['face']);     
        
        // 没有则设置头像
        $_SESSION['face'] = '/uploads/'.$path;


        message('设置成功',2,'/blog/index');
      
    }

    // 批量上传
    public function allupload(){
        view('users.batchup');
    }

    public function batch(){
     
        //图片存储根目录
        $root = ROOT.'/public/uploads';
        $date = date('Ymd');
        //判断目录中是否有此文件，如果没有就创建
        if(!is_dir($root.'/'.$date)){
            mkdir($root.'/'.$date,0777);
        }
        // 循环五张图片的name
        foreach($_FILES['Album']['name'] as $k => $v ){
             // 获取图片扩展名
            $exction = strrchr($k,'.');
            // 生成唯一文件名
            $name = md5(time().rand(1,9999));
            //完整文件名
            $allName = $name.$exction;
            // 移动文件到指定目录
            move_uploaded_file($_FILES['Album']['tmp_name'][$k],$root.'/'.$date.'/'.$allName);
        }
        
        echo "上传成功";
    }


    // 大图上传-图片分割
    public function uploadbig(){
        // var_dump($_POST);
        // var_dump($_FILES);
        // 接收提交的数据
        $count = $_POST['count']; //总的数量
        $i = $_POST['i']; //当前是第几张
        $size = $_POST['size'];//每块的大学
        $name = "big_img_".$_POST['img_name']; //所有分快的名字

        $img = $_FILES['img']; //图片
        // 保存每个分片
        move_uploaded_file( $img['tmp_name'] , ROOT.'tmp/'.$i );
        $redis = \libs\Redis::getredis();
        // 没上传一张就加一
        $uploadcount = $redis->incr($name);
        // 如果是最后一个分支就合并
        if($uploadcount == $count){
            // 以追回的方式创建并打开最终的大文件
            $fp = fopen(ROOT.'public/uploads/big/'.$name.'.png','a');
            // 循环所有的分片
            for($i=0;$i<$count;$i++){
                // 读取第i号文件并写到大文件中
                fwrite($fp.file_get_contents(ROOT.'tmp/'.$i));
                // 删除第i号临时文件
                unlink(ROOT.'tmp/'.$i);
            }
            // 关闭文件
            fclose($fp);
            // 从redis中删除这个文件对应的编号这个变量
            $redis->del($name);
        }

    }




    // 取出活跃用户
    public function setActiveUser(){
        $user = new User;
        $user->Activeuser();

    }












}

?>