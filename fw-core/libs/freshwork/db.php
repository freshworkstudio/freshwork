<?php

class DB {
    protected $_dbHandle;
    protected $_result;
	protected $_query;
	protected $_table;
	protected $_pk;
	protected $_development_env;

	protected $_describe = array();

	protected $_orderBy;
	protected $_order;
	protected $_extraConditions;
	protected $_hO;
	protected $_hM;
	protected $_hMABTM;
	protected $_page;
	protected $_limit;
	protected $_offset;
	protected $_cond;
	protected $_last_inserted_id;
	public $debug;
    /** Connects to database **/
	
    function connect($address, $account, $pwd, $name) {
        $this->_dbHandle = mysql_connect($address, $account, $pwd);
		echo mysql_error();
        if ($this->_dbHandle != 0) {
            if (mysql_select_db($name, $this->_dbHandle)) {
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }
 
    /** Disconnects from database **/
	
    function disconnect() {
        if (@mysql_close($this->_dbHandle) != 0) {
            return 1;
        }  else {
            return 0;
        }
    }

    /** Select Query **/
	
	/*function where($field, $value, $concat = "AND") {
		$this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` = \''.mysql_real_escape_string($value).'\' '.$concat.' ';
	}*/
	
	function set_debug($val){
		$this->debug = $val;	
	}
	function where($field,$value = false){
		if(!is_array($this->_cond))$this->_cond = array();
		if(is_array($field)){
			$this->_cond = array_merge($this->_cond,$field);
		}else{
			$this->_cond[$field] = $value;
		}
	}
	function nwhere($field,$value){
		if(!is_array($this->_cond))$this->_cond = array();
		$this->_cond[$field] = "//".$value;	
	}
	/*function nwhere($field, $value, $concat = "AND") {
		$this->_extraConditions .= $field.' = '.($value).' '.$concat.' ';
	}*/

	function like($field, $value) {
		//$this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` LIKE \'%'.mysql_real_escape_string($value).'%\' AND ';
		$this->_cond[$field." LIKE"] = "%$value%";	
	}

	function show_has_one() {
		$this->_hO = 1;
	}

	function show_has_many() {
		$this->_hM = 1;
	}

	function show_HMABTM() {
		$this->_hMABTM = 1;
	}

	function set_imit($limit,$offset = 0) {
		$this->_limit = $limit;
		$this->_offset = $offset;
	}

	function set_page($page) {
		if($page <= 0)trigger_error("Page has to be bigger than zero", E_USER_ERROR);
		$this->_page = $page;
	}

	function order_by($orderBy, $order = 'ASC') {
		$this->_orderBy = $orderBy;
		$this->_order = $order;
	}
	function get_search_query(){
		global $inflect;
		
		$join = "";

		$from = '`'.$this->_table.'` as `'.$this->_model.'` ';
		$conditions = '';
		$conditionsChild = '';
		$fromChild = '';
		
		if ($this->_hO == 1 && isset($this->hasOne)) {
			
			foreach ($this->hasOne as $alias => $opts) {
				if(!is_array($opts)){
					$alias = $opts;
					$opts = array("model" => $opts);	 
				}
				//Defaults
				if(!isset($opts["model"]))throw new Exception('hasOne need a "model" parameter to work');
				$model = $opts["model"];
				if(class_exists($model)){
					$modelObj = new $model();
				}
				$singularAlias = strtolower($alias);		
				$fk_singular_model = strtolower(get_class($modelObj));
				$defaults = array(
					"fk" 		=> '`'.$alias.'`.`'.$modelObj->get_pk().'`',
					"id"		=> $this->_model.".".$singularAlias."_id",
					"join"		=> "LEFT JOIN",
					"model"		=> $alias,
					"alias"		=> $alias
				);
				$opts = array_merge($defaults,$opts);
				
								
				
				
				if(isset($modelObj) && $modelObj){
					$tablejoin = $modelObj->getTableName();
				}else{
					$tablejoin = strtolower($inflect->pluralize($opts["model"]));	
				}
				
				
				$from .= $opts["join"].' `'.$tablejoin.'` as `'.$alias.'` ';
				$from .= "ON ".$this->formatCampo($opts["id"])." =  ".$opts["fk"]." ";
				//die($);
			}
		}

		if ($this->_extraConditions) {
			$conditions .= $this->_extraConditions;
		}
		if($this->_cond){
			$conditions .= $this->serializaCondiciones($this->_cond);	
		}
		if($conditions == '')$conditions = "1=1";
		//$conditions = substr($conditions,0,-4);
		
		if (isset($this->_orderBy)) {
			$conditions .= ' ORDER BY '; 
			$conditions .= ($this->_orderBy != "RANDOM")?"`".$this->_model.'`.`'.$this->_orderBy.'` '.$this->_order:"RAND()";
		}

		if (isset($this->_page)) {
			$offset = ($this->_page-1)*$this->_limit;
			$conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
		}else{
			if($this->_limit)$conditions .= ' LIMIT '.$this->_limit;
			if($this->_offset)$conditions .= ' OFFSET '.$this->_offset;
		}
		
		
		$this->_query = 'SELECT * FROM '.$from.' '.$join.' WHERE '.$conditions;
	}
	function search($one = false) {
		
// echo $conditions;
// echo $this->_query;
		$this->get_search_query();
		$this->_result = $this->execute_query($this->_query, $this->_dbHandle);
		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();
		$numOfFields = mysql_num_fields($this->_result);
		for ($i = 0; $i < $numOfFields; ++$i) {
		    array_push($table,mysql_field_table($this->_result, $i));
		    array_push($field,mysql_field_name($this->_result, $i));
		}
		if (mysql_num_rows($this->_result) > 0 ) {
			while ($row = mysql_fetch_row($this->_result)) {
				
				for ($i = 0;$i < $numOfFields; ++$i) {
					$tempResults[$table[$i]][$field[$i]] = $row[$i];
					//echo "tempResults[".$table[$i]."][".$field[$i]."] = row[$i] (".$row[$i].")\n";
				}
				//echo "-----------\n";
				//print_r($table);
				//echo "\n-----------";
				//print_r($tempResults);
				if ($this->_hM == 1 && isset($this->hasMany)) {
					foreach ($this->hasMany as $aliasChild => $modelChild) {
						
						if(!is_array($modelChild)){
							$aliasChild = $modelChild;	
						}
						//Defaults
						$defaults = array(
							"model"		=> $aliasChild,
							"fk" 		=> '`'.$aliasChild	.'`.`'.strtolower($aliasChild).'_id`',
							"id"		=> "`".$this->_model."`.`id`",
						);
						$modelChild = (is_array($modelChild))?$modelChild:array("model" => $modelChild);
						$opts = array_merge($defaults,$modelChild);
						if(!isset($opts["model"]))throw new Exception('hasMany need a "model" parameter to work');
						$modelChild = $opts["model"];
						
						/*try{
							$m = new $modelChild();
							$table_name = $m->getTableName();
						}catch(Exception $e){
							$table_name = strtolower($inflect->pluralize($model));	
						}*/
						
						
						
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';
						
						if(class_exists($modelChild)){
							$modelObjChild = new $modelChild();
						}
						if(isset($modelObjChild) && $modelObjChild){
							$tableChild = $modelObjChild->getTableName();
						}else{
							$tableChild = strtolower($inflect->pluralize($opts["model"]));	
						}
						$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
						$singularAliasChild = strtolower($aliasChild);
						
						
						$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`';
						
						$conditionsChild .= "$opts[fk] = '".$tempResults[$this->_model][$this->_pk]."'";
	
						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;	
						$resultChild = $this->execute_query($queryChild, $this->_dbHandle);
						
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
						
						if (mysql_num_rows($resultChild) > 0) {
							$numOfFieldsChild = mysql_num_fields($resultChild);
							for ($j = 0; $j < $numOfFieldsChild; ++$j) {
								array_push($tableChild,mysql_field_table($resultChild, $j));
								array_push($fieldChild,mysql_field_name($resultChild, $j));
							}

							while ($rowChild = mysql_fetch_row($resultChild)) {
								for ($j = 0;$j < $numOfFieldsChild; ++$j) {
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
						
						$tempResults[$aliasChild] = $resultsChild;
						
						mysql_free_result($resultChild);
					}
				}


				if ($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany)) {
					foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';

						$tableChild = strtolower($inflect->pluralize($tableChild));
						$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
						$singularAliasChild = strtolower($aliasChild);

						$sortTables = array($this->_table,$pluralAliasChild);
						sort($sortTables);
						$joinTable = implode('_',$sortTables);

						$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`,';
						$fromChild .= '`'.$joinTable.'`,';
						
						$conditionsChild .= '`'.$joinTable.'`.`'.$singularAliasChild.'_id` = `'.$aliasChild.'`.`id` AND ';
						$conditionsChild .= '`'.$joinTable.'`.`'.strtolower($this->_model).'_id` = \''.$tempResults[$this->_model]['id'].'\'';
						$fromChild = substr($fromChild,0,-1);

						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;	
						$resultChild = $this->execute_query($queryChild, $this->_dbHandle);
				
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
						
						if (mysql_num_rows($resultChild) > 0) {
							$numOfFieldsChild = mysql_num_fields($resultChild);
							for ($j = 0; $j < $numOfFieldsChild; ++$j) {
								array_push($tableChild,mysql_field_table($resultChild, $j));
								array_push($fieldChild,mysql_field_name($resultChild, $j));
							}

							while ($rowChild = mysql_fetch_row($resultChild)) {
								for ($j = 0;$j < $numOfFieldsChild; ++$j) {
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
						
						$tempResults[$aliasChild] = $resultsChild;
						mysql_free_result($resultChild);
					}
				}

				array_push($result,$tempResults);
			}
			mysql_free_result($this->_result);
			$this->clear();
			
			if ($one)return($result[0]);
			return($result);
		} else {
			mysql_free_result($this->_result);
			$this->clear();
			return $result;
		}

	}
	
	function find($phrase = false, $fields=false){
		if(!$phrase && isset($_REQUEST['q']))$phrase = $_REQUEST['q'];
		if(!$fields)$fields = $this->_describe;
		if(is_string($fields)){
			$fields_tmp = array();
			$tmp = split(",",$fields);
			foreach($tmp as $field){
				$fields_tmp[] = $field;	
			}
			$fields = $fields_tmp;
		}
		$words = split(" ",trim($phrase));
		foreach($words as $key => $word){
			foreach($fields as $field){
				$this->_cond[$key][$field." LIKE"]	= "%$word%";
			}
		}
		return $this->search();
	}	
	
	function getById($id){
		$this->where($this->_pk,$id);
		return $this->search(true);	
	}
	
	function serializeData($data,$tipo = "update"){
		if($tipo == "update"){
			$vals = " ";
			if(is_array($data)){
				foreach($data as $key => $value){
					if(in_array($key,$this->_describe)){
						$value = (substr($value,0,2)=="//")?substr($value,2):"'".mysql_real_escape_string($value)."'";
						$vals .= "`".mysql_real_escape_string($key)."` = $value,";
					}
				}
			}else{ 
				return false;
			}
			$return = substr($vals,0,-1);
		}elseif($tipo == "insert"){
			$fields = "";
			$values = "";	
			if(is_array($data)){
				foreach($data as $key => $value){
					if(in_array($key,$this->_describe)){
						$value = (substr($value,0,2) == "//")?substr($value,2):"'".mysql_real_escape_string($value)."'";
						$fields .= mysql_real_escape_string($key).",";
						$values .= $value.",";
					}
				}
				$fields = substr($fields,0,-1);
				$values = substr($values,0,-1);
				$return= ($fields != "")?"($fields) VALUES ($values)":"";
			}else{ 
				return false;
			}
		}
		return $return;
	}
	function serializaCondiciones($datos,$complete = true, $default = "AND",$inicio = true,$campo = ""){
		$valores = "";
		if(is_array($datos)){
			$valores = "";
			foreach($datos as $key => $valor){
				if(is_array($valor)){
					$def = ($default == "AND")?"OR":"AND";
					$valores .= "(".self::serializaCondiciones($valor,$complete,$def,true,$key).") $default ";
				}else{
					$format = (substr($valor,0,2) != "//");
					if($valor != "" && $valor{0} == "'")$valor = "'".mysql_real_escape_string(substr($valor,1,-1))."'";
					
					if($format)$valor = "'".mysql_real_escape_string($valor)."'";
					else $valor = substr($valor,2);
					
					$key = is_numeric($key)?$campo:$key;
					$keyparts = explode(" ",$key);
					//keyparts[0] = Campo
					//keyparts[1] = Operados - Opcional
					$comparador = (isset($keyparts[1]) && $keyparts[1] != "")?$keyparts[1]:"=";
					if(substr($valor,-3) !=" OR" && substr($valor,-4)!=" AND")$valor .= " $default ";
					if(array_key_exists($keyparts[0],$this->_describe))$keyparts[0] = $this->_describe[$keyparts[0]];
					$field = ($format)?$this->formatCampo($keyparts[0],$complete):$keyparts[0];
					$valores .= "$field $comparador $valor";
				}
			}
			if($inicio)$valores = substr($valores, 0,-4);
			if($valores == "")return false;
		}elseif(is_numeric($datos)){
			$valores = $this->formatCampo($this->primaryKey,$complete)." = $datos";
		}
		if($valores == "")$valores = "1=1";
		return $valores;
	}
	function formatCampo($str,$complete = true,$is_select = false){
		//$str = $this->getCampoGrande($str,false,true);
		//$str = $this->getCampo($str,false,true);
		$camp = explode(".",$str);
		if($complete){
			if(count($camp) == 2)$str = "`$camp[0]`.`$camp[1]`";
			else $str = "`".$this->_model."`.`$camp[0]`";
		}else{
			if(count($camp) == 2)$str = "`$camp[1]`";
			else $str = "`$camp[0]`";
		}
		if($is_select && isset($camp[1]))$this->campos_select[] = array("modelo" => $camp[0],"campo" => $camp[1]);
		return $str;
	}
	
	function get_last_inserted_id(){
		return $this->_last_inserted_id;	
	}
	function get_last_saved_id(){
		return $this->_last_saved_id;	
	}
    /** Custom SQL Query **/

	function custom($query) {

		global $inflect;
		
		if (isset($this->_page)) {
			$offset = ($this->_page-1)*$this->_limit;
			$query = str_replace(array("[LIMIT]","[OFFSET]","[PAGINATION]"),
								 array($this->_limit,$offset,"LIMIT ".$this->_limit." OFFSET ".$offset),
								 $query);
			$this->_query = $query;
		}
		$this->_query = $query;
		
		$this->_result = $this->execute_query($this->_query, $this->_dbHandle);

		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();

		if(substr_count(strtoupper($query),"SELECT")>0) {
			if (mysql_num_rows($this->_result) > 0) {
				$numOfFields = mysql_num_fields($this->_result);
				for ($i = 0; $i < $numOfFields; ++$i) {
					array_push($table,mysql_field_table($this->_result, $i));
					array_push($field,mysql_field_name($this->_result, $i));
				}
					while ($row = mysql_fetch_row($this->_result)) {
						for ($i = 0;$i < $numOfFields; ++$i) {
							$table[$i] = ucfirst($inflect->singularize($table[$i]));
							$tempResults[$table[$i]][$field[$i]] = $row[$i];
						}
						array_push($result,$tempResults);
					}
			}
			mysql_free_result($this->_result);
		}
		$this->clear();
		return($result);
	}

    /** Describes a Table **/

	protected function _describe() {
		global $cache;
		
		$cachekey = 'describe'.$this->_table;
		
		if ($cache->isCached($cachekey) && !$this->_development_env) {
			$cachedata = $cache->get($cachekey);
			$this->_describe = $cachedata["cols"];
			$this->_pk = $cachedata["pk"];	
		}else{
			$this->_describe = array();
			$query = 'DESCRIBE '.$this->_table;
			
			$this->_result = $this->execute_query($query, $this->_dbHandle);
			if(!($this->_result)){
				throw new Exception("Al parecer, la tabla '".$this->_table."' no existe");
				return false;
			}
			while ($row = mysql_fetch_row($this->_result)) {
				if($row[3] == "PRI")$this->_pk = $row[0];
				 array_push($this->_describe,$row[0]);
			}
			mysql_free_result($this->_result);
			$cache->set('describe'.$this->_table,array(
				"cols" 	=> $this->_describe,
				"pk"	=> $this->_pk
			));
			
		}

		foreach ($this->_describe as $field) {
			$this->$field = null;
		}
	}
	function clean_fields($arr){
		return array_intersect_key($arr, array_flip($this->_describe));	
	}
	function clean_where(){
		$this->_cond = $this->clean_fields($this->_cond);
	}
	
	function get_pk(){
		return $this->_pk;	
	}
    /** Delete an Object **/

	function delete($cond,$limit=1,$forceAll = false) {
		if(is_numeric($cond))$cond = array($this->_pk => $cond);
		$limit = ($limit)?"LIMIT $limit":"";
		$conditions = $this->serializaCondiciones($cond,false);
		if($conditions != "1=1"){
			$conditions = "WHERE $conditions";
		}else{
			if(!$forceAll){
				trigger_error("No conditions sent to delete method. If you want to delete all the data of the table you have to set ".'forceAll (third)'." parameter to true",E_USER_WARNING);
				return false;
			}
			$conditions = "";
		}
		
		$query = "DELETE FROM ".$this->_table." $conditions $limit";		
		$this->_result = $this->execute_query($query, $this->_dbHandle);
		
		$this->clear();
		if ($this->_result == 0)return -1;
	   return true;
	}

    /** Saves an Object i.e. Updates/Inserts Query **/

	function save($data,$cond = false) {
		if(is_numeric($cond))$cond = array($this->_pk => $cond);
		if(is_array($cond)){
			foreach($cond as $key => $value){
				if(!isset($value)){
					trigger_error("No conditions sent to save method. One or more of the conditions sent in the array was not set.",E_USER_WARNING);
					return false;	
				}
			}
		}
		$update = false;
		if($cond){
			$this->where($cond);
			$update = ($search_result = $this->search(true));
		}
		$res = false;
		if($update){
			
			//Exists
			$res = $this->update($data,$cond);
			$this->_last_saved_id = $search_result[$this->_model][$this->_pk];
		}else{
			//Doesn't exists
			$res = $this->insert($data);
			$this->_last_saved_id = $this->get_last_inserted_id();
		}
		$this->clear();
		return $res;
		
	}
	
	function update($data,$cond,$limit=1,$forceAll=false){
		if(is_numeric($cond))$cond = array($this->_pk => $cond);
		$limit = ($limit)?"LIMIT $limit":"";
		$conditions = $this->serializaCondiciones($cond,false);
		if($conditions != "1=1"){
			$conditions = "WHERE $conditions";
		}else{
			if(!$forceAll){
				trigger_error("No conditions sent to update method. If you want to update all the data of the table you have to set ".'forceAll (fourth)'." parameter to true",E_USER_ERROR);
				return -1;
			}
			$conditions = "";
		}
		$updates = $this->serializeData($data,"update");
		if($updates == ""){
			trigger_error("Parameter ".'$data'." (first) is empty or contains no existing fields in the table '".$this->_table."'. Can't update without setting columns.",E_USER_WARNING);
			return -1;	
		}
		
		$query = 'UPDATE '.$this->_table." SET $updates $conditions $limit";
		$this->_result = $this->execute_query($query, $this->_dbHandle);
		$this->clear();
		if ($this->_result == 0) {
            /** Error Generation **/
			return -1;
        }
		return true;
	}
	function insert($data){
		$inserts = $this->serializeData($data,"insert");
		if($inserts == ""){
			trigger_error("Parameter ".'$data'." (first) is empty or contains no existing fields in the table '".$this->_table."'. Can't insert without data.",E_USER_WARNING);
			return -1;	
		}

		$query = 'INSERT INTO '.$this->_table." $inserts";
		$this->_result = $this->execute_query($query, $this->_dbHandle);
		$this->_last_inserted_id = mysql_insert_id();
		
		$this->clear();
		if ($this->_result == 0) {
            /** Error Generation **/
			return -1;
        }	
		return true;
	}
 
	/** Clear All Variables **/

	function clear() {
		foreach($this->_describe as $field) {
			$this->$field = null;
		}

		$this->_cond = array();
		$this->_limit = 1;
		$this->_orderby = array();
		$this->_hO = null;
		$this->_hM = null;
		$this->_hMABTM = null;
		$this->_page = null;
		$this->_order = null;
	}

	/** Pagination Count **/
	function get_count($intern = false){
		if(!$intern)$this->get_search_query();
		if(!$intern && !$this->_query){
			trigger_error("Can't Obtain total count if _query isn't set");
			return -1;
		}
		$pattern = '/(SELECT (.*?) FROM (.*)) (LIMIT (.*))+/i';
		$replacement = 'SELECT COUNT(*) FROM $3';
		$query = preg_replace($pattern, $replacement, $this->_query);
		$this->_result = $this->execute_query($query, $this->_dbHandle);
		$count = mysql_fetch_row($this->_result);
		return (int)$count[0];
	}
	function get_total_pages() {
		if(!$this->_limit)return -1;
		$count = $this->get_count();
		$totalPages = ceil($count/$this->_limit);
		return $totalPages;
	}


    /** Get error string **/

    function get_error() {
        return mysql_error($this->_dbHandle);
    }
	
	function set_table_name($name){
		$this->_table = $name;	
	}
	function get_table_name(){
		return $this->_table;	
	}
	
	function execute_query($sql,$dbHandle){
		$res = mysql_query($sql,$dbHandle);
		if($this->debug){
			echo '<!--'.$sql." (Error: ".$this->get_error().")-->\n";
		}
		if(!$res)trigger_error('Error SQL: '.$this->get_error(),E_USER_WARNING);
		return $res;
	}
}