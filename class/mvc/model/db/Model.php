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

namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\mvc\model\db\orm\Deleter;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\mvc\model\db\orm\Selecter;
use org\jecat\framework\mvc\model\db\orm\Inserter;
use org\jecat\framework\mvc\model\db\orm\Updater;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\orm\Association;
use org\jecat\framework\mvc\model\AbstractModel;
use org\jecat\framework\mvc\model\db\orm\Prototype;

define('org\\jecat\\framework\\mvc\\model\\db\\Recordset\\KEY_MARK_CHAR','*') ;

/**
 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
 *  ==模型的克隆(clone)==
 *
 *  在蜂巢中，Model是一个封装对象，对象是引用传递(址传递)，这里引用了克隆(clone)来完成变量之间的传值，这里要注意clone不是Model的特有函数，而是对象的之间赋值的方法.
 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)模型的克隆"]
 */

class Model extends AbstractModel implements IBean
{
	/**
	 *  @wiki /MVC模式/数据库模型/模型列表(ModelList)
	 *  ==模型列表(ModelList)==
	 *  
	 *  Model也可以看作成是一个Model容器，可以放置多个Model,是多个Model的集合。
	 *  [example title="/MVC模式/数据库模型/模型列表(ModelList)"]
	 *  
	 */
	public function __construct(Prototype $aPrototype=null,$bList=false)
	{
	    parent::__construct($bList) ;
	    $this->setPrototype($aPrototype) ;	    
	}
	
	public function name()
	{
		return $this->aPrototype? $this->aPrototype->name(): null ; 
	}
	
	/**
	 * @return org\jecat\framework\mvc\model\db\orm\Prototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}

	public function setPrototype(Prototype $aPrototype=null)
	{
		$this->aPrototype = $aPrototype ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrDataSheet[$this->nDataRow]) ;
	}
	
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的加载(load)==
	 *
	 *  模型通过加载(load)对数据进行读取,如果不写加载条件，则会对数据表整个进行读取.
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/加载"]
	 */
	public function load($values=null,$keys=null)
	{
		if( $values instanceof Restriction )
		{
			trigger_error('Model::load() 不再接收 Criteria/Restriction 对像做为参数',E_USER_DEPRECATED ) ;
		}
		
		$this->clearData() ;
		
		$this->nDataRow = 0 ;
		return Selecter::singleton()->execute(
			$this->prototype() , $this->recordset(), null , self::buildRestriction($this->prototype(),$values,$keys), false, $this->db()
		) ;
	}
	
	public function loadSql($sWhereStatement=null,$arrFactors=null)
	{		
		$aRestriction = $sWhereStatement?
				call_user_func_array(array('org\\jecat\\framework\\db\\sql\\SQL','makeRestriction'),func_get_args())
				: null ;
		
		$this->clearData() ;
		$this->nDataRow = 0 ;
		
		return Selecter::singleton()->execute(
				$this->prototype() , $this->recordset(), null , $aRestriction, false, $this->db()
		) ;
	}

	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的保存(save)==
	 *  
	 *  数据的更新，数据的添加是通过模型的保存方法(save)实现的.实际上模型的保存方法是由两个部分集合起来的，insert和update.
	 *  当数据被Model加载过之后，save会自动判断使用update方法，对数据进行更新.当数据没有被Model加载过，save会自动判断使用insert方法,新添加一个数据
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/保存(update)"]
	 */
	public function save($bForceCreate=false)
	{
		// update
		if( !$bForceCreate and $this->hasSerialized() )
		{
			return $this->update() ;
		}
		
		// insert
		else 
		{
			return $this->insert() ;
		}
	}

	protected function insert()
	{
		return Inserter::singleton()->execute($this->db(), $this) ;
	}
	
