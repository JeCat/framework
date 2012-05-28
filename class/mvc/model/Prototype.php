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
namespace org\jecat\framework\mvc\model ;

use org\jecat\framework\util\EventManager;

use org\jecat\framework\db\sql\compiler\SqlNameCompiler;
use org\jecat\framework\db\sql\compiler\SqlCompiler;
use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\db\sql\Criteria;
use org\jecat\framework\db\sql\Update;
use org\jecat\framework\lang\Object;
use org\jecat\framework\mvc\model\db\ModelList;
use org\jecat\framework\db\sql\Order;
use org\jecat\framework\util\serialize\IIncompleteSerializable;
use org\jecat\framework\util\serialize\ShareObjectSerializer;
use org\jecat\framework\lang\Type;
use org\jecat\framework\mvc\model\db\orm\Association;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\DB;

/**
 * 
 * @author qusong
 * @wiki /MVC模式/数据库模型/数据表原型
 * ==数据表原型(ProtoType)==
 * 
 * 蜂巢充分的利用面向对象的思想，这里原型就是将数据表抽象为对象，对数据表进行封装，用户可以直接通过对原型对象的操作，来对数据进行操作。
 * 所有的Model的创建方法的实现都是原型创建出来的，原型是和数据表结合最近的环节。
 *
 */
class Prototype
{
	const hasOne = 1;
	const belongsTo = 2;
	const hasMany = 4;
	const hasAndBelongsToMany = 8;
	
	const oneToOne = 3 ;			// 一对一关联

	const pair = 7 ;				// 两表关联		
	const triplet = 8 ;				// 三表关联
	
	const total = 15 ;				// 所有
	
	const transTable = 'transTable' ;

	static private $arrAssociations = array(
		self::hasOne, self::belongsTo, self::hasMany, self::hasAndBelongsToMany
	) ;
	
	
	public function __construct($sTableName,$sPrototypeName=null,$primaryKeys=null,$columns='*',$aDB=null)
	{
		EventManager::singleton()->emitEvent(__CLASS__,self::transTable,$arrArgv=array(&$sTableName,&$sPrototypeName)) ;
		
		$this->arrPrototype['name'] = $sPrototypeName ;
		$this->arrPrototype['xpath'] = null ;
		$this->arrPrototype['table'] = $sTableName ;
		$this->arrPrototype['tableAlias'] = $sPrototypeName ;
		$this->arrPrototype['columns'] = $columns ;
		$this->arrPrototype['key'] = $primaryKeys ;
		$this->aDB = $aDB?: DB::singleton() ;

		self::reflectTable($this->arrPrototype,$this->aDB) ;
		
		$this->arrPrototypeShortcut[ $this->arrPrototype['name'] ] =& $this->arrPrototype ;
		$this->arrPrototypeShortcut['$'] =& $this->arrPrototype ;
	}
	
	// getter and setter
	
	public function name()
	{
		return $this->arrPrototype['name'] ;
	}
	/**
	 * @return Prototype
	 */
	public function setName($sName)
	{
		$this->arrPrototype['name'] = $sName;
		return $this;
	}
	public function xpath()
	{
		return $this->arrPrototype['xpath'] ;
	}
	
	public function keys()
	{
		return $this->arrPrototype['keys'];
	}
	/**
	 *   键可以为多个。本函数接受一个数组（多个键）或一个字符串（一个键）。
	 * @return Prototype
	 */
	public function setKeys( $keys )
	{
		$this->arrPrototype['keys'] = (array)$keys ;
		return $this;
	}
	/**
	 * 数据表定义的主键
	 */
	public function devicePrimaryKey()
	{
		if( $this->arrPrototype['devicePrimaryKey']===null )
		{
			if( !$this->arrPrototype['devicePrimaryKey'] = $this->tableReflecter()->primaryName() )
			{
				$this->arrPrototype['devicePrimaryKey'] = '' ;
			}
		}
		return $this->arrPrototype['devicePrimaryKey'] ?: null ;
	}
	public function tableName()
	{
		return $this->arrPrototype['table'];
	}
	/**
	 * @return Prototype
	 */
	public function setTableName($sTableName)
	{
		$this->arrPrototype['table'] = $sTableName;
		return $this;
	}
	public function tableAlias()
	{
		return $this->arrPrototype['tableAlias'];
	}
	
