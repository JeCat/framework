<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\lang\Type;

use org\jecat\framework\db\sql\StatementState;
use org\jecat\framework\db\sql\Statement;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\db\sql\name\NameTransferFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\db\sql\TablesJoin;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\orm\Prototype;

class Association implements IBean
{
	const youKnow = null ;
	
	const hasOne = 1;
	const belongsTo = 2;
	const hasMany = 4;
	const hasAndBelongsToMany = 8;
	
	const oneToOne = 3 ;		// 一对一关联

	const pair = 7 ;			// 两表关联		
	const triplet = 8 ;		// 三表关联
	
	const total = 15 ;		// 所有
	
	// public function
	public function __construct(DB $aDB=null,$nType=null,$aFromPrototype=null,$aToPrototype=null,$fromKeys=self::youKnow,$toKeys=self::youKnow,$sBridgeTable=self::youKnow,$toBridgeKeys=self::youKnow,$fromBridgeKeys=self::youKnow)
	{
		$this->aDB = $aDB ;
		$this->nType=$nType;
		$this->aFromPrototype = $aFromPrototype;
		$this->aToPrototype = $aToPrototype;
		
		$this->setFromKeys($fromKeys);
		$this->setToKeys($toKeys);
		
		if( $nType===self::hasAndBelongsToMany )
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
		$this->arrFromKeys = Type::toArray($fromKeys) ;
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
		if($this->nType != self::hasAndBelongsToMany)
		{
			throw new ORMException('函数 Association::setBridge() 只有在 nType 是 hasAndBelongsToMany时才可以被调用');
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
		return $this->toPrototype()->sqlTableAlias().'#bridge' ;
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
		if(!$this->nType)
		{
			throw new ORMException('尚未指定 ORM 关联(%s)的类型',$this->path()) ;
		}
		if(!$this->aFromPrototype)
		{
			throw new ORMException('尚未指定 ORM 关联(%s)的 fromPrototype',$this->path()) ;
		}
		
		// 
		if( $this->nType==self::hasAndBelongsToMany )
		{
			if(!$this->toBridgeKeys())
			{
				throw new ORMException('无法确定 ORM 关联(%s)的桥接表上的外键 toBridgeKeys ：没有指定作为外键的字段，桥接表上也没有和另一端外键相同的字段',$this->path()) ;
			}
			if(!$this->fromBridgeKeys())
			{
				throw new ORMException('无法确定 ORM 关联(%s)的桥接表上的外键 fromBridgeKeys ：没有指定作为外键的字段，桥接表上也没有和另一端外键相同的字段',$this->path()) ;
			}
		
			if( count($this->fromKeys())!==count($this->toBridgeKeys()) )
			{
				throw new ORMException( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromKeys:%s <-> toBridgeKeys:%s", array(
							$this->path()
							, implode(',',$this->fromKeys())
							, implode(',',$this->toKeys())
						) ) ;
			}
		
			if( count($this->fromBridgeKeys())!==count($this->toKeys()) )
			{
				throw new ORMException( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromBridgeKeys:%s <-> toKeys:%s", array(
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
				throw new ORMException( "ORM关联(%s)两端的外键无效，字段数量必须对等; fromKeys:%s <-> toKeys:%s", array(
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
	 * @return org\jecat\framework\db\sql\TablesJoin
	 */
	public function sqlTablesJoin()
	{
		return $this->aTablesJoin ;
	}
	public function setSqlTablesJoin(TablesJoin $aTablesJoin)
	{
		$this->aTablesJoin = $aTablesJoin ;
	}
	
	// implements IBean
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /mvc/模型/关系/Bean配置数组
	 *
	 * type string 指定关系类型
	 * fromkeys array 起始表列名
	 * tokeys array 目标表列名
	 * frombridgekeys array 起始桥接表列名
	 * tobridgekeys array 起始桥接表列名
	 * bridge string 桥接表名
	 * fromPrototype string 指定起源原型配置
	 * on string 
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !$this->aDB )
		{
			$this->aDB = DB::singleton() ;
		}
		
		$arrConfigForToPrototype = $arrConfig ;
		$arrConfigForToPrototype['class'] = 'prototype' ;
		
		$this->aToPrototype = BeanFactory::singleton()->createBean($arrConfigForToPrototype,$sNamespace) ;
		$this->aToPrototype->setAssociationBy($this) ;
	
		if(!empty($arrConfig['type']))
		{
			$this->nType = $arrConfig['type'] ;
		}
		if(!empty($arrConfig['fromkeys']))
		{
			$this->setFromKeys($arrConfig['fromkeys']) ;
		}
		if(!empty($arrConfig['tokeys']))
		{
			$this->setToKeys($arrConfig['tokeys']) ;
		}
		if(!empty($arrConfig['tobridgekeys']))
		{
			$this->setToBridgeKeys($arrConfig['tobridgekeys']) ;
		}
		if(!empty($arrConfig['frombridgekeys']))
		{
			$this->setFromBridgeKeys($arrConfig['frombridgekeys']) ;
		}
		if(!empty($arrConfig['bridge']))
		{
			$this->sBridgeTable = $arrConfig['bridge'] ;
		}
		if(!empty($arrConfig['fromPrototype']))
		{
			$this->aFromPrototype = $arrConfig['fromPrototype'] ;
		}
		if(!empty($arrConfig['on']))
		{
			$arrConfig['on'] = (array)$arrConfig['on'] ;
			foreach($arrConfig['on'] as &$items)
			{
				// 桥接表区分是否 Bridge 上的条件
				if( $this->isType(self::hasAndBelongsToMany) and strpos($items[1],'to.')===0 )
				{
					$aRestriction = $this->otherBridgeTableJoinOn() ;
				}
				else
				{
					$aRestriction = $this->otherTableJoinOn() ;
				}
				$sMethod = array_shift($items) ;
				call_user_func_array(array($aRestriction,$sMethod),$items) ;
			}
		}
		
		$this->done() ;
			
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	public function setDB(DB $aDB)
	{
		$this->aDB = $aDB ;
	}
	
	public function on()
	{
		return $this->aTablesJoin? $this->aTablesJoin->on(): null ;
	}
	
	
	// --------------
	public function otherTableJoinOn($bAutoCreate=true)
	{
		if( !$this->aOtherTableJoinOn and $bAutoCreate )
		{
			$this->aOtherTableJoinOn = $this->createJoinOn() ;
		}
		return $this->aOtherTableJoinOn ;
	}
	public function otherBridgeTableJoinOn($bAutoCreate=true)
	{
		if( !$this->aOtherTableJoinOn and $bAutoCreate )
		{
			$this->aOtherTableJoinOn = $this->createJoinOn() ;
		}
		return $this->aOtherTableJoinOn ;
	}
	private function createJoinOn()
	{
		$aTableJoinOn = new Restriction() ;
		
		$aStatementNameTransfer = NameTransferFactory::singleton()->create() ;
		$aStatementNameTransfer->addColumnNameHandle(array($this,'statementColumnNameHandle')) ;
		
		$aTableJoinOn->setNameTransfer($aStatementNameTransfer) ;
		
		return $aTableJoinOn ;
	}
	public function statementColumnNameHandle($sName,Statement $aStatement,StatementState $sState)
	{
		// 切分 原型名称 和 字段名称
		$nPos = strrpos($sName,'.') ;
		if($nPos===false)
		{
			throw new ORMException("ORM 关联的Join On条件，必须指出字段所属的表(from,to,bridge)") ;	
		}
		
		$sColumnName = substr($sName,$nPos+1) ;
		
		switch(substr($sName,0,$nPos))
		{
			case 'from' :
				$aPrototype = $this->fromPrototype() ;
				break ;
			case 'to' :
				$aPrototype = $this->toPrototype() ;
				break ;
			case 'bridge' :
				return '`'.$this->bridgeSqlTableAlias()."`.`{$sColumnName}`" ;
			default :
				throw new ORMException("ORM 关联的Join On条件，必须使用：from,to,bridge 表示对应的表名; 传入的字段名为：%s",$sName) ;	
		}
		
		return array(
			'`'.$aPrototype->sqlTableAlias().'`.`'.($aPrototype->getColumnByAlias($sColumnName)?:$sColumnName).'`'
			, $aStatement, $sState
		) ;
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
	
	private $arrBeanConfig ;
	
	private $aOtherTableJoinOn ;
	
}
?>