	protected function update()
	{
		return Updater::singleton()->execute($this->db(), $this) ;
	}
	
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的删除(delete)==
	 *
	 *  数据的删除，由模型的删除(delete)方法完成.删除数据之前，通过对Model的加载(load)锁定要删除的数据行,这里加载相当与sql中的where的过滤条件
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/删除"]
	 */
	public function delete()
	{
		return Deleter::singleton()->execute($this->db(), $this) ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\Criteria
	 */
	/*public function createCriteria(Restriction $aRestriction=null)
	{
		return ; //$this->prototype()->statementFactory()->createCriteria($aRestriction) ;
	}*/
	
	/**
	 * @return org\jecat\framework\db\sql\Restriction
	 */
	/*public function createWhere($bLogic=true)
	{
		return ; //$this->prototype()->statementFactory()->createRestriction($bLogic) ;
	}*/
	
	static public function buildRestriction(Prototype $aPrototype,$values=null,$keys=null)
	{
		if($values===null)
		{
			return null ;
		}
		
		$keys = $keys? (array)$keys: $aPrototype->keys() ;
		
		if($values instanceof Restriction)
		{
			return $values ;
		}
		else
		{			
			$aRestriction = new Restriction() ;
			
			$sSqlTableAlias = $aPrototype->sqlTableAlias() ;
			$values = array_values((array) $values) ;
			foreach($keys as $nIdx=>$sKey)
			{
				list($sTable,$sColumn) = SQL::splitColumn($sKey) ;
				$aRestriction->expression( array(
						SQL::createRawColumn($sTable,$sColumn),
						'=', SQL::transValue($values[$nIdx])
				), true, true ) ;
			}
			return $aRestriction ;
		}
	}
	
	// implements IBean
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !empty($arrConfig['orm']) )
		{
			if( !empty($arrConfig['name']) )
			{
				$arrConfig['orm']['name'] = $arrConfig['name'] ;
			}
			if(empty($arrConfig['orm']['class']))
			{
				$arrConfig['orm']['class'] = 'prototype' ;
			}
			if(empty($arrConfig['orm']['model-class']))
			{
				$arrConfig['orm']['model-class'] = $arrConfig['class'] ;
			}
			if( !$aPrototype = BeanFactory::singleton()->createBean($arrConfig['orm'],$sNamespace) )
			{
				throw new BeanConfException("无法创建orm bean: %s , %s",array($sNamespace,var_export($arrConfig['orm'],true))) ;
			}
			
			$aBean = $aPrototype->createModel( !empty($arrConfig['list']) ) ;
		}
		else
		{
			$sClass = get_called_class() ;
			$aBean = new $sClass() ;
		}
		
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/数据库模型/模型的Bean配置数组
	 * ==Bean配置数组==
	 *
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |model-class
	 * |string
	 * |无
	 * |可选
	 * |用哪个类来实现模型对象
	 * |-- --
	 * |table
	 * |string
	 * |无
	 * |可选
	 * |对应的数据库表
	 * |-- --
	 * |name
	 * |string
	 * |无
	 * |可选
	 * |在原型关系中的名字,用来区分不同的原型
	 * |-- --
	 * |columns
	 * |array
	 * |无
	 * |可选
	 * |需要表中哪些列的数据
	 * |-- --
	 * |keys
	 * |array
	 * |无
	 * |可选
	 * |指定表中哪些列为主键,若指定了主键则使用这里的主键而忽略数据库主键,如果未指定则使用数据库指定的主键
	 * |-- --
	 * |alias
	 * |string
	 * |无
	 * |可选
	 * |别名
	 * |-- --
	 * |list
	 * |bool
	 * |false
	 * |可选
	 * |设置此对象是否是其他模型对象的容器
	 * |-- --
	 * |limit
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的上限,下限为0
	 * |-- --
	 * |limitLen
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的上限
	 * |-- --
	 * |limitFrom
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的下限
	 * |-- --
	 * |order
	 * |array
	 * |无
	 * |可选
	 * |指定依据某一列来排序,同时设置正序排列
	 * |-- --
	 * |orderAsc
	 * |string
	 * |无
	 * |可选
	 * |指定依据某一列正序排序
	 * |-- --
	 * |orderDesc
	 * |string
	 * |无
	 * |可选
	 * |指定依据某一列反序排序
	 * |-- --
	 * |orderRand
	 * |bool
	 * |无
	 * |可选
	 * |随机排列结果
	 * |-- --
	 * |where
	 * |array
	 * |无
	 * |可选
	 * |where条件（where的格式很有趣，是对Lisp风格的尝试）
	 * |-- --
	 * |hasOne
	 * |array
	 * |无
	 * |可选
	 * |配置hasone关系
	 * |-- --
	 * |belongsTo
	 * |array
	 * |无
	 * |可选
	 * |配置belongsTo关系
	 * |-- --
	 * |hasMany
	 * |array
	 * |无
	 * |可选
	 * |配置hasMany关系
	 * |-- --
	 * |hasAndBelongsToMany
	 * |array
	 * |无
	 * |可选
	 * |配置hasAndBelongsToMany关系
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::setList(!empty($arrConfig['list'])) ;
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	public function db()
	{
		return DB::singleton() ;
	}
	
	
	public function hasSerialized()
	{
		return Selecter::singleton()->hasExists($this) ;
	}
	
	
	