	// columns
	public function columns()
	{
		return $this->arrPrototype['columns'] ;
	}
	/**
	 *  本函数接受一个数组（多个列）或一个字符串（一个列）。
	 * @return Prototype
	 */
	public function addColumns($columnNames)
	{
		if( !isset($this->arrPrototype['columns']) or $this->arrPrototype['columns']=='*' )
		{
			$this->arrPrototype['columns'] = array() ;
		}
		
		foreach(Type::toArray($columnNames) as $sColumnName)
		{
			if( $sColumnName and !in_array($sColumnName,$this->arrPrototype['columns']) )
			{
				$this->arrPrototype['columns'][] = $sColumnName ;
			}
		}
		
		return $this;
	}
	/**
	 * @return Prototype
	 */
	public function removeColumn($sColumn)
	{
		$key = array_search($sColumn,$this->arrPrototype['columns']) ;
		
		if($key!==false)
		{
			unset($this->arrPrototype['columns'][$key]);
		}
		
		return $this;
	}
	/**
	 * @return Prototype
	 */
	public function clearColumns()
	{
		$this->arrPrototype['columns']=array() ;
		return $this ;
	}
	public function columnIterator()
	{
		return new \ArrayIterator($this->arrPrototype['columns']) ;
	}
	
	
	public function forceIndex()
	{
		return $this->arrPrototype['forceIndex'] ;
	}
	public function setForceIndex($sForceIndex)
	{
		return $this->arrPrototype['forceIndex'] = $sForceIndex ;
	}

	/**
	 * 设置一个或多个 order by 字段
	 *
	 * $columns 可以用以下类型：
	 * $columns = 'column' ;
	 * $columns = array(
	 * 		'column_a' ,
	 * 		'column_b' => false , // asc
	 * 		'column_c' => true ,  // desc
	 * ) ;
	 * @param $columns	string,array	可以字符串类型表示一个字段，或数组类型表示多个字段；当传入数组类型时，可以用数组的键名表示字段名，值以bool类型表示是否为 desc 顺序，也可以只提供字符串类型元素值表示字段名
	 * @return Prototype
	 */
	public function addOrder($columns,$bDesc=true)
	{
		if( is_string($columns) )
		{
			unset($this->arrPrototype['orderBy'][$columns]) ;
			$this->arrPrototype['orderBy'][$columns] = $bDesc ;
		}
		else if( is_array($columns) )
		{
			foreach($columns as $key=>&$item)
			{
				if( is_int($key) )
				{
					unset($this->arrPrototype['orderBy'][$item]) ;
					$this->arrPrototype['orderBy'][$item] = $bDesc ;
				}
				else
				{
					unset($this->arrPrototype['orderBy'][$key]) ;
					$this->arrPrototype['orderBy'][$key] = $item? true: false ;
				}
			}
		}
		else
		{
			throw new Exception("Prototype::addOrder() 传入的参数 \$columns 类型错误，必须为 string 或 array 类型。") ;
		}
		return $this ;
	}
	public function rawOrder()
	{
		return isset($this->arrPrototype['orderBy'])? $this->arrPrototype['orderBy']: null ;
	}
	/**
	 * 设置一个或多个 group by 字段
	 * @return Prototype
	 */
	public function addGroup($columns)
	{
		if( is_string($columns) )
		{
			$this->arrPrototype['groupBy'][] = $columns ;
		}
		else if( is_array($columns) )
		{
			foreach($columns as &$sColumn)
			{
				$this->arrPrototype['groupBy'][] = $sColumn ;
			}
		}
		else
		{
			throw new Exception("Prototype::addGroup() 传入的参数 \$columns 类型错误，必须为 string 或 array 类型。") ;
		}
		return $this ;
	}
	public function rawGroup()
	{
		return isset($this->arrPrototype['groupBy'])? $this->arrPrototype['groupBy']: null ;
	}
	/**
	 * 设置一组 where 条件
	 * @return Prototype
	 */
	public function addWhere($where)
	{
		$this->arrPrototype['where'][] = $where ;
		return $this ;
	}
	public function rawWhere()
	{
		return isset($this->arrPrototype['where'])? $this->arrPrototype['where']: null ;
	}
	
	/**
	 * @return Prototype
	 */
	public function setLimit($nLen,$from=null)
	{
		$this->arrPrototype['limitLen'] = $nLen ;
		$this->arrPrototype['limitFrom'] = $from ;
		return $this ;
	}
	
	public function limitLength()
	{
		return isset($this->arrPrototype['limitLen'])? $this->arrPrototype['limitLen']: null ;
	}
	public function limitFrom()
	{
		return isset($this->arrPrototype['limitFrom'])? $this->arrPrototype['limitFrom']: null ;
	}
	
