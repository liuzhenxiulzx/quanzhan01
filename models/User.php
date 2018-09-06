<?php
namespace models;

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
            return true;
        }else{
            return false;
        }
    }

    // 写日志
    public  function dowrite($title,$content,$is_show){
        $stmt = self::$pdo->prepare("INSERT INTO blog(title,content,is_show,user_id) VALUES (?,?,?,?)");
        $ret = $stmt->execute([
            $title,
            $content,
            $is_show,
            $_SESSION['id'],
        ]);

        if(!$ret){
            echo "发表失败";
            // $error = $stmt->errorInfo();
            // echo "<pre>";
            // var_dump($error);
            // exit;
        }
        // 返回新插入的记录的ID
        return self::$pdo->lastInsertId();
    }


















    
}
?>