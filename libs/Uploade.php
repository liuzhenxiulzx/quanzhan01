<?php
    namespace libs;
    class Uploade{
        // 私有的静态属性
        private static $objs = null ;
        // 私有的克隆，防止克隆
        private function __clone(){}
        // 私有的构造，防止在类外部 new 对象
        private function __construct(){}

        public static function getuploads(){

            if(self::$objs == null){
                // 生成一个对象
                self::$objs = new self;
            }
            return self::$objs;
        }
        
        // 定义属性

        private $root = ROOT.'/public/uploads/'; //图片保存的一级目录
        private $exction = ['image/jpeg','image/jpg','image/png','image/gif','image/bmp','image/ejpeg'];
        private $maxsize = 1024*1024*1.8; //最大上传的尺寸
        private $file;  //保存用户上传的图片的信息
        private $subDir;

        //定义公开方法
        // 上传图片
        public function uploade($name,$sudir){
            // 把用户图片的信息保存到属性中
            $this->file = $_FILES[$name];
            $this->subDir = $sudir;

            if(!$this->checkType()){
                die('图片类型错误');
            }

            if(!$this->checkSize()){
                die('图片尺寸错误');
            }

            // 创建目录
            $dir = $this->makeDir();
            // var_dump();
            // 生成唯一的名字
            $name = $this->makeName();
            // 移动图片
            move_uploaded_file($this->file['tmp_name'],$this->root.$dir.$name);
            // 返回二级目录开始的路径
            return $dir.$name;

        }

        // 定义私有的方法
        // 创建目录
        private function makeDir(){
            $dir = $this->subDir.'/'.date('Ymd');
            if(!is_dir($this->root.$dir)){
                mkdir($this->root.$dir,0777,true);
            }
            return $dir.'/';
        }

        // 生成唯一文件名
        private function makeName(){
            $name = md5(time().rand(1,9999));
            $ext = strrchr($this->file['name'],'.');
            return $name.$ext;
        }

        // 图片类型方法
        private function checkType(){
            return in_array($this->file['type'],$this->exction);
        }

        // 图片尺寸
        private function checkSize(){
            return $this->file['size'] < $this->maxsize;
        }

    }
?>