	public function db()
	{
		if(!$this->aDB)
		{
			$this->aDB = DB::singleton() ;
		}
		return $this->aDB ;
	}
	/**
	 * @return org\jecat\framework\db\reflecter\AbStractTableReflecter
	 */
	static public function tableReflecter(array & $arrPrototype,DB $aDB)
	{
		$aTableReflecter = $aDB->reflecterFactory()->tableReflecter($arrPrototype['table']) ;
	
		if( !$aTableReflecter->isExist() )
		{
			throw new Exception('ORM原型(%s)的数据表表名无效：%s',array($arrPrototype['xpath'],$arrPrototype['table'])) ;
		}
	
		return $aTableReflecter ;
	}
	
	/**
	 * @return org\jecat\framework\db\reflecter\AbStractColumnReflecter
	 */
	static public function columnReflecter(array & $arrPrototype,$sColumn,DB $aDB)
	{
		$sColumn = $this->getColumnByAlias($sColumn)?: $sColumn ;
	
		$aColumnReflecter = $aDB->reflecterFactory()->columnReflecter($arrPrototype['table'],$sColumn) ;
	
		if( !$aColumnReflecter->isExist() )
		{
			throw new Exception('ORM原型(%s)的数据表字段无效：%s.%s',array($arrPrototype['xpath'],$arrPrototype['table'],$sColumn)) ;
		}
	
		return $aColumnReflecter ;
	}

	static private function reflectTable(& $arrPrototype,DB $aDB)
	{
		$aTableReflecter = self::tableReflecter($arrPrototype,$aDB) ;
	
		if( empty($arrPrototype['columns']) or $arrPrototype['columns']=='*' )
		{
			$arrPrototype['columns'] = $aTableReflecter->columns() ;
		}
	
		if( empty($arrPrototype['keys']) )
		{
			$arrPrototype['keys'] = $aTableReflecter->primaryName() ;
				
			if( $arrPrototype['keys'] )
			{
				$arrPrototype['keys'] = (array) $arrPrototype['keys'] ;
			}
		}
	}
	
	
	// association
	public function addAssociation(array $arrAssociation)
	{
		$sFromXPath = $this->xpath() ;
		$sFromTableAlias = $this->tableAlias() ;
		
		if( empty($arrAssociation['assoc']) )
		{
			if( !empty($arrAssociation['type']) )
			{
				$arrAssociation['assoc'] = $arrAssociation['type'] ;
			}
			else
			{
				throw new Exception("Model关联缺少 type 属性：%s",var_export($arrAssociation,true)) ;
			}
		}
		if( !in_array($arrAssociation['assoc'],self::$arrAssociations) )
		{
			throw new Exception("Model关联无效：%s",@$arrAssociation['assoc']) ;
		}
		if( empty($arrAssociation['table']) )
		{
			throw new Exception("Model %s关联缺少有效的table属性",@$arrAssociation['assoc']) ;
		}
		
		// name / fullname
		if( !array_key_exists('name',$arrAssociation) )
		{
			$arrAssociation['name'] = null ;
		}
		
		// table/name trans evetn 
		EventManager::singleton()->emitEvent(__CLASS__,self::transTable,$arrArgv=array(&$arrAssociation['table'],&$arrAssociation['name'])) ;
		echo $arrAssociation['name'] ;
		if(!$arrAssociation['name'])
		{
			$arrAssociation['name'] = $arrAssociation['table'] ;
		}
		
		$arrAssociation['tableAlias'] = $sFromTableAlias . '.' . $arrAssociation['name'] ;
		$arrAssociation['xpath'] = ($sFromXPath? ($sFromXPath.'.'): '') . $arrAssociation['name'] ;

		// 通过反射数据表获得 原型关键信息
		self::reflectTable($arrAssociation,$this->db()) ;
		
		// 检查关联名称是否重复
		if( isset($this->arrPrototypeShortcut[$arrAssociation['xpath']]) )
		{
			throw new Exception(
					"名称冲突：正在向Model原型 %s 添加的关联的名称 %s 已经存在。"
					, array($sFromXPath,$arrAssociation['name'])
			) ;
		}
		
		// 添加关联
		$this->arrPrototype['associations'][$arrAssociation['name']] =& $arrAssociation ;
		$this->arrPrototypeShortcut[$arrAssociation['xpath']] =& $arrAssociation ;
		
		// 用 from property 的主键做为关联的 from keys
		if( empty($arrAssociation['fromKeys']) )
		{
			if( !$arrAssociation['fromKeys']=$this->keys() )
			{
				throw new Exception(
					"正在为Model原型%s添加一个缺少 fromKeys 的关联，该原型即没有设置主键，数据表上也没有主键，因此无法为新添加的关联自动设置 fromKeys"
					, $sFromXPath
				) ;
			}
		}
		else
		{
			$arrAssociation['fromKeys'] = (array) $arrAssociation['fromKeys'] ;
		}
		
		// 用 to property 的主键做为关联的 to keys
		if( empty($arrAssociation['toKeys']) )
		{
			if( !$arrAssociation['keys'] )
			{
				throw new Exception(
						"正在为Model原型%s添加一个缺少 toKeys 的关联，关联没有设置主键，数据表上也没有主键，因此无法为新添加的关联自动设置 toKeys"
						, $sFromXPath
				) ;
			}
			$arrAssociation['toKeys'] = $arrAssociation['keys'] ;
		}
		else
		{
			$arrAssociation['toKeys'] = (array) $arrAssociation['toKeys'] ;
		}
		
		// bridge
		if( $arrAssociation['assoc']==self::hasAndBelongsToMany )
		{
			$arrAssociation['bridgeTableAlias'] = $arrAssociation['tableAlias'] . '#bridge' ;
			
			if( empty($arrAssociation['bridge']) )
			{
				throw new Exception(
						"hasAndBelongsToMany(多对多)关联 %s 缺少必须的 bridge 属性"
						, $arrAssociation['xpath']
				) ;
			}

			if( empty($arrAssociation['toBridgeKeys']) )
			{
				// 通过反射数据表检查 bridge表 中有无 fromKeys 同名的字段
				// todo ...
				
				// 用 fromKeys 做为 toBridgeKeys
				$arrAssociation['toBridgeKeys'] = $arrAssociation['fromKeys'] ;
			}
			else
			{
				$arrAssociation['toBridgeKeys'] = (array) $arrAssociation['toBridgeKeys'] ;
			}
			
			if( empty($arrAssociation['fromBridgeKeys']) )
			{
				// 通过反射数据表检查 bridge表 中有无 toKeys 同名的字段
				// todo ...
				
				// 用 toKeys 做为 fromBridgeKeys
				$arrAssociation['fromBridgeKeys'] = $arrAssociation['toKeys'] ;
			}
			else
			{
				$arrAssociation['fromBridgeKeys'] = (array) $arrAssociation['fromBridgeKeys'] ;
			}
		}
				
		return $this ;
	}
	
