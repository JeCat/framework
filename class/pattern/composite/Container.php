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
namespace org\jecat\framework\pattern\composite ;

use org\jecat\framework\util\FilterMangeger;
use org\jecat\framework\pattern\composite\IContainedable;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Container extends Object implements IContainer, \Serializable
{
	public function __construct($Classes=null)
	{
		if($Classes)
		{
			$this->addAcceptClasses($Classes) ;
		}
		
		parent::__construct() ;
	}
	
	public function addAcceptClasses($Classes)
	{
		foreach((array)$Classes as $sClass)
		{
			$sClass = strval($sClass) ;
			if( empty($this->arrAcceptClasses) or !in_array($sClass,$this->arrAcceptClasses) )
			{
				$this->arrAcceptClasses[] = $sClass ;
			}
		}
	}
	
	public function accept($object)
	{
		if( $this->aAddFilters )
		{
			$object = $this->aAddFilters->handle($object) ;
			if(!$object)
			{
				return false ;
			}
		}
		
		
		if( empty($this->arrAcceptClasses) )
		{
			return true ;
		}
		foreach($this->arrAcceptClasses as $sClass)
		{
			if($sClass=='*')
			{
				return true ;
			}
			if($object instanceof $sClass)
			{
				return true ;
			}
		}
		return false ;
	}
	
	public function add($object,$sName=null,$bTakeover=true)
	{
		if( $object==$this )
		{
			return ;
		}
		
		if( !$this->accept($object) )
		{
			throw new Exception(__METHOD__."() 方法无法接受 %s 类型的参数",Type::reflectType($object)) ;
		}
	
		if( !in_array($object,$this->arrObjects,is_object($object)) )
		{
			$this->arrObjects[] = $object ;
			
			$this->attach($object,$sName,$bTakeover) ;
		}
		
		return $object ;
	}
	
	public function remove($object)
	{
		// 移除对象
		$nIdx = array_search($object,$this->arrObjects,is_object($object)) ;
		if($nIdx!==false)
		{
			array_splice($this->arrObjects,$nIdx,1) ;
		}
		
		$this->free($object) ;
	}
	public function clear()
	{
		foreach($this->arrObjects as $object)
		{
			$this->remove($object) ;
		}
	}
	public function count()
	{
		return count($this->arrObjects) ;
	}
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrObjects) ;
	}
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function nameIterator()
	{
		return empty($this->arrNames)?
			new \EmptyIterator():
			new \org\jecat\framework\pattern\iterate\ArrayIterator( array_keys($this->arrNames) ) ;
	}
	/**
	 * @return \Iterate
	 */
	public function acceptClassIterator()
	{
		return  empty($this->arrAcceptClasses)?
			new \EmptyIterator():
			new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrAcceptClasses) ;
	}
	
	public function addFilters()
	{
		if(!$this->aAddFilters)
		{
			$this->aAddFilters = new FilterMangeger() ;
		}
		
		return $this->aAddFilters ;
	}

	public function getByName($sName)
	{
		return isset($this->arrNames[$sName])? $this->arrNames[$sName]: null ;
	}

	public function getName($object)
	{
		if(empty($this->arrNames))
		{
			return null ;
		}
		if( $sName = array_search($object,$this->arrNames,is_object($object)) )
		{
			return $sName ;
		}
		else if( $object instanceof INamable ) 
		{
			return $object->name() ;
		}
		else
		{
			return null ;
		}
	}

	public function getByPosition($nPosition)
	{
		return isset($this->arrObjects[$nPosition])? $this->arrObjects[$nPosition]: null ;
	}
	
	public function hasName($sName)
	{
		return empty($this->arrNames)? false: array_key_exists($sName,$this->arrNames) ;
	}
	
	public function has($object)
	{
		return in_array($object,$this->arrObjects,is_object($object)) ;
	}
	
	public function search($object)
	{
		return array_search( $object, $this->arrObjects, is_object($object) ) ;
	}
	
	public function replace($object,$newObject,$sName=null)
	{
		$nPos = $this->search($object) ;
		if($nPos===false)
		{
			return ;
		}
		
		$this->arrObjects[$nPos] = $newObject ;
	
		// 解除旧对象
		$this->free($object) ;
		
		// 建立新对象的关系
		$this->attach($newObject,$sName) ;
	}
	
	public function insertBefore($object,$_)
	{
		$nPos = $this->search($object) ;
		if($nPos===false)
		{
			return false ;
		}
		
		$arrArgs = func_get_args() ;
		$arrArgs[0] = $nPos ;
		
		call_user_func_array(array($this,'insertBeforeByPosition'),$arrArgs) ;
		
		return true ;
	}
	
	public function insertBeforeByPosition($nPos=0,$_)
	{
		$arrArgs = func_get_args() ;
		array_shift($arrArgs) ;
	
		foreach(array_values($arrArgs) as $nIdx=>$aInsObject)
		{
			if( $this->has($aInsObject) )
			{
				$nHasPos = $this->search($aInsObject) ;
				if($nHasPos <= $nPos ){
					-- $nPos ;
				}
				$this->remove($aInsObject) ;
			}
		
			array_splice($this->arrObjects,$nPos+$nIdx,0,array($aInsObject)) ;
			
			$this->attach($aInsObject) ;
		}
	}

	public function insertAfter($object,$_)
	{	
		$nPos = $this->search($object) ;
		if($nPos===false)
		{
			return false ;
		}
		
		$arrArgs = func_get_args() ;
		$arrArgs[0] = $nPos ;
		
		call_user_func_array(array($this,'insertAfterByPosition'),$arrArgs) ;
		
		return true ;
	}
	
	public function insertAfterByPosition($nPos,$_)
	{	
		$arrArgs = func_get_args() ;
		array_shift($arrArgs) ;
	
		// 最后一个
		if( count($this->arrObjects)-1 === $nPos )
		{
			foreach($arrArgs as $aInsObject)
			{
				if( $this->has($aInsObject) )
				{
					$this->remove($aInsObject) ;
				}
				
				$this->add($aInsObject) ;
			}
		}
		
		else 
		{		
			$nPos ++ ;
			
			foreach(array_values($arrArgs) as $nIdx=>$aInsObject)
			{
				if( $this->has($aInsObject) )
				{
					$nHasPos = $this->search($aInsObject) ;
					if($nHasPos <= $nPos ){
						-- $nPos ;
					}
					$this->remove($aInsObject) ;
				}
				
				array_splice($this->arrObjects,$nPos+$nIdx,0,array($aInsObject)) ;
				
				$this->attach($aInsObject) ;
			}
		}
	}

	private function attach($object,$sName=null,$bTakeover=true)
	{
		if( $sName===null and $object instanceof INamable )
		{
			$sName = $object->name() ;
		}
		
		if( $sName!==null )
		{
			$this->arrNames[$sName] = $object ;
		}
	
		if( $bTakeover and $object instanceof IContainedable )
		{
			$object->setParent($this) ;
		}
	}
	private function free($object)
	{
		// 移除名称检索
		if(!empty($this->arrNames))
		{
			$sName = array_search($object,$this->arrNames,is_object($object)) ;
			if($sName!==false)
			{
				unset($this->arrNames[$sName]) ;
			}
		}
		
		// 解除父子关系
		if( $object instanceof IContainedable and $object->parent()==$this )
		{
			$object->setParent(null) ;
		}
	}

	public function serialize ()
	{
		$arrData['arrObjects'] =& $this->arrObjects ;
		if(!empty($this->arrAcceptClasses))
		{
			$arrData['arrAcceptClasses'] =& $this->arrAcceptClasses ;
		}
		if(!empty($this->arrNames))
		{
			foreach($this->arrNames as $sName=>$aObject)
			{
				$pos = $this->search($aObject) ;
				if($pos!==false)
				{
					$arrData['arrNames'][$sName] = $pos ;
				}
			}
		}
		
		return serialize($arrData) ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
		$this->arrObjects =& $arrData['arrObjects'] ;
		if(!empty($arrData['arrAcceptClasses']))
		{
			$this->arrAcceptClasses =& $arrData['arrAcceptClasses'] ;
		}
		if(!empty($arrData['arrNames']))
		{
			foreach($arrData['arrNames'] as $sName=>&$pos)
			{
				$this->arrNames[$sName] = $this->arrObjects[$pos] ;
			}
		}
	}
	
	private $arrObjects = array() ;
	
	private $arrNames = null ;
	
	private $arrAcceptClasses = null ;
	
	private $aAddFilters ;
}



