<?php
    namespace models;
    use PDO;
    class Redpacket extends Base{

        public function create($userId){
            $stmt = self::$pdo->prepare("INSERT INTO redbag(user_id) VALUES(?)");
            var_dump($stmt);
            $stmt->execute([$userId]);
        }

     



    }
?>