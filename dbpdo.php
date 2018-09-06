<?php
/**
 * 数据库操作类，继承自PDO，PDO常用的方法如下：
 * PDO->beginTransaction() — 标明回滚起始点
 * PDO->commit() — 标明回滚结束点，并执行 SQL
 * PDO->__construct() — 建立一个 PDO 链接数据库的实例
 * PDO->errorCode() — 获取错误码
 * PDO->errorInfo() — 获取错误的信息
 * PDO->exec() — 处理一条 SQL 语句，并返回所影响的条目数
 * PDO->getAttribute() — 获取一个“数据库连接对象”的属性
 * PDO->getAvailableDrivers() — 获取有效的 PDO 驱动器名称
 * PDO->lastInsertId() — 获取写入的最后一条数据的主键值
 * PDO->prepare() — 生成一个“查询对象”
 * PDO->query() — 处理一条 SQL 语句，并返回一个“PDOStatement”
 * PDO->quote() — 为某个 SQL 中的字符串添加引号
 * PDO->rollBack() — 执行回滚
 * PDO->setAttribute() — 为一个“数据库连接对象”设定属性
 * PDOStatement->bindColumn() — Bind a column to a PHP variable
 * PDOStatement->bindParam() — Binds a parameter to the specified variable name
 * PDOStatement->bindValue() — Binds a value to a parameter
 * PDOStatement->closeCursor() — Closes the cursor, enabling the statement to be executed again.
 * PDOStatement->columnCount() — Returns the number of columns in the result set
 * PDOStatement->errorCode() — Fetch the SQLSTATE associated with the last operation on the statement handle
 * PDOStatement->errorInfo() — Fetch extended error information associated with the last operation on the statement handle
 * PDOStatement->execute() — Executes a prepared statement
 * PDOStatement->fetch() — Fetches the next row from a result set
 * PDOStatement->fetchAll() — Returns an array containing all of the result set rows
 * PDOStatement->fetchColumn() — Returns a single column from the next row of a result set
 * PDOStatement->fetchObject() — Fetches the next row and returns it as an object.
 * PDOStatement->getAttribute() — Retrieve a statement attribute
 * PDOStatement->getColumnMeta() — Returns metadata for a column in a result set
 * PDOStatement->nextRowset() — Advances to the next rowset in a multi-rowset statement handle
 * PDOStatement->rowCount() — Returns the number of rows affected by the last SQL statement
 * PDOStatement->setAttribute() — Set a statement attribute
 * PDOStatement->setFetchMode() — Set the default fetch mode for this statement
 */

class DBPDO extends PDO{
	function __construct(){
		try{
			parent::__construct(DB_DRIVER .':host='.DB_HOSTNAME.';dbname='.DB_DATABASE, DB_USERNAME, DB_PASSWORD);
		}catch(Exception $e){
			throw new Exception('DB Not Found');
			exit;
		}
		$this->query('set names '.DB_CHARSET.';');
	}
	
	public function __get($name){
		if(method_exists($this, $method='get'.ucfirst($name))){
			return $this->$method();
		}else{
			return $this->$name;
		}
	}

	public function __set($name, $value){
		if(method_exists($this, $method='set'.ucfirst($name))){
			$this->$method($value);
		}else{
			$this->$name=$value;
		}
	}

	/** 
    * 取得数据表的字段信息 
    * @param string $tbName 表名
    * @return array 
    */
    public function getFields($tbName) {
        $sql = 'SELECT COLUMN_NAME, COLUMN_KEY, COLUMN_TYPE, COLUMN_COMMENT, EXTRA, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="'.trim($tbName).'" AND TABLE_SCHEMA="'.DB_DATABASE.'"';
        $stmt = $this->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //pr($return, 1);
        return $return;   
    }    

