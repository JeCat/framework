<?php
namespace org\jecat\framework\mvc\model ;

use org\jecat\framework\mvc\model\executor\Selecter;

use org\jecat\framework\db\DB;

use org\jecat\framework\lang\Exception;

class Model
{
	public function __construct($table,$sPrototypeName,$primaryKeys=null,$columns=null)
	{
		if( is_string($table) )
		{
			$this->aPrototype = new Prototype($table,$sPrototypeName,$primaryKeys,$columns) ;
		}
		else if( $table instanceof Prototype )
		{
			$this->aPrototype = $table ;
		}
		else
		{
			throw new Exception("Model::__construct() 参数\$table类型错误") ;
		}

		//$this->sDataPrefix = $this->aPrototype->name() . '.' ;
		//$this->nDataPrefixLength = strlen($this->sDataPrefix) ;
	}
	/**
	 * @return Model
	 */
	static public function create($sTable,$sPrototypeName=null,$primaryKeys=null,$columns=null)
	{
		return new self($sTable,$sPrototypeName,$primaryKeys,$columns) ;
	}
	/**
	 * @alias org\jecat\framework\mvc\model\Prototype::addOrder
	 * @return Model
	 */
	public function order($columns,$bDesc=true)
	{
		$this->aPrototype->addOrder($columns,$bDesc) ;
		return $this ;
	}
	/**
	 * 设置 limit
	 * @return Model
	 */
	public function limit($nLen,$pos=null)
	{
		$this->aPrototype->setLimit($nLen,$pos) ;
		return $this ;
	}
	/**
	 * 设置一个或多个 group by 字段
	 * @return Model
	 */
	public function group($columns)
	{
		$this->aPrototype->addGroup($columns) ;
		return $this ;
	}
	/**
	 * 设置一组 where 条件
	 * @return Model
	 */
	public function where($sWhere)
	{
		$this->aPrototype->addWhere($sWhere) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function find($values,$columns=null)
	{
		$this->aPrototype->where( $this->makeSqlFind($values,$columns) ) ;
		return $this ;
	}
	private function makeSqlFind($values,$columns=null)
	{
		if($columns===null)
		{
			$columns = $this->aPrototype->keys() ;
		}
		else
		{
			$columns = (array) $columns ;
		}
		$values = $values===null? array(null): (array)$values ;
		
		$arrWhere = '' ;
		foreach($columns as $nIdx=>&$sColumn)
		{
			$arrWhere[] = $sColumn . "='" . addslashes($values[$nIdx]) . "'" ;
		}
		
		return implode(' AND ',$arrWhere) ;
	}

	/**
	 * @return Model
	 */
	public function hasOne($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null)
	{
		$this->aPrototype->addAssociation(array(
				'assoc' => Prototype::hasOne ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		)) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function belongsTo($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null)
	{
		$this->aPrototype->addAssociation(array(
				'assoc' => Prototype::belongsTo ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		)) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function hasMany($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null)
	{
		$this->aPrototype->addAssociation(array(
				'assoc' => Prototype::hasMany ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		)) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function hasAndBelongsToMany($toTable,$sBridgeTableName,$fromKeys=null,$toBridgeKeys=null,$fromBridgeKeys=null,$toKeys=null,$sAssocName=null)
	{
		$this->aPrototype->addAssociation(array(
				'assoc' => Prototype::hasAndBelongsToMany ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
				'bridge' => $sBridgeTableName ,
				'toBridgeKeys' => $toBridgeKeys ,
				'fromBridgeKeys' => $fromBridgeKeys ,
		)) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function assoc(array $arrOptions)
	{
		$this->aPrototype->addAssociation($arrOptions) ;
		return $this ;
	}
	public function back($step=1)
	{
		$this->aPrototype->back($step) ;
		return $this ;
	}
	
	//////////////////////////////////////////////////////

	/**
	 * 执行 select 操作
	 * 
	 * $values==self::ignore，不使用任何条件
	 * 
	 * 如果 $columns=null，则使用原型的主键
	 * 如果 $columns===true ，则 $values 被当作一个 sql where 字符串
	 * 
	 * @return Model
	 */
	public function load($values=self::ignore,$columns=self::primaryKeys)
	{
		if( $values===self::ignore )
		{
			$sTmpWhere = null ;
		}
		else
		{
			if( $columns===self::asWhereClause )
			{
				$sTmpWhere = $values ;
			}
			else
			{
				$sTmpWhere = $this->makeSqlFind($values,$columns) ;
			}
		}
		
		Selecter::singleton()->execute( $this->aPrototype->refRaw(), $this->arrData, $sTmpWhere, $this->db() ) ;

		print_r($this->arrData) ;
		
		return $this ;
	}
	
	/**
	 * 执行 insert/update 操作
	 * @return Model
	 */
	public function save()
	{
		
	}
	
	/**
	 * 执行删除操作，
	 * 仅仅删除数据库中的记录，Model对像中的数据仍然保留，并且可以在 delete() 以后立即执行 save()
	 * @return Model
	 */
	public function delete()
	{
		
	}
	
	/**
	 * 返回一个“臭名昭著”的 prototype 对像
	 * 真正维护 prototype 的，应该是 Prototype 类，Model类的 order,limit 等方法仅仅提供了简单的
	 * @return Prototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}
	
	/**
	 * @return org\jecat\framework\db\DB
	 */
	public function db()
	{
		if(!$this->aDB)
		{
			$this->aDB = DB::singleton() ;
		}
		return $this->aDB ;
	}
	/**
	 * @return Model
	 */
	public function setDB(DB $aDB)
	{
		$this->aDB = $aDB ;
		return $this ;
	}
	
	
	
	// 数据操作 /////////////////////////////
	
	/**
	 * @return bool
	 */
	public function first($sChildName=null)
	{
		if( $arrParentRow=&$this->localeRow($sChildName,$this->arrData) )
		{
			if( $this->isSheet($arrParentRow,$sChildName) )
			{
				if( !empty($arrParentRow[$sChildName]) )
				{
					reset($arrParentRow[$sChildName]) ;
					return true ;
				}
			}
		}
		return false ;
	}
	/**
	 * @return bool
	 */
	public function last($sChildName=null)
	{
		if( $arrParentRow=&$this->localeRow($sChildName,$this->arrData) )
		{
			if( $this->isSheet($arrParentRow,$sChildName) )
			{
				if( !empty($arrParentRow[$sChildName]) )
				{
					end($arrParentRow[$sChildName]) ;
					return true ;
				}
			}
		}
		return false ;
	}
	/**
	 * @return bool
	 */
	public function prev($sChildName=null)
	{
		if( $arrParentRow=&$this->localeRow($sChildName,$this->arrData) )
		{
			if( $this->isSheet($arrParentRow,$sChildName) )
			{
				if( !empty($arrParentRow[$sChildName]) )
				{
					prev($arrParentRow[$sChildName]) ;
					if( each($arrParentRow[$sChildName])!==false )
					{
						next($arrParentRow[$sChildName]) ;
						return true ;
					}
				}
			}
		}
		return false ;
	}
	/**
	 * @return bool
	 */
	public function next($sChildName=null)
	{
	
	}
	

	public function data($sName)
	{
		if( $arrRow =& $this->localeRow($sName,$this->arrData) )
		{
			return $arrRow[$sName] ;
		}
		else 
		{
			return null ;
		}
	}
	public function setData($sName,$value)
	{
		if( $arrRow =& $this->localeRow($sName,$this->arrData) )
		{
			return $arrRow[$sName] = $value ;
		}
	}
	private function & rowRef($sChildName=null)
	{
		if($sChildName===null)
		{
			return $this->currentRow($this->arrData) ;
		}
		else
		{
			if( $arrRow=&$this->localeRow($sChildName,$this->arrData) )
			{
				if( $this->isSheet($arrRow,$sChildName) )
				{
					return $this->currentRow($arrRow[$sName]) ;
				}
			}
			return self::$null ;
		}
	}
	public function row($sChildName=null)
	{
		return $this->rowRef($sChildName) ;
	}
	public function setRow($arrDatas,$sChildName=null)
	{		
		if($arrRow=&$this->rowRef($sChildName))
		{
			foreach($arrDatas as $key=>&$value)
			{
				$arrRow[$key] = $value ;
			}
		}
	}
	
	private function & currentRow(array & $arrSheet)
	{
		if(empty($arrSheet))
		{
			return self::$null ;
		}

		$nRow = key($arrSheet) ;
		
		if( !is_array($arrSheet[$nRow]) )
		{
			return self::$null ;
		}
		
		return $arrSheet[$nRow] ;
	}
	
	private function & localeRow($sName,array & $arrSheet,$pos=-1)
	{
		if( !$arrRow=&$this->currentRow($arrSheet) )
		{
			return self::$null ;
		}
		
		if( array_key_exists($sName,$arrRow) )
		{
			return $arrRow ;
		}
		else
		{
			while( ($pos=strpos($sName,'.',$pos+1))!==false )
			{
				$sSubName = substr($sName,0,$pos) ;
				if( $this->isSheet($arrRow,$sSubName) )
				{
					return $this->localeRow($sName,$arrRow[$sSubName],$pos) ;
				} 
			}
			return self::$null ;
		}
	}
	
	private function isSheet(array & $arrRow,$sDataName)
	{
		return array_key_exists($sDataName,$arrRow) and !empty($arrRow[$sDataName.chr(0).'sheet']) ;
	}
	
	const ignore = '~-+ignore this arg+-~' ;
	const primaryKeys = '~-+use primary keys+-~' ;
	const asWhereClause = true ;
		
	
	private $aPrototype ;
	
	private $aDB ;

	private $arrData = array() ;
	private $sDataPrefix ;
	private $nDataPrefixLength ;
	
	static private $null = null ;
}