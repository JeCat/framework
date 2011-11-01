<?php

namespace jc\mvc\model\db\orm;

use jc\db\sql\TablesJoin;

use jc\db\DB;
use jc\lang\Exception;
use jc\mvc\model\db\orm\Prototype;

class Association
{
	const youKnow = null ;
	
	const hasOne = 1;
	const belongsTo = 2;
	const hasMany = 4;
	const hasAndBelongsTo = 8;
	
	const oneToOne = 3 ;		// 一对一关联

	const pair = 7 ;			// 两表关联		
	const triplet = 8 ;		// 三表关联
	
	const total = 15 ;		// 所有
	
	// public function
	public function __construct(DB $aDB,$nType,$aFromPrototype,$aToPrototype,$fromKeys=self::youKnow,$toKeys=self::youKnow,$sBridgeTable=self::youKnow,$toBridgeKeys=self::youKnow,$fromBridgeKeys=self::youKnow)
	{
		$this->aDB = $aDB ;
		$this->nType=$nType;
		$this->aFromPrototype = $aFromPrototype;
		$this->aToPrototype = $aToPrototype;
		
		$this->setFromKeys($fromKeys);
		$this->setToKeys($toKeys);
		
		if( $nType===self::hasAndBelongsTo )
		{
			$this->setBridge($sBridgeTable,$toBridgeKeys,$fromBridgeKeys);
		}
	}
	
	public function fromKeys()
	{
		if( !$this->arrFromKeys )
		{
			$this->arrFromKeys = $this->fromPrototype()->keys() ;
		}
		
		return $this->arrFromKeys ;
	}
	public function setFromKeys($fromKeys)
	{
		$this->arrFromKeys = $fromKeys? (array) $fromKeys: array() ;
	}
	
	public function toKeys()
	{
		if( !$this->arrToKeys )
		{
			$this->arrToKeys = $this->toPrototype()->keys() ;
		}
		
		return $this->arrToKeys ;
	}
	public function setToKeys($toKeys)
	{
		$this->arrToKeys = $toKeys? (array) $toKeys: array() ;
	}
	
	/**
	 *  设置连桥表的原型、左连键和右连键
	 *
	 *  $BridgeTable，连桥表。接受字符串或Prototype对象，表示表名或原型。
	 *  $toBridgeKeys 和 $fromBridgeKeys 接受字符串或数组。
	 *
	 *  setToBridgeKeys() , setFromBridgeKeys() 
	 */
	public function setBridge($sBridgeTable,$toBridgeKeys,$fromBridgeKeys)
	{
		if($this->nType != self::hasAndBelongsTo)
		{
			throw new Exception('函数 Association::setBridge() 只有在 nType 是 hasAndBelongsTo时才可以被调用');
		}
		
		$this->sBridgeTable = $sBridgeTable ;
		
		$this->setToBridgeKeys($toBridgeKeys) ;
		$this->setFromBridgeKeys($fromBridgeKeys) ;
	}

	public function bridgeTableName()
	{
		return $this->sBridgeTable ;
	}

	public function bridgeSqlTableAlias()
	{
		return $this->sBridgeTable ;
	}

	public function toBridgeKeys()
	{
		if(!$this->arrToBridgeKeys)
		{
			$arrFromKeys = $this->fromKeys() ;
			$arrBridgeColumns = $this->aDB->reflecterFactory()->tableReflecter($this->sBridgeTable)->columns() ;

			if( array_intersect($arrFromKeys,$arrBridgeColumns)==$arrFromKeys )
			{
				$this->arrToBridgeKeys = $arrFromKeys ;
			}
		}
		
		return $this->arrToBridgeKeys ;
	}
	public function setToBridgeKeys($toBridgeKeys)
	{
		$this->arrToBridgeKeys = $toBridgeKeys? (array) $toBridgeKeys: array() ;
	}
	
	public function fromBridgeKeys()
	{
		if(!$this->arrFromBridgeKeys)
		{
			$arrToKeys = $this->toKeys() ;
			$arrBridgeColumns = $this->aDB->reflecterFactory()->tableReflecter($this->sBridgeTable)->columns() ;

			if( array_intersect($arrToKeys,$arrBridgeColumns)==$arrToKeys )
			{
				$this->arrFromBridgeKeys = $arrToKeys ;
			}
		}
		
		return $this->arrFromBridgeKeys;
	}
	public function setFromBridgeKeys($fromBridgeKeys)
	{
		$this->arrFromBridgeKeys = $fromBridgeKeys? (array) $fromBridgeKeys: array() ;
	}

	public function isType($nType)
	{		
		return ($nType & $this->nType) ? true: false ;
	}
	
	/**
	 * @return Prototype
	 */
	public function fromPrototype()
	{
		return $this->aFromPrototype;
	}
	/**
	 * @return Prototype
	 */
	public function toPrototype()
	{
		return $this->aToPrototype;
	}
	
	public function type()
	{
		return $this->nType;
	}
		
	public function done()
	{
		// 
		if( $this->nType==self::hasAndBelongsTo )
		{
			if(!$this->toBridgeKeys())
			{
				throw new Exception('无法确定 ORM 关联(%s)的桥接表上的外键 toBridgeKeys ：没有指定作为外键的字段，桥接表上也没有和另一端外键相同的字段',$this->path()) ;
			}
			if(!$this->fromBridgeKeys())
			{
				throw new Exception('无法确定 ORM 关联(%s)的桥接表上的外键 fromBridgeKeys ：没有指定作为外键的字段，桥接表上也没有和另一端外键相同的字段',$this->path()) ;
			}
		
			if( count($this->fromKeys())!==count($this->toBridgeKeys()) )
			{
				throw new Exception( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromKeys:%s <-> toBridgeKeys:%s", array(
							$this->path()
							, implode(',',$this->fromKeys())
							, implode(',',$this->toKeys())
						) ) ;
			}
		
			if( count($this->fromBridgeKeys())!==count($this->toKeys()) )
			{
				throw new Exception( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromBridgeKeys:%s <-> toKeys:%s", array(
							$this->path()
							, implode(',',$this->fromKeys())
							, implode(',',$this->toKeys())
						) ) ;
			}
			
		}
		
		// 
		else 
		{
			if( count($this->fromKeys())!==count($this->toKeys()) )
			{
				throw new Exception( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromKeys:%s <-> toKeys:%s", array(
							$this->path()
							, implode(',',$this->fromKeys())
							, implode(',',$this->toKeys())
						) ) ;
			}
		}
	}
	
	public function name()
	{
		return $this->aToPrototype->name() ;
	}
	
	public function path($bFull=true)
	{
		return $this->aToPrototype->path($bFull) ;
	}

	/**
	 * @return jc\db\sql\TablesJoin
	 */
	public function sqlTablesJoin()
	{
		return $this->aTablesJoin ;
	}
	public function setSqlTablesJoin(TablesJoin $aTablesJoin)
	{
		$this->aTablesJoin = $aTablesJoin ;
	}
	
	// private data
	private $aDB = null ;
	private $aFromPrototype = null ;
	private $aToPrototype = null ;
	private $nType = 0 ;
	private $arrFromKeys = array();
	private $arrToKeys = array();
	private $sBridgeTable;
	private $arrToBridgeKeys = array();
	private $arrFromBridgeKeys = array();
	private $aTablesJoin = null ;
	
}
?>
