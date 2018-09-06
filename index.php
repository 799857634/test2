<?php
header("content-type:text/html;charset=utf8");

ini_set("memory_limit","512M");
set_time_limit(1800);
include('config.php');     //配置
include('dbpdo.php');      //数据库类
$db = new DBPDO();
$GLOBALS['DB'] = $db;
include('model.php');      //模型类
include('product_property.php');      //模型类

function str_rand($min, $max = ''){
        if($max){
            if($min < 0 || $max < 0 || $min > $max)return 0;
            $length = mt_rand($min, $max);
        }else{
            if($min < 0 )return 0;
            $length = $min;
        }
        $ori_str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i = 0; $i < $length; $i++){
            $str .= $ori_str[mt_rand(0, 61)];
        }
        return $str;
}
 function batch_insert(){
        set_time_limit(1800);
        $insert_counts = 50000;
        $data_length = 1000;
        $sleep_time = 0;
        for($i = 0; $i < $insert_counts; $i++){
            //if($sleep_time){
             //   sleep($sleep_time);
            //}
            $data = [];
            for($j = 0; $j < $data_length; $j++){
                $data[] = [
                    'goods_id' => mt_rand(1000, 9000 ),
                    'product_id' => mt_rand(1000, 9000 ),
                    'property_id' => mt_rand(50, 200 ),
                    'vid' => mt_rand(50, 200 ),
                    'property_name' => str_rand(5, 8),
                    'value_name' => str_rand(1, 10),
                    'goods_type_id' => mt_rand(1, 4),
                    'property_type' => mt_rand(1, 4),
                ];
            }
            $re = ProductProperty::addItem($data);
            if($re){
                echo  $i . '  '; //if(($i+1) % 50 == 0)echo '<br>';
            }else{
                echo '第' . $i . '行失败<br>';
            }
            //ob_flush();
            //flush();
        }
    }
    batch_insert();