<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\util\serialize\IIncompleteSerializable;
use org\jecat\framework\util\serialize\ShareObjectSerializer;
use org\jecat\framework\lang\Type;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\orm\Prototype;

class Association implements IBean, \Serializable, IIncompleteSerializable
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
	public function setFromPrototype(Prototype $aPrototype)
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
		if(!$this->aToPrototype)
		{
			throw new ORMException('尚未指定 ORM 关联(%s)的 toPrototype',$this->path()) ;
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
	/*public function sqlTablesJoin()
	{
		return $this->aTablesJoin ;
	}
	public function setSqlTablesJoin(TablesJoin $aTablesJoin)
	{
		$this->aTablesJoin = $aTablesJoin ;
	}*/
	
	public function joinType()
	{
		return $this->sJoinType ;
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
	 * @wiki /MVC模式/数据库模型/数据表关联
	 * ==Bean配置数组==
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
		$this->aToPrototype->setassociatedBy($this) ;
	
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
			if( is_array($arrConfig['on']) )
			{
				$arrOnFactors = $arrConfig['on'] ;
				$this->arrOnRawSql['expr_type'] = 'clause_on' ;
				$this->arrOnRawSql['subtree'] =& SQL::parseSql(array_shift($arrOnFactors),'on',true) ;
				$aOnClause = new SQL($this->arrOnRawSql) ;
				$aOnClause->addFactors($arrOnFactors) ;
			}
			else
			{
				$this->arrOnRawSql['expr_type'] = 'clause_on' ;		
				$this->arrOnRawSql['subtree'] =& SQL::parseSql($arrConfig['on'],'on',true) ;
			}
		}
		if(!empty($arrConfig['join']))
		{
			$this->sJoinType = strtoupper($arrConfig['join']) ;
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
	
	public function & joinOnRawSql()
	{
		return $this->arrOnRawSql ;
	}
	// ----------------------------------
	public function serializableProperties()
	{
		return array(
			__CLASS__ => array(
				'nType' ,
				'arrFromKeys' ,
				'arrToKeys' ,
				'sBridgeTable' ,
				'arrToBridgeKeys' ,
				'arrFromBridgeKeys' ,
				'arrOnRawSql' ,
				'sJoinType' ,
				'arrBeanConfig' ,
				'aToPrototype' ,
				'aFromPrototype' ,
			)
		) ;
	}
	public function serialize ()
	{
		return ShareObjectSerializer::singleton()->serialize($this) ;
	}
	public function unserialize ($serialized)
	{
		ShareObjectSerializer::singleton()->unserialize($serialized,$this) ;
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
	private $arrOnRawSql ;
	//private $aTablesJoin = null ;
	private $sJoinType = 'LEFT' ;
	
	private $arrBeanConfig ;
	
}


