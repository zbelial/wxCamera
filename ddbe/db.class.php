<?php 
// namespace utilties;

class db{

    private $DBname = null;
    private $DBip = null;
    private $DBuser = null;
    private $DBpwd = null;
    private $TabName = null;
    private $object_str = null;
    private $where_str = null;
    private $other_Str = null;
    private $newObj_str = null;
    private $sql_str = null;
    public  $PDO_LINK = null;

    // protected static $_instance = null;

    // 连接数据库
    function __construct($DBname, $DBip, $DBuser, $DBpwd){
        $this->DBname = $DBname;
        $this->DBip = $DBip;
        $this->DBuser = $DBuser;
        $this->DBpwd = $DBpwd;

        try {
            $this->PDO_LINK = new PDO("mysql:host=$this->DBip;dbname=$this->DBname;",$this->DBuser,$this->DBpwd);

            $this->PDO_LINK->setAttribute(PDO::ATTR_AUTOCOMMIT,1);
            $this->PDO_LINK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $this->PDO_LINK->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("connect fail!".$e->getMessage());
        }

        $this->PDO_LINK->query('set names utf8');
    }

    // SingleExp
    // public function getInstance($DBname,$DBip,$DBuser,$DBpwd) {

    //     if (self::$_instance === null) {
    //         self::$_instance = new self($DBname,$DBip,$DBuser,$DBpwd);
    //     } 
    //     return self::$_instance;
    // }

    // 释放连接
    public function overConnect(){
        $this->PDO_LINK = null;
        unset($this->PDO_LINK);
        $this->relaseThis();
    }
    // 设置数据表名
    public function select_Tab($TabName){
        // $TabName = strval($TabName);
        $this->TabName = $TabName;
        return $this;
    }
    // 设置sql执行的对象
    public function select_Obj($object_str){
        // $object_str = strval($object_str);
        $this->object_str = $object_str;
        return $this;
    }
    public function select_Where($where_str){
        // $where_str = strval($where_str);
        $this->where_str = ' WHERE '.$where_str.' ';
        return $this;
    }
    // 补充的其他sql语句
    public function select_other($other_Str){
        // $other_Str = strval($other_Str);
        $this->other_Str = ' '.$other_Str.' ';
        return $this;
    }
    // 设置新对象
    public function set_newObj($newObj_str){
        // $newObj_str = strval($newObj_str);
        $this->newObj_str = $newObj_str;
        return $this;
    }
    private function do_sql_query($sqlstr){

        $queryBool = $this->PDO_LINK->exec($sqlstr);
        $this->relaseThis();
        if ($queryBool === false) {
            return array('pass' => false);
            
        }else{
            return array('pass' => true);
        }
    }
    private function relaseThis(){
        unset($this->sql_str);
        unset($this->where_str);
        unset($this->other_Str);
        unset($this->object_str);
        unset($this->TabName);
        $this->sql_str = null;
        $this->where_str = null;
        $this->other_Str = null;
        $this->object_str = null;
        $this->TabName = null;
    }

    // 查
    public function search_command(){
        $this->sql_str = "SELECT $this->object_str FROM $this->TabName".$this->where_str.$this->other_Str;

        try {
            $sql_query = $this->PDO_LINK->query($this->sql_str);
            $returnArr = $sql_query->fetchAll(PDO::FETCH_ASSOC);

            // 如果不释放的话就会占满空间，无法进行新建一个类中一个函数内两次调用此方法
            $this->relaseThis();

            return $returnArr;
        } catch (Exception $e) {
            return array('pass' => false);
            // var_dump($e);
        }
            
    }
    // 改
    public function update_command(){
        $this->sql_str = "UPDATE $this->TabName SET $this->object_str = $this->newObj_str $this->where_str";
        return $this->do_sql_query($this->sql_str);
    }

    // 更新 - 新版
    public function update_new_command($update_array) {
        $updateStr = null;
        $max = count($update_array);
        $num = 1;

        foreach ($update_array as $key => $value) {
            $keyvalstr = $key.'="'.mysql_escape_string($value).'"';
            $updateStr.=$keyvalstr;
            if ($num < $max) {
                $updateStr.=',';
            }
            $num++;
        }

        $this->sql_str = "UPDATE $this->TabName SET $updateStr $this->where_str";
        return $this->do_sql_query($this->sql_str);
    }

    // 删
    public function del_command(){
        $this->sql_str = "DELETE FROM $this->TabName $this->where_str";
        return $this->do_sql_query($this->sql_str);
    }
    // 增
    public function insert_command(){
        $this->sql_str = 'INSERT INTO '.$this->TabName.'('.$this->object_str.') value('.$this->newObj_str.')';
        return $this->do_sql_query($this->sql_str);
    }

    // 更新 - 新版 增
    public function insert_new_command($insert_array){
        // $insert_array是一个二维数组
        $insertString = null;

        for ($i=0; $i < count($insert_array); $i++) {
            $insert_str_temp = "(";
            $length = count($insert_array[$i]);

            for ($j=0; $j < $length; $j++) {
                $variable = $insert_array[$i][$j];
                if ($j < ($length-1)) {
                    $insert_str_temp.="'$variable',";
                } else {
                    $insert_str_temp.="'$variable')";
                }
            }

            if($i == 0) {
                $insertString = $insert_str_temp;
            } else {
                $insertString .= ",$insert_str_temp";
            }
        }

        $this->sql_str = 'INSERT INTO '.$this->TabName.'('.$this->object_str.') values'.$insertString;
        return $this->do_sql_query($this->sql_str);
    }
}














