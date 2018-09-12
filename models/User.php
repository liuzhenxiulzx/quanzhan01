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
            $_SESSION['money'] = $user['money'];
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













    
}
?>