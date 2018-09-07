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




















    
}
?>