	// ---------------------------------------------
	public function serializableProperties()
	{
		$arrProps['org\\jecat\\framework\\mvc\\model\\AbstractModel'] = array('arrChildren') ;
		$arrProps[__CLASS__] = array('aPrototype','arrDataSheet','nDataRow') ;
		return $arrProps ;
	}
	
	
	
	
	
	// ------------------------------
	
	public function data($sName)
	{
		$this->transDataName($sName) ;
		return isset($this->arrDataSheet[$this->nDataRow][$sName])?
			$this->arrDataSheet[$this->nDataRow][$sName]: null ;
	}
	
	public function setData($sName,$value, $bChanged=true)
	{
		$this->transDataName($sName) ;
		
		$sOriData = isset($this->arrDataSheet[$this->nDataRow][$sName])?
			$this->arrDataSheet[$this->nDataRow][$sName]: null ;
	
		if( $bChanged and $value!=$sOriData )
		{
			$this->arrDataSheet[$this->nDataRow][Recordset\KEY_MARK_CHAR.'_changed'][$sName] = $sOriData ;
		}

		$this->arrDataSheet[$this->nDataRow][$sName] = $value ;
		
		return $this ;
	}
	public function __get($sName)
	{
		return $this->data ( $sName );
	}
	
	public function __set($sName, $value)
	{
		$this->setData ( $sName, $value );
	}
	
	public function hasData($sName)
	{
		$this->transDataName($sName) ;
		
		return is_array($this->arrDataSheet)
			and isset($this->arrDataSheet[$this->nDataRow])
			and is_array($this->arrDataSheet[$this->nDataRow])
			and key_exists($sName,$this->arrDataSheet[$this->nDataRow]) ;
	}
	
	public function removeData($sName)
	{
		$this->transDataName($sName) ;
		
		unset(
			$this->arrDataSheet[$this->nDataRow][Recordset\KEY_MARK_CHAR.'_changed'][$sName]
			, $this->arrDataSheet[$this->nDataRow][$sName]
		) ;
		
		return $this ;
	}
	
	public function clearData()
	{
		$this->arrDataSheet = array() ;
	}
	
	/**
	 * @param string $sName	$sName=null返回一个数组，或返回指定数据项的“是否变化”状态
	 */
	public function changed($sName=null)
	{
		if($sName===null)
		{
			return !empty($this->arrDataSheet[$this->nDataRow][Recordset\KEY_MARK_CHAR.'_changed']) ;
		}
		else
		{
			$this->transDataName($sName) ;
			return 
				array_key_exists(Recordset\KEY_MARK_CHAR.'_changed',$this->arrDataSheet[$this->nDataRow])
					and array_key_exists($sName, $this->arrDataSheet[$this->nDataRow][Recordset\KEY_MARK_CHAR.'_changed']) ;
		}
	}
	
	public function clearChanged()
	{
		unset($this->arrDataSheet[$this->nDataRow][Recordset\KEY_MARK_CHAR.'_changed']) ;
	}
	
