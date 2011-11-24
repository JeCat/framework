<?php
namespace org\jecat\framework\pattern\composite ;


use org\jecat\framework\util\FilterMangeger ;
use org\jecat\framework\pattern\composite\IContainedable;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Container extends Object implements IContainer
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
			if( !in_array($sClass,$this->arrAcceptClasses) )
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
	
	public function add($object,$sName=null)
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
			
			$this->attach($object,$sName) ;
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
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( array_keys($this->arrNames) ) ;
	}
	/**
	 * @return \Iterate
	 */
	public function acceptClassIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrAcceptClasses) ;
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

	public function getByPosition($nPosition)
	{
		return isset($this->arrObjects[$nPosition])? $this->arrObjects[$nPosition]: null ;
	}
	
	public function hasName($sName)
	{
		return array_key_exists($sName,$this->arrNames) ;
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
			return ;
		}
		
		$arrArgs = func_get_args() ;
		$arrArgs[0] = $nPos ;
		
		call_user_func_array(array($this,'insertBeforeByPosition'),$arrArgs) ;
	}
	
	public function insertBeforeByPosition($nPos=0,$_)
	{
		$arrArgs = func_get_args() ;
		array_shift($arrArgs) ;
	
		foreach(array_values($arrArgs) as $nIdx=>$aInsObject)
		{
			if( $this->has($aInsObject) )
			{
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
			return ;
		}
		
		$arrArgs = func_get_args() ;
		$arrArgs[0] = $nPos ;
		
		call_user_func_array(array($this,'insertAfterByPosition'),$arrArgs) ;
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
					$this->remove($aInsObject) ;
				}
				
				array_splice($this->arrObjects,$nPos+$nIdx,0,array($aInsObject)) ;
				
				$this->attach($aInsObject) ;
			}
		}
	}

	private function attach($object,$sName=null)
	{
		if( $sName===null and $object instanceof INamable )
		{
			$sName = $object->name() ;
		}
		
		if( $sName!==null )
		{
			$this->arrNames[$sName] = $object ;
		}
	
		if( $object instanceof IContainedable )
		{
			$object->setParent($this) ;
		}
	}
	private function free($object)
	{
		// 移除名称检索
		$sName = array_search($object,$this->arrNames,is_object($object)) ;
		if($sName!==false)
		{
			unset($this->arrNames[$sName]) ;
		}
		
		// 解除父子关系
		if( $object instanceof IContainedable and $object->parent()==$this )
		{
			$object->setParent(null) ;
		}
	}
	
	private $arrObjects = array() ;
	
	private $arrNames = array() ;
	
	private $arrAcceptClasses = array() ;
	
	private $aAddFilters ;
}

?>