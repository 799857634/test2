<?php
/**
 * 模型抽象类
 * 一个关于各种模型的基本行为类，每个模型都必须继承这个类的方法
 */

class Model {
	public $db;
	public $table;
	public $id;
	public $para;

	private $_select;
	private $_where;
	private $_orderby;
	private $_limit;
	private $_join;


	public function __construct($table){
		global $DB;
		$this->db = &$DB;
		$this->table = DB_PREFIX . "$table";
		$this->id = 0;
		$this->para = array();

		$this->reset();

	}

	public static function create($table){
		$m = new Model($table);
		return $m;
	}

	public function select($fields = '*'){
		$this->_select = $fields;
		return $this;
	}

	public function where($field, $operator = null, $value = null){
		if($operator !== null && $value == null ){
			$value = $operator;
			$operator = '=';
		}
		if($this->_where == ''){
			$this->_where .= SQL::where($field, $operator, $value);
		} else {
			$this->_where .= SQL::andWhere($field, $operator, $value);
		}
		return $this;    
	}

	public function andWhere($field, $operator = null, $value = null){
		return $this->where($field, $operator, $value); 
	}

	public function orWhere($field, $operator = null, $value = null){
		if($operator !== null && $value == null ){
			$value = $operator;
			$operator = '=';
		}
		if($this->_where == ''){
			$this->_where .= SQL::where($field, $operator, $value);
		} else {
			$this->_where .= SQL::orWhere($field, $operator, $value);
		}
		return $this;    
	}

	public function whereIn($field, $operator = null, $value = null){
		if($operator !== null && $value == null ){
			$value = $operator;
			$operator = '=';
		}
		$condition = " $field " . SQL::in( $value ) . " ";
		if($this->_where == ''){
			$this->_where .= SQL::where($condition);
		} else {
			$this->_where .= SQL::andWhere($condition);
		}
		return $this;   
	}

	public function orderBy($field, $sort = "desc"){
		$this->_orderby .= " $field $sort, ";
		return $this;
	}

	public function take( $num ){
		$this->_limit = SQL::limit( $num );
		return $this;
	}

	public function leftJoin($table, $field1, $operator, $field2){
		$this->_join .= SQL::leftJoin($table, $field1, $operator, $field2);
		return $this;
	}

	public function setPara($para){
		$this->para = $para;
		return $this;
	}

	public function find($id){
		if(intval($id) > 0){
			$field = $this->getPriKey();		
			$sql = SQL::select($this->_select) . SQL::from($this->table) . SQL::where($field, '=', '?');
			$sql = $this->clear($sql);
			$item = $this->db->getRow($sql, $id);
			$this->setId($id);
			$this->reset();
			return $item;
		}else{
			return null;
		}		
	}