	private function _dataIterator($bForDataName=false)
	{
		if( !$aPrototype=$this->prototype() )
		{
			return ;
		}
		if( !is_array($this->arrDataSheet) or !isset($this->arrDataSheet[$this->nDataRow]) or !is_array($this->arrDataSheet[$this->nDataRow]) )
		{
			return new \EmptyIterator() ;
		}
		
		$arrOwnData = array() ;
		foreach($aPrototype->columns(true) as $sDataName)
		{
			$arrOwnData[] = $bForDataName? $sDataName: $this->data($sDataName) ;
		}
		return new \ArrayIterator($arrOwnData) ;
	}
	public function dataIterator($bForDataName=false)
	{
		return $this->_dataIterator(false) ;
	}
	public function dataNameIterator()
	{
		return $this->_dataIterator(true) ;
	}
	
	protected function transDataName(& $sName)
	{
		$aPrototype = $this->prototype() ;
		$arrPath = explode('.',$sName) ;
		
		if(count($arrPath)>1)
		{
			$sName = array_pop($arrPath) ;
			while( each($arrPath)!==false )
			{
				$sPrototypeName = array_shift($arrPath) ;
				if( !$aAssociation = $aPrototype->associationByName($sPrototypeName) )
				{
					if($arrPath)
					{
						$sPrototypeName.= '.'.implode('.',$arrPath) ;
					}
					$sName = $sPrototypeName . '.' . $sName ;
					break ;
				}
				
				$aPrototype = $aAssociation->toPrototype() ;
			}
		}
		
		$sName = $aPrototype->getColumnByAlias($sName)?: $sName ;
		
		return $sName = $aPrototype->path().'.'.$sName ;
	}
	
	/**
	 * @return Recordset
	 */
	protected function & recordset()
	{
		if($this->arrDataSheet===null)
		{
			$this->arrDataSheet = array() ;
		}
		return $this->arrDataSheet ;
	}
	
	protected function & childrenContainer($bCreate=true)
	{
		$sContainerKey = Recordset\KEY_MARK_CHAR.'_children_'.$this->prototype()->path() ;
		if( !isset($this->arrDataSheet[$this->nDataRow][$sContainerKey]) )
		{
			$this->arrDataSheet[$this->nDataRow][$sContainerKey] = $bCreate? array(): null ;
		}

		return $this->arrDataSheet[$this->nDataRow][$sContainerKey] ;
	}
	
	/**
	 * @return Model
	 */
	public function child($sName)
	{
		$arrChildrenContainer =& $this->childrenContainer() ;
		
		$aChild = isset($arrChildrenContainer[$sName])?
						$arrChildrenContainer[$sName]: null ;
		
		if( !$aChild and $this->aPrototype and $aAssociation=$this->aPrototype->associationByName($sName) )
		{
			$aChildPrototype = $aAssociation->toPrototype() ;
			$bIsList = !$aAssociation->isType(Association::oneToOne) ;
			
			$aChild = $aChildPrototype->createModel( $bIsList ) ;
			$this->segmentalizeChild( $aChild, $bIsList, $sName ) ;
			
			$arrChildrenContainer[$sName] = $aChild ;
		}
		
		return $aChild ;
	}
	
	public function addChild(IModel $aModel, $sName = null)
	{
		if( $sName===null )
		{
			$sName = $aModel->name() ;
		}
		$arrChildrenContainer =& $this->childrenContainer() ;
		$arrChildrenContainer[$sName] = $aModel ;
	}
	
	public function removeChild(IModel $aModel)
	{
		$arrChildrenContainer =& $this->childrenContainer() ;
		unset ( $arrChildrenContainer[$aModel->name()] );
	}
	
	public function clearChildren()
	{
		$arrChildrenContainer =& $this->childrenContainer() ;
		$arrChildrenContainer = null ;
	}
	
	public function childrenCount()
	{
		$arrChildrenContainer =& $this->childrenContainer() ;
		return $arrChildrenContainer? count($arrChildrenContainer): 0 ; 
	}
	
