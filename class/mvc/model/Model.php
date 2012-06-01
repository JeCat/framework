<?php
namespace org\jecat\framework\mvc\model ;

use org\jecat\framework\mvc\model\executor\Deleter;
use org\jecat\framework\mvc\model\executor\Updater;
use org\jecat\framework\mvc\model\executor\Inserter;
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
	 * new model
	 * @param unknown_type $sTable
	 * @param unknown_type $sPrototypeName
	 * @param unknown_type $primaryKeys
	 * @param unknown_type $columns
	 */
	public static function Create($sTable,$sPrototypeName=null,$primaryKeys=null,$columns=null)
	{
	    return new self($sTable,$sPrototypeName,$primaryKeys,$columns) ;
	}
	
	/**
	 * @alias org\jecat\framework\mvc\model\Prototype::addOrder
	 * @return Model
	 */
	public function order($columns,$bDesc=true,$sTable=NULL)
	{
		$this->aPrototype->addOrder($columns,$bDesc,$sTable) ;
		return $this ;
	}
	/**
	 * 设置 limit
	 * @return Model
	 */
	public function limit($nLen,$pos=null,$sTable=NULL)
	{
		$this->aPrototype->setLimit($nLen,$pos,$sTable) ;
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
	public function where($sWhere,$sTable=NULL)
	{
		$this->aPrototype->addWhere($sWhere,$sTable) ;
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
	public function hasOne($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null,$sFromPrototype='$')
	{
		$this->aPrototype->addAssociation(array(
				'type' => Prototype::hasOne ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		),$sFromPrototype) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function belongsTo($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null,$sFromPrototype='$')
	{
		$this->aPrototype->addAssociation(array(
				'type' => Prototype::belongsTo ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		),$sFromPrototype) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function hasMany($toTable,$fromKeys=null,$toKeys=null,$sAssocName=null,$sFromPrototype='$')
	{
		$this->aPrototype->addAssociation(array(
				'type' => Prototype::hasMany ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
		),$sFromPrototype) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function hasAndBelongsToMany($toTable,$sBridgeTableName,$fromKeys=null,$toBridgeKeys=null,$fromBridgeKeys=null,$toKeys=null,$sAssocName=null,$sFromPrototype='$')
	{
		$this->aPrototype->addAssociation(array(
				'type' => Prototype::hasAndBelongsToMany ,
				'table' => $toTable ,
				'name' => $sAssocName ,
				'fromKeys' => $fromKeys ,
				'toKeys' => $toKeys ,
				'bridge' => $sBridgeTableName ,
				'toBridgeKeys' => $toBridgeKeys ,
				'fromBridgeKeys' => $fromBridgeKeys ,
		),$sFromPrototype) ;
		return $this ;
	}
	/**
	 * @return Model
	 */
	public function ass(array $arrOptions,$sFromPrototype='$')
	{
		$this->aPrototype->addAssociation($arrOptions,$sFromPrototype) ;
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
		
		Selecter::singleton()->execute( $this, $this->aPrototype->refRaw(), $this->arrData, $sTmpWhere, $this->db() ) ;

		//echo "<pre>";print_r($this->arrData);echo "</pre>";
		
		return $this ;
	}

	public function insert(array $arrData=null,$sChildName=null)
	{
		$aInserter = Inserter::singleton() ;
		$arrPrototype =& $this->aPrototype->refRaw($sChildName?:'$') ;
		
		
		$bRecursively = $sChildName? false: true ;

		// insert 整个 list
		if($arrData)
		{
			reset($arrData) ;
			if( is_int(key($arrData)) )
			{
				$aInserter->execute( $this, $arrPrototype, $arrData, $bRecursively, $this->db() ) ;
				return $this ;
			}
		}

		// insert 单行
		if( empty($arrData) )
		{
			$arrData =& $this->rowRef($sChildName) ;
		}
		$aInserter->insertRow( $this, $arrPrototype, $arrData, $bRecursively, $this->db() ) ;

		return $this ;
	}
	
	public function update(array $arrData=null,$sWhere=null,$sChildName=null)
	{
		if($arrData===null)
		{
			if( $sChildName )
			{
				if( !$arrParentRow=&$this->localeRow($sChildName) or empty($arrParentRow[$sChildName]) )
				{
					return ;
				}
				$arrData =& $arrParentRow[$sChildName] ;
			}
			else
			{
				if( !$arrData=&$this->currentRow($this->arrData) )
				{
					return ;
				}
			}
		}
		if( !$arrPrototype=&$this->aPrototype->refRaw($sChildName?:'$') )
		{
			throw new Exception("传入 Model::update 方法的参数\$sChildName无效:%s",$sChildName) ;
		}
		
		
		Updater::singleton()->execute( $this, $arrPrototype, $arrData, $sWhere, $this->db() ) ;
	}
	
	/**
	 * 执行删除操作，
	 * 仅仅删除数据库中的记录，Model对像中的数据仍然保留，并且可以在 delete() 以后立即执行 save()
	 * @return Model
	 */
	public function delete($sWhere=null , $sOrder=null ,$sLimit=null,$sChildName=null)
	{
		if( !$arrPrototype=&$this->aPrototype->refRaw($sChildName?:'$') )
		{
			throw new Exception("传入 Model::update 方法的参数\$sChildName无效:%s",$sChildName) ;
		}
		
		Deleter::singleton()->execute( $arrPrototype, $sWhere ,$sOrder , $sLimit, $this->db() ) ;
	}
	
	public function aff($sChildName=null)
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

	public function switchPrototype($sPrototypeName='$')
	{
		$this->aPrototype->switchPrototype($sPrototypeName) ;
		return $this ;
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
					if( each($arrParentRow[$sChildName])===false )
					{
						next($arrParentRow[$sChildName]) ;
						return true ;
					}
					else
					{
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
		if( $arrParentRow=&$this->localeRow($sChildName,$this->arrData) )
		{
			if( $this->isSheet($arrParentRow,$sChildName) )
			{
				if( !empty($arrParentRow[$sChildName]) )
				{
					next($arrParentRow[$sChildName]) ;
					if( each($arrParentRow[$sChildName])===false )
					{
						prev($arrParentRow[$sChildName]) ;
						return false ;
					}
					else
					{
						return true ;
					}
				}
			}
		}
		return false ;
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
	private function & rowRef($sChildName=null,$bCreateRowIfNotExists=false)
	{
		if($sChildName===null)
		{
			return $this->currentRow($this->arrData,$bCreateRowIfNotExists) ;
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
		$arrSheet =& $this->buildSheet($sChildName) ;
		$arrRow =& $this->currentRow($arrSheet,true) ;

		foreach($arrDatas as $key=>&$value)
		{
			$arrRow[$key] = $value ;
		}
	}
	public function addRow($arrRow=null,$sChildName=null)
	{
		// 针对主表
		if( $sChildName===null or $sChildName==='$' )
		{
			$this->arrData[] = $arrRow ?: array() ;
			end($this->arrData);
		}
		// 指定表
		else
		{
			$arrSheet =& $this->buildSheet($sChildName) ;
			$arrSheet[] = array() ;
			end($arrSheet) ;
		}
	}
	public function rowNum($sChildName=null)
	{
		if($sChildName===null)
		{
			return count($this->arrData) ;
		}
		else if( $arrParentRow=&$this->localeRow($sChildName,$this->arrData) and $this->isSheet($arrParentRow,$sChildName) )
		{
			return count($arrParentRow[$sChildName]) ;
		}
		else
		{
			return -1 ;
		}
	}
	
	public function & buildSheet($sChildName)
	{
		if($sChildName===null)
		{
			return $this->arrData ;
		}
		
		$arrSheet =& $this->arrData ;
		$sXPath = '' ;
		
		foreach( explode('.',$sChildName) as $sName )
		{
			$sXPath.= ($sXPath?'.':'') . $sName ;
			$arrPrototype =& $this->aPrototype->refRaw($sXPath) ;
		
			// 多属关联，建立并切换到下级表
			if( !($arrPrototype['type']&Prototype::oneToOne) )
			{
				$arrSheet =& $this->makeSheet(
						$this->currentRow($arrSheet,true) // 上级表的当前行（没有则建立一行做为当前行）
						, $sXPath
				) ;
			}
		}
		return $arrSheet ;
	}
	
	private function & makeSheet(array & $arrParentRow,$sXPath)
	{
		if( !isset($arrParentRow[$sXPath]) or !is_array($arrParentRow[$sXPath]) )
		{
			$arrParentRow[$sXPath] = array() ;
			$arrParentRow[$sXPath.chr(0).'sheet'] = true ;
		}
		
		return $arrParentRow[$sXPath] ;
	}
	
	private function & currentRow(array & $arrSheet,$bCreateRowIfNotExists=false)
	{
		if(empty($arrSheet))
		{
			if($bCreateRowIfNotExists)
			{
				$arrSheet[] = array() ;
			}
			else
			{
				return self::$null ;
			}
		}

		$nRow = key($arrSheet) ;
		
		if(empty($arrSheet[$nRow])) $arrSheet[$nRow] = array();
		if(!is_array($arrSheet[$nRow]) )
		{
			if($bCreateRowIfNotExists)
			{
				$arrSheet[$nRow] = array() ;
			}
			else
			{
				return self::$null ;
			}
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
	
	public function isSheet(array & $arrRow,$sDataName)
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
	
	private $arrLastAffecteds = array() ;
	
	static private $null = null ;
}