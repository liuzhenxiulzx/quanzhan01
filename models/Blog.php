<?php
    namespace models;

    use PDO;
    
    
    class Blog extends Base
    {

        // 日志列表
        public function search()
        {
            // 取日志的数据
            // $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','');
            // $pdo->exec('SET NAMES UTF8');
    
            // 设置的 $where 取出所有日志
            // $where = 1;
            // 只取出自己的日志
            if(isset($_SESSION['id'])){
                $where = 'user_id='.$_SESSION['id'];
            }else{
                $where = 1;
            }
            
    
            // 放预处理对应的值
            $value = [];
            
            // 如果有keword 并值不为空时
            if(isset($_GET['keyword']) && $_GET['keyword'])
            {
                $where .= " AND (title LIKE ? OR content LIKE ?)";
                $value[] = '%'.$_GET['keyword'].'%';
                $value[] = '%'.$_GET['keyword'].'%';
            }
   
            if(isset($_GET['start_date']) && $_GET['start_date'])
            {
                $where .= " AND created_at >= ?";
                $value[] = $_GET['start_date'];
            }
   
            if(isset($_GET['end_date']) && $_GET['end_date'])
            {
                $where .= " AND created_at <= ?";
                $value[] = $_GET['end_date'];
            }
    
            if(isset($_GET['is_show']) && ($_GET['is_show']==1 || $_GET['is_show']==='0'))
            {
                $where .= " AND is_show = ?";
                $value[] = $_GET['is_show'];
            }
    
    
            /***************** 排序 ********************/
            // 默认排序
            $odby = 'created_at';
            $odway = 'desc';
    
            if(isset($_GET['odby']) && $_GET['odby'] == 'display')
            {
                $odby = 'display';
            }
    
            if(isset($_GET['odway']) && $_GET['odway'] == 'asc')
            {
                $odway = 'asc';
            }
    
            /****************** 翻页 ****************/
            $perpage = 15; // 每页15
            // 接收当前页码（大于等于1的整数）， max：最参数中大的值
            $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
            // 计算开始的下标
            // 页码  下标
            // 1 --> 0
            // 2 --> 15
            // 3 --> 30
            // 4 --> 45
            $offset = ($page-1)*$perpage;
    
            // 制作按钮
            // 取出总的记录数
            $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM blog WHERE $where");
            $stmt->execute($value);
            $count = $stmt->fetch( PDO::FETCH_COLUMN );
            // 计算总的页数（ceil：向上取整（天花板）， floor：向下取整（地板））
            $pageCount = ceil( $count / $perpage );
    
            $btns = '';
            for($i=1; $i<=$pageCount; $i++)
            {
                // 先获取之前的参数
                $params = getUrlParams(['page']);
    
                $class = $page==$i ? 'active' : '';
                $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
                
            }
            
      
            /*************** 执行 sqL */
            // 预处理 SQL
            $stmt = self::$pdo->prepare("SELECT * FROM blog WHERE $where ORDER BY $odby $odway LIMIT $offset,$perpage");
            // 执行 SQL
            $stmt->execute($value);
    
            // 取数据
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // print_r($data);
            // 加载视图
            view('blogs.index', [
                'data' => $data,
                'btns' => $btns,
            ]);
        }


        public function content_to_html()
        {
            // 取日志的数据
            // $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','');
            // $pdo->exec('SET NAMES UTF8');

            $stmt = self::$pdo->query('SELECT * FROM blog');
            $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 开启缓冲区
            ob_start();

            // 生成静态页
            foreach($blogs as $v)
            {
                // 加载视图
                view('blogs.content', [
                    'blog' => $v,
                ]);
                // 取出缓冲区的内容
                $str = ob_get_contents();
                // 生成静态页
                file_put_contents(ROOT.'public/contents/'.$v['id'].'.html', $str);
                // 清空缓冲区
                ob_clean();

            }
        }
 

        public function index2html()
        {
            // 取前20条记录数据
            $stmt = self::$pdo->query("SELECT * FROM blog WHERE is_show=1 ORDER BY id DESC LIMIT 20");
            $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 开启一个缓冲区
            ob_start();

            // 加载视图文件到缓冲区
            View('index.index',[
                'blogs'=>$blogs
            ]);

            // 从缓冲区中取出页面
            $str = ob_get_contents();

            // 把页面的内容生成一个静态页面
            file_put_contents(ROOT.'public/index.html', $str);
        }


        // 把内存中的浏览量会写到数据库中
        public function getDisplay($id){
            // 使用日志ID拼出键名
            $key = "blog-{$id}";

            // 连接redis
            $redis = \libs\Redis::getredis();

            // 判断hash 中是否有这个键，如果有就操作内存，如果没有就从数据库中取
            if($redis->hexists('blog_displays',$key)){
                // 累加并且返回添加完之后的值
                $newNum = $redis->hincrby('blog_displays',$key,1);
                return $newNum;
            }else{
                // 从数据库中取出浏览量
                $stmt = self::$pdo->prepare('select display from blog where id =?');
                $stmt->execute([$id]);
                $display = $stmt->fetch(PDO::FETCH_COLUMN);
                $display++;
                // 保存到redis
                $redis->hset('blog_displays',$key,$display);
                return $display;
            }
        }

        
        public function displayToDb()
            {
                // 1. 先取出内存中所有的浏览量
                // 连接 Redis
                $redis = \libs\Redis::getredis();
                
                $data = $redis->hgetall('blog_displays');

                // 2. 更新回数据库
                foreach($data as $k => $v)
                {
                    $id = str_replace('blog-', '', $k);
                    $sql = "UPDATE blog SET display={$v} WHERE id = {$id}";
                    self::$pdo->exec($sql);
                }
            }

        // 删除日志
        public function delete($id){
            // 只能删除自己的日志
            $stmt = self::$pdo->prepare('DELETE FROM blog WHERE id = ? AND user_id = ?');
            $stmt->execute([
                $id,
                $_SESSION['id'],
            ]);
        }
        
        // 写日志
        public  function dowrite($title,$content,$is_show){
            $stmt = self::$pdo->prepare("INSERT INTO blog(title,content,is_show,user_id,created_at) VALUES (?,?,?,?,now())");
            $ret = $stmt->execute([
                $title,
                $content,
                $is_show,
                $_SESSION['id'],
            ]);
                echo $_SESSION['id'];
            if(!$ret){
                echo "发表失败";
                $error = $stmt->errorInfo();
                echo "<pre>";
                var_dump($error);
                exit;
            }
            // 返回新插入的记录的ID
            return self::$pdo->lastInsertId();
        }

        // 修改日志
        public function find($id){
            $stmt = self::$pdo->prepare('SELECT * FROM blog where id = ?');
            $stmt->execute([
                $id
            ]);
            // 取出数据
            return $stmt->fetch();
        }
        // 处理修改的日志表
       public function update($title,$content,$is_show,$id){
            $stmt = self::$pdo->prepare("UPDATE blog SET title=?,content=?,is_show=? WHERE id=? ");
            $ret = $stmt->execute([
                $title,
                $content,
                $is_show,
                $id,
            ]);
       }

       //为某一个日志生成静态页
       // 参数：日志ID
       public function makeHtml($id){
            // 1.取出日志的信息
            $blog = $this->find($id);
            // 2.打开缓冲区，并且加载视图到缓冲区
            ob_start();
            view('blogs.content',[
                'blog'=>$blog
            ]);
            // 3.从缓冲区中取出视图并写到静态页中
            $str  = ob_get_clean();
            file_put_contents(ROOT.'public/contents/'.$id.'.html',$str);

       }

        // 删除静态页
        public function deleteHtml($id){
            // @ 防止 报错：有这个文件就删除，没有就不删除，不用报错
            @unlink(ROOT.'public/contents/'.$id.'.html');
        }



        // 取出最新20条数据
        public function getNew()
        {
            $stmt = self::$pdo->query('SELECT * FROM blog WHERE is_show=1 ORDER BY id DESC LIMIT 20');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }








    }
?>