	/**
	 * @return IIterator
	 */
	public function childIterator()
	{
		
		foreach( $this->prototype()->associationNames() as $sAssociationName)
		{
			// 自动创建子模型
			$this->child($sAssociationName) ;
			
		}
		
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->childrenContainer()) ;
	}
	
	/**
	 * @return IIterator
	 */
	public function childNameIterator()
	{
		$arrChildrenContainer =& $this->childrenContainer() ;
		$arrChildNames = $arrChildrenContainer? array_keys($arrChildrenContainer): array() ;
		
		foreach( $this->prototype()->associationNames() as $sAssociationName)
		{
			if(!in_array($sAssociationName,$arrChildNames))
			{
				$arrChildNames[] = $sAssociationName ;
			}
		}
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($arrChildNames) ;
	}
	
	
	
	
	protected function segmentalizeChild(Model $aChild,$bIsList=false,$sName=null)
	{
		if($bIsList)
		{
			$aChild->arrDataSheet =& self::dataSheet($this->arrDataSheet,$this->nDataRow,$sName,true) ;
			$aChild->nDataRow = 0 ;
		}
		
		else
		{
			$aChild->arrDataSheet =& $this->arrDataSheet ;
			
			// 按引用传递，完全和 parent model 一致，当 parent 的 DataRow 在 DataSheet 中移动时，将会影响 child model
			$aChild->nDataRow =& $this->nDataRow ;
		}
		
		return $aChild ;
	}
		
	public function printStruct(IOutputStream $aOutput = null, $nDepth = 0, $sDisplayTitle=null )
	{
		if (! $aOutput)
		{
			$aOutput = Response::singleton()->printer();
		}
		
		$aOutput->write ( "<pre>\r\n\r\n" );
		
		$aOutput->write ( str_repeat ( "\t", $nDepth ) ) ;
		if( $sDisplayTitle===null )
		{
			$sDisplayTitle = "<b>[Model] ".$this->name().'</b>' ;
		}
		$aOutput->write ( $sDisplayTitle."\r\n") ;

		// 数据
		$this->printStructData($aOutput,$nDepth) ;
		
		// 子模型
		$this->printStructChildren($aOutput,$nDepth) ;
		
		$aOutput->write ( "</pre>" );
		
		return ;
	}
	
	protected function printStructData(IOutputStream $aOutput = null, $nDepth = 0)
	{
		$aPrototype = $this->prototype() ;
		foreach( $aPrototype->columns() as $sDataName )
		{
			$aOutput->write ( str_repeat ( "\t", $nDepth+1 ) . "{$sDataName}: " . $this->data($sDataName) . "\r\n" );
		}
	}
	protected function printStructChildren(IOutputStream $aOutput = null, $nDepth = 0)
	{
		foreach ( $this->childIterator () as $sName=>$aChild )
		{
			$aChild->printStruct ( $aOutput, $nDepth + 1, "<b>[Model".($aChild->isList()?' List':'')."] {$sName}</b>" );
		}	
	}
	
	public function isList()
	{
		return false ;
	}
	
	static public function & dataSheet(array & $parentSheet,$nRow,$sSheetName,$bAutoCreate=false)
	{
		$sKey = Recordset\KEY_MARK_CHAR.'_sheet'.Recordset\KEY_MARK_CHAR ;
		if( !isset($parentSheet[$nRow][$sKey][$sSheetName]) )
		{
			if(!$bAutoCreate)
			{
				$null = null ;
				return $null ;
			}
			else
			{
				$parentSheet[$nRow][$sKey][$sSheetName] = array() ;
			}
		}
	
		return $parentSheet[$nRow][$sKey][$sSheetName] ;
	}
	
	protected $arrDataSheet = array() ;
	protected $nDataRow = 0 ;
	
	/**
	 * @var org\jecat\framework\mvc\model\db\orm\Prototype
	 */
	private $aPrototype ;
	private $aCriteria ;
	
	private $arrBeanConfig ;
	
	
}




