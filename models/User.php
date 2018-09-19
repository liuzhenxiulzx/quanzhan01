<?php
namespace models;
use PDO;
class User extends Base
{
    public function getName()
    {
        return 'Tom';
    } 


    public function add($email,$password)
    {
        $stmt = self::$pdo->prepare("INSERT INTO users (email,password) VALUES(?,?)");
        return $stmt->execute([
                                $email,
                                $password,
                            ]);
    }

    // 登录
    public function login($email,$password){
        $stmt = self::$pdo->prepare("select * from users where email = ? and password = ?");
        $stmt->execute([
            $email,
            $password
        ]);
    
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($user){
            $_SESSION['id']=$user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['money'] = $user['money'];
            $_SESSION['face'] = $user['face'];
            return true;
        }else{
            return false;
        }
    }


    // 为用户增加金额
    public function addmoney($money,$userId){
        
        $stmt = self::$pdo->prepare("UPDATE users SET money=money+? WHERE id=?");
        $stmt->execute([
            $money,
            $userId
        ]);
        
        // 更新session中的余额
        // $_SESSION['money'] += $money;
    }




    public function selecmoney($userId){
        
        $stmt = self::$pdo->prepare("SELECT money FROM users where id=?");
        $stmt->execute([
            $userId
        ]);
        $money = $stmt->fetch(\PDO::FETCH_ASSOC);
        // var_dump($money);
        // die;
       return $money;
    }



    // 设置头像
    public function setface($path){
        $stmt = self::$pdo->prepare('UPDATE users SET face=? WHERE id=?');
        
        $stmt->execute([
            $path,
            $_SESSION['id']
        ]);
    }



    // 切换账号
    public function getAll(){
        $stmt = self::$pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    // 计算活跃用户
    public function Activeuser(){
        // 取日志的分值
        $stmt = self::$pdo->query('SELECT user_id,COUNT(*)*5 fz
                                        FROM blog
                                            WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 1 WEEK)
                                                GROUP BY user_id');
        $data1 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 取评论的分值
         $stmt = self::$pdo->query('SELECT user_id,COUNT(*)*3 fz
                                        FROM comment
                                            WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 1 WEEK)
                                                GROUP BY user_id');
        $data2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 取出点赞的分值
        $stmt = self::$pdo->query('SELECT user_id,COUNT(*) fz
                                        FROM thumbs_up
                                            WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 1 WEEK)
                                                GROUP BY user_id');
        $data3 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 合并数组
        $arr = [];

        foreach($data1 as $v){
            $arr[$v['user_id']] = $v['fz'];
        }

        // 合并第2个数组
        foreach($data2 as $v){
            if(isset($arr[$v['user_id']]))
                $arr[$v['user_id']] += $v['fz'];
            else
                $arr[$v['user_id']] = $v['fz'];
            
        }

        // 合并第3个数组
        foreach($data3 as $v){
            if(isset($arr[$v['user_id']]))
                $arr[$v['user_id']] += $v['fz'];
            else
                $arr[$v['user_id']] = $v['fz'];
            
        }

        // 倒叙排序
        arsort($arr);
        // 取出前20条并保持键 （第四个参数保留键）
        $data = array_splice($arr,0,20,true);

        // 从数组中取出所有的键
        $userId = array_keys($data);

        // 数组转成字符串 [1,2,3,4,5,6]  => 1,2,3,4,5
        $userId = implode(',',$userId);

        // 取出用户的头像和email
        $sql = "SELECT id,email,face FROM users WHERE id IN($userId)";
        $stmt = self::$pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // echo "<pre>";
        // var_dump($data);

        //  把计算的结果保存到 redis 中，因为redis 中只能保存字符串，所以要转成字符串格式
        $redis = \libs\Redis::getredis();
                    // 别名
        $redis->set('active_users',json_encode($data));
    }


    //从redis中取出数据
    public function getActiveUser(){
        $redis = \libs\Redis::getredis();
        $data = $redis->get('active_users');
        // 转回数组 （第二个参数 true:数组   false：对象）
        return json_decode($data,true);
    }
}
?>