	public function getRow($sql, $params=null, $expire=0){
		//pr($sql);
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return unserialize(file_get_contents($file));
			}else{
				$data=$this->getRow($sql, $params);
				file_put_contents($file, serialize($data));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);			
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$return=$stmt->fetch();
			$stmt=null;
			return $return;
		}
	}

	public function getRows($sql, $params=null, $expire=0){		
		//pr($sql);
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return unserialize(file_get_contents($file));
			}else{
				$data=$this->getRows($sql, $params);
				file_put_contents($file, serialize($data));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$return=$stmt->fetchAll();
			$stmt=null;
			return $return;
		}
	}

	public function getObject($sql, $field, $params=null, $expire=0){
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return unserialize(file_get_contents($file));
			}else{
				file_put_contents($file, serialize($data=$this->getObject($sql, $field, $params)));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$return=$stmt->fetchAll();
			$stmt=null;
			//print_r($return);exit;
			$data=array();
			if($return) foreach($return as $var){
				$data[$var[$field]]=$var;
			}
			//print_r($data);exit;
			return $data;
		}
	}

	public function getPage($sql, &$page=1, $pageSize=20, $params=null, $expire=0){
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return unserialize(file_get_contents($file));
			}else{
				file_put_contents($file, serialize($data=$this->getPage($sql, $page, $pageSize, $params)));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);

			$return['total']=$stmt->rowCount();
			if($return['total']<=$pageSize){
				$return['data']=$stmt->fetchAll();
			}elseif($page<=1){
				for($i=0;$i<$pageSize; $i++) $return['data'][]=$stmt->fetch();
			}else{
				$pageCount=ceil($return['total']/$pageSize);
				if($page>$pageCount) $page=$pageCount;
				$startRow=($page-1)*$pageSize;
				$sql.=" LIMIT $startRow, $pageSize";
				$return['data']=$this->getRows($sql, $params);
			}
			$stmt=null;
			return $return;
		}
	}	

	public function getCol($sql, $params=null, $expire=0){
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return unserialize(file_get_contents($file));
			}else{
				file_put_contents($file, serialize($data=$this->getCol($sql, $params)));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$ret=array();
			while(($val=$stmt->fetchColumn())!==false) $ret[]=$val;
			$stmt=null;
			return $ret;
		}
	}

	public function getValue($sql, $params=null, $expire=0){
		if($expire){
			if(is_file($file=DIR_CACHE.md5($sql.serialize($params))) && (filemtime($file)+$expire) > time()){
				return file_get_contents($file);
			}else{
				file_put_contents($file, $data=$this->getValue($sql, $params));
				return $data;
			}
		}else{
			$stmt=$this->prepare($sql);
			if(!is_array($params)){
				// 如果传入的是一个值
				$params=array($params);
			}else if(array_key_exists(0, $params)){
				// 如果传入的是一个数字索引的数组
				//$stmt=$this->prepare($sql, $params);
			}else{
				// 如果传入的是一个字符索引的数组
				//$para=array();
				//foreach($params as $key=>$val) $para[":$key"]=$val;
				//$params=$para;
			}
			$stmt->execute($params);
			$return=$stmt->fetchColumn();
			$stmt=null;
			return $return;
		}
	}
	public function update($sql, $params=null){
		return $this->process($sql, $params);
	}

	public function insert($sql, $params=null){		
		return $this->process($sql, $params);
	}	

	public function delete($sql, $params=null){
		return $this->process($sql, $params);
	}

	private function process($sql, $params=null){
		if($params){
			if(!is_array($params)){
				$params=array($params);
			}
			if(!$stmt=$this->prepare($sql)){
				return 'SQL ERROR：'.$sql;
			}
			if(!$return=$stmt->execute($params)){
				$err=$stmt->errorInfo();
				return json_encode($err);
			}
			$id = $this->lastInsertId();
			if($id){
				return $id;
			}else{
				return $return;
			}		
		}else{
			if($this->exec($sql)){
				return true;
			}else{
				$err=$this->errorInfo();
				return $err;
			}
		}
	}
}



/**
 * SQL类。
 * 
 */
class SQL
{
    /**
     * select语句。
     */
    public static function select($fields = '*')
    {
        return "SELECT $fields ";
    }

    /**
     * update语句。
     */
    public static function update($table)
    {
        return "UPDATE $table SET ";
    }

    /**
     * insert语句。
     */
    public static function insert($table)
    {
        return "INSERT INTO $table ";
    }

    /**
     * delete语句。
     */
    public static function delete($table)
    {
        return "DELETE FROM $table ";
    }

    /**
     * 创建From部分。
     */
    public static function from($table, $alias='')
    {
        return " FROM $table $alias ";
    }

    /**
     * 创建WHERE部分。
     */
    public static function where($field, $operator = null, $value = null)
    {
        if($value !== null){

			$condition = "$field $operator '$value' ";

        }else{
            $condition = $field;
        }
        stripos(strtolower($condition), 'where') !== false ? $sql = " $condition " : $sql = " WHERE $condition ";
        return $sql;
    } 

    /**
     * 创建AND部分。
     */
    public static function andWhere($field, $operator = null, $value = null, $addMark = false)
    {
    	if($value !== null){
			if(stripos($value,"'") !== false){
				$condition = $field .' '.$operator .' '.'"'. $value .'"';
			}else{
				$condition = "$field $operator '$value' ";
			}
        }else{
            $condition = $field;
        }
        if($addMark){
        	return " AND ( $condition ) ";
        }else{
        	return " AND $condition ";
        }
    }

