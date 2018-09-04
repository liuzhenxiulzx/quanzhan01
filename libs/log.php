<?php
    namespace libs;

    class Log{
        private $fp;
        // 参数：日志文件名
        public function __construct($fileName){
            // 打开日志文件 
            // fopen (打开文件路径，打开文件的模式）：打开一个文件。
            // fwrite(内容)：向文件中写内容

            $this->$fp = fopen(ROOT.'logs/'.$fileName.'.log'.'a');
        }

        // 向日志文件中追加内容
        public function log($content){
            // 获取当前时间
            $data = date('Y-m-d H:i:s');
            //拼出日志内容的格式
            $c = $data."\r\n";
            $c .= str_repeat('=',120)."r\n"; //str_repeat:获取30个=
            $c .= $content . "\r\n\r\n";
            fwrite($this->fp,$c); 
        }
    }
?>