	public function first(){
		$sql = SQL::select($this->_select);
		$sql .= SQL::from($this->table);
		if($this->_join != ''){
			$sql .= $this->_join;
		}
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
		} 
		if($this->_orderby != ''){			
			$sql .= SQL::orderBy($this->_orderby);
		}
		$sql .= SQL::limit(1);
		//pr($sql);
		$item = $this->db->getRow($sql);
		$this->reset();
		return $item;
	}

	public function delete(){
		$sql = SQL::delete($this->table);		
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
			return $this->db->delete($sql);
		}else{
			$field = $this->getPriKey();
			$value = $this->id;
			if($value > 0){
				$sql .= SQL::where($field, '=', '?');
				$sql = $this->clear($sql);
				$this->reset();
				return $this->db->delete($sql, $value);
			}else{
				return false;
			}
		}
	}

	public function destroy($id, $isIn = false){
		$sql = SQL::delete($this->table);
		$field = $this->getPriKey();
		if($isIn){
			$sql .= SQL::where(" $field IN ( $id ) ");
			$this->reset();
			return $this->db->delete($sql);
		}else{
			$sql .= SQL::where($field, '=', '?');
			$sql = $this->clear($sql);
			$this->reset();
			return $this->db->delete($sql, $id);
		}		
	}

	public function get(){
		$sql = SQL::select($this->_select);
		$sql .= SQL::from($this->table);
		if($this->_join != ''){
			$sql .= $this->_join;
		}
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
		} 
		if($this->_orderby != ''){			
			$sql .= SQL::orderBy($this->_orderby);
		}
		if($this->_limit != ''){
			$sql .= SQL::limit($this->_limit);
		}
		$this->reset();
		return $this->db->getRows($sql);
	}

	public function count() {
		$priKey = $this->getPriKey();
		$sql = SQL::select($priKey);
		$sql .= SQL::from($this->table);
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
		} 
		$this->reset();
		return count( $this->db->getRows($sql) );
	}

	public function paginate($pageSize = 20, $rel = ''){
		$sql = SQL::select($this->_select);
		$sql .= SQL::from($this->table);
		if($this->_join != ''){
			$sql .= $this->_join;
		}
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
		} 
		if($this->_orderby != ''){			
			$sql .= SQL::orderBy($this->_orderby);
		}
		if($this->_limit != ''){
			$sql .= SQL::limit($this->_limit);
		}

		global $R;
		$para = $R;
    	if($pageSize == 0 ){
    		$cPageSize = isset($this->C['page_size']) ? $this->C['page_size'] : 0;
    		if($cPageSize > 0){
    			$pageSize = $cPageSize;
    		}else{
    			$pageSize = 10;
    		}
    	}
    	if(isset($para['route'])) unset($para['route']);

    	if(isset($para['!p'])){
    		$page = $para['!p'];
    		unset($para['!p']);
    	}else{
    		$page = 1;
    	}
        if($para){
        	$rel = '?' . http_build_query($para,'','&') . $rel . '&!p=';
        }else{
        	$rel = '?!p=';
        }
		$url = '/'. CTRL . '/' . ACT . $rel;
		
		//pr($sql);
		$list = $this->db->getPage($sql, $page, $pageSize);
		if($list['total'] > 1){
			$recordCount = $list['total'];
		}else{
			$recordCount = 1;
		}

		$pageCount = ceil($recordCount / $pageSize);
		$listPageSize = 10;
		$startPage = $page - floor($listPageSize / 2);
		if($startPage < 1) $startPage = 1;
		$prePage = $page - 1;
		if($prePage < 1) $prePage = 1;
		$nextPage = $page + 1;
		if($nextPage > $pageCount) $nextPage = $pageCount;
		$middlePage = $startPage + $listPageSize;
		if($middlePage > $pageCount) $middlePage = $pageCount;
		if($page > $pageCount ) $page = $pageCount;

		$P = array(
			'url'			=> $url,
			'page'			=> $page,
			'pageSize'		=> $pageSize,
			'firstPage'		=> 1,
			'lastPage'		=> $pageCount,
			'pageCount'		=> $pageCount,
			'recordCount'	=> $recordCount,
			'prePage'		=> $prePage,
			'nextPage'		=> $nextPage,
			'startPage'		=> $startPage,
			'middlePage'	=> $middlePage			
		);

		$ret['P'] = $P;
		$ret['list'] = $list['data'];
		$this->reset();
		return $ret;
	}

	public function save(){	
		if($this->para){			
			$field = $this->getPriKey();
			if(isset($this->para[$field]) && $this->para[$field]){
				$this->setId($this->para[$field]);
				$this->_where = SQL::where($field, '=', $this->para[$field]);
			}
			if($this->_where != ''){
				return $this->update($this->para);
			}else{				
				return $this->insert($this->para);
			}			
		}else{
			return false;
		}		
	}

	public function insert2($data){
		$sql = SQL::insert($this->table) . " (";
		$values = '';
		foreach($data as $key=>$val){
			if($values){
				$sql .= ', ';
				$values .= ', ';
			}
			$sql .= "`$key`";
			$values .= ":$key";
		}
		$sql .= ") values ($values)";
		$this->reset();		print_r($sql);exit;
		return $this->db->insert($sql, $data);
	}
    
    public function insert($data){
		$sql = SQL::insert($this->table) . " (";
		$values = '';
		foreach($data as $key=>$val){
			$sql .= "`$key`,";
			$values .= ":$key,";
		}
        $sql = rtrim($sql, ',');
        $values = rtrim($values, ',');
		$sql .= ") values ($values)";
		$this->reset();
		return $this->db->insert($sql, $data);
	}
    
    public function batchInsert($data){
        $sql = SQL::insert($this->table) . " (";
		$values = '';
		foreach($data[0] as $key=>$val){
			$sql .= "`$key`,";
		}
        $sql = rtrim($sql, ',') . ' ) values ';
        foreach($data as $sub){
            $values .= '(';
            foreach($sub as $k=>$v){
                $values .= '"' . $v . '",';
            }
            $values = rtrim($values, ',');
            $values .= '),';
        }
        $values = rtrim($values, ',');
        $sql .= $values;
        $this->reset();
		return $this->db->insert($sql);
    }

	public function update($data){
		$field = $this->getPriKey();
		$sql = SQL::update($this->table);
		foreach($data as $key=>$value){
			if($this->_where == '' && isset($data[$field]) && $data[$field] != ''){
				$this->_where = SQL::where($field, '=', $value);
				unset($data[$field]);
				continue;
			}
			$sql .= " `$key`=:$key,";			
		}
		$sql = rtrim($sql, ',');				
		if($this->_where != ''){
			$sql .= SQL::where($this->_where);
			$this->reset();			
			return $this->db->update($sql, $data);
		}else{
			$this->reset();		
			return false;
		}		
	}	

	private function getFields(){
		$return = array();
		$fields = $this->db->getFields($this->table);
		if($fields){
	        foreach ($fields as $key=>$value) {
	        	if($value['COLUMN_NAME'] != 'created_at' 
	        		&& $value['COLUMN_NAME'] != 'updated_at' 
	        		&& $value['EXTRA'] != 'auto_increment' ){

	        		$return[$value['COLUMN_NAME']] = $value['COLUMN_DEFAULT'];
	        	}
	            
	        }
		}
		return $return;
	}

	private function getPriKey(){
		$priKey = '';
		$fields = $this->db->getFields($this->table);
		if($fields){
	        foreach ($fields as $key=>$value) {
	        	if($value['COLUMN_KEY'] == 'PRI' && $value['EXTRA'] == 'auto_increment'){
	        		$priKey = $value['COLUMN_NAME']; 
	        	}
	        }
		}
		return $priKey;
	}

	private function setId($id){
		$this->id = $id;
	}

	private function clear($sql){
		$sql = str_replace('\'?\'', '?', $sql);
		return $sql;
	}

	private function reset(){
		$this->_select = "*";
		$this->_where = "";		
		$this->_orderby = "";
		$this->_limit = "";
		$this->_join = "";

	}
}