    /**
     * 创建OR部分。
     */
    public static function orWhere($field, $operator = null, $value = null, $addMark = false)
    {
    	if($value !== null){
            $condition = "$field $operator '$value' ";
        }else{
            $condition = $field;
        }
        if($addMark){
        	return " OR ( $condition ) ";
        }else{
        	return " OR $condition ";
        }
    }

    /**
     * 创建LEFT JOIN部分。
     */
    public static function leftJoin($table, $field1, $operator, $field2 )
    {
    	if($field1 != '' && $field2 != ''){
    		$join = "LEFT JOIN $table ON $field1 $operator $field2";
    	}else{
    		$join = "LEFT JOIN $table";
    	} 
    	stripos(strtolower($join), 'join') !== false ? $sql = " $join " : $sql = " LEFT JOIN $join ";
        return $sql;      
    }

    /**
     * 创建ON部分。
     */
    public function on($condition)
    {
        return " ON $condition ";
    }

    /**
     * 创建"BETWEEN AND"。
     */
    public static function between($min, $max)
    {
        return " BETWEEN $min AND $max ";
    }

    /**
     * 创建LIMIT部分。
     */
    public static function limit($limit)
    {
        if($limit != ''){
        	stripos(strtolower($limit), 'limit') !== false ? $sql = " $limit " : $sql = " LIMIT $limit ";
        	return $sql;
        }        
    }

    /**
     * 创建IN部分。
	 * 将数组或者列表转化成 IN( 'a', 'b') 的形式
	 */
	public static function in($ids)
	{
	    if(is_array($ids)){
	        if(!function_exists('get_magic_quotes_gpc') or !get_magic_quotes_gpc()){
	            foreach ($ids as $key=>$value)  $ids[$key] = addslashes($value); 
	        }
	        return " IN ('" . join("','", $ids) . "')";
	    }

	    if(!function_exists('get_magic_quotes_gpc') or !get_magic_quotes_gpc()) $ids = addslashes($ids);
	    return " IN ('" . str_replace(',', "','", str_replace(' ', '', $ids)) . "')";
	}

    /**
     * 创建'NOT IN'部分。
     */
    public static function notin($ids)
    {
    	if(is_array($ids)){
	        if(!function_exists('get_magic_quotes_gpc') or !get_magic_quotes_gpc()){
	            foreach ($ids as $key=>$value)  $ids[$key] = addslashes($value); 
	        }
	        return " NOT IN ('" . join("','", $ids) . "')";
	    }

	    if(!function_exists('get_magic_quotes_gpc') or !get_magic_quotes_gpc()) $ids = addslashes($ids);
	    return " NOT IN ('" . str_replace(',', "','", str_replace(' ', '', $ids)) . "')";
    }

    /**
     * 创建LIKE部分。
     */
    public static function like($field, $string)
    {
        return " $field LIKE '$string' ";
    }

    /**
     * 创建NOT LIKE部分。
     */
    public static function notLike($field, $string)
    {
        return " $field NOT LIKE '$string' ";
    }

    /**
     * 创建ORDER BY部分。
     */
    public static function orderBy($order)
    {
        $order = str_replace(array('|', ''), ' ', $order);        
        $order = trim(trim($order, ', '), ',');
   
        /*
        $orders = explode(',', $order);
        foreach($orders as $i => $order)
        {
            $orderParse = explode(' ', trim($order));
            foreach($orderParse as $key => $value)
            {
                $value = trim($value);
                if(empty($value) or strtolower($value) == 'desc' or strtolower($value) == 'asc') continue;
                $field = trim($value, '`');

                //such as t1.id field.
                if(strpos($value, '.') !== false) list($table, $field) = explode('.', $field);
                //Ignore order with function e.g. order by length(tag) asc
                if(strpos($field, '(') === false) $field = "`$field`";

                $orderParse[$key] = isset($table) ? $table . '.' . $field :  $field;
                unset($table);
            }
            $orders[$i] = join(' ', $orderParse);
        }
        $order = join(',', $orders);
        */
        return " ORDER BY $order ";
    }

    /**
     * 创建GROUP BY部分。
     */
    public function groupBy($groupBy)
    {
        return " GROUP BY $groupBy ";
    }

    /**
     * 创建HAVING部分。
     */
    public function having($having)
    {
        return " HAVING $having";
    }
}