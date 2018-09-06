<?php
namespace controllers;

// 引入模型类
use models\User;

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
            echo "登录成功";
        }else{
            echo "密码或邮箱错误";
        }
    }

    
}
?>