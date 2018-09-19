<?php
   namespace models;
   use PDO;

   class Comment extends Base{
        
        public function add($content,$blog_id){
            $stmt = self::$pdo->prepare('INSERT INTO comment (content,blog_id,user_id) VALUES(?,?,?)');
            $stmt->execute([
                $content,
                $blog_id,
                $_SESSION['id']
            ]);

        }
        

       public function  getComment($blogid){
           $sql = "SELECT c.*,u.email,u.face
                    FROM comment c
                     LEFT JOIN users u
                       ON c.user_id =  u.id
                         WHERE blog_id = ?
                            ORDER BY c.id DESC";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                $blogid
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
       }

   }
?>