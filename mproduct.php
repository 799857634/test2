<?php

class Mproduct extends Model
{
    public $table = 'z_product';

    public function __construct()
    {
        parent::__construct($this->table);
    }
    public static function getAll(){
        $m = new Mproduct();
        return $m->get();
    }
    public static function editById($id ,$arr){
        $m = new Mproduct();
        return $m->where('id', '=', $id)->update($arr);
    }
    public static function addItem($item){
        $m = new Mproduct();
        return $m->insert($item);
    }
    public static function getItemByGoodsId($goods_id){
        $m = new Mproduct();
        return $m->where('goods_id', '=', $goods_id)->first();
    }
}