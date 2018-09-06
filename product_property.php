<?php

class ProductProperty extends Model
{
    public $table = 'product_property';

    public function __construct()
    {
        parent::__construct($this->table);
    }
    public static function getAll(){
        $m = new ProductProperty();
        return $m->get();
    }
    public static function editById($id ,$arr){
        $m = new ProductProperty();
        return $m->where('id', '=', $id)->update($arr);
    }
    public static function addItem($item){
        $m = new ProductProperty();
        return $m->batchInsert($item);
    }
    public static function getItemById($id){
        $m = new self;
        return $m->where('id', '=', $id)->first();
    }
}