	public function & refRaw($sXPath='$')
	{
		return $this->arrPrototypeShortcut[$sXPath] ;
	}
	
	public function switchPrototype($sXPath=null)
	{
		if( !isset($this->arrPrototypeShortcut[$sXPath]) )
		{
			throw new Exception("指定的原型：%s不存在，无法完成原型切换",$sXPath) ;
		}
		
		//$this->arrSwitchStack[] =& $this->arrPrototype ;
		$arrOriFullname = $this->arrPrototype['xpath'] ;
		
		$this->arrPrototype =& $this->arrPrototypeShortcut[$sXPath] ;
		
		return $arrOriFullname ;
	}
	
	/*public function back($step=1)
	{
		if(is_int($step))
		{
			if( $this->arrSwitchStack )
			{
				end($this->arrSwitchStack) ;
				while($step--)
				{
					$sKey = key($this->arrSwitchStack) ;
					$this->arrPrototype =& $this->arrSwitchStack[$sKey] ;
					unset($this->arrSwitchStack[$sKey]) ;
				}
			}
		}
		else 
		{
			if( !isset($this->arrPrototypeShortcut[$step]) )
			{
				throw new Exception("指定的原型：%s不存在，无法完成原型切换",$step) ;
			}
			$this->arrPrototype =& $this->arrPrototypeShortcut[$step] ;
			$this->arrSwitchStack = array() ;
		}
		return $this ;
	}*/
	
	/*public function transDataName(&$sDataName)
	{
		if( substr($sDataName,0,$this->nDataPrefixLength) !== $this->sDataPrefix )
		{
			$sDataName = $this->sDataPrefix . $sDataName ;
		} 
		
	}*/
	
	
	private $arrPrototype = array() ;
	
	private $arrPrototypeShortcut = array() ;
	
	private $arrSwitchStack ;
	
	
	// 共享状态
	private $aDB ;
	
}

