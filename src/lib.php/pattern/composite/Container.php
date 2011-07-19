<?php
namespace jc\pattern\composite ;


use jc\util\FilterMangeger ;
use jc\pattern\composite\IContainedable;
use jc\lang\Type;
use jc\lang\Exception;
use jc\lang\Object;

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
	
	public function add($object,$sName=null,$bAdoptRelative=false)
	{
		if( $object==$this )
		{
			return ;
		}
		
		if( !$this->accept($object) )
		{
			throw new Exception(__METHOD__."() 方法无法接受 %s 类型的参数",Type::reflectType($object)) ;
		}
		
		if( $sName===null and $object instanceof INamable )
		{
			$sName = $object->name() ;
		}
		
		if( $sName===null )
		{
			if( !in_array($object,$this->arrObjects) )
			{
				$this->arrObjects[] = $object ;
			}
		}
		else 
		{
			$this->arrObjects[$sName] = $object ;
		}
	
		if( $bAdoptRelative and ($object instanceof IContainedable) )
		{
			$object->setParent($this) ;
		}
		
		return $object ;
	}
	public function remove($object)
	{
		$nIdx = array_search($object,$this->arrObjects) ;
		if($nIdx!==false)
		{
			unset($this->arrObjects[$nIdx]) ;
		}
	}
	public function clear()
	{
		$this->arrObjects = array() ;
	}
	public function count()
	{
		return count($this->arrObjects) ;
	}
	/**
	 * @return \Iterate
	 */
	public function iterator()
	{
		return new \ArrayIterator($this->arrObjects) ;
	}
	/**
	 * @return \Iterate
	 */
	public function nameIterator()
	{
		return new \ArrayIterator( array_keys($this->arrObjects) ) ;
	}
	/**
	 * @return \Iterate
	 */
	public function acceptClassIterator()
	{
		return new \ArrayIterator($this->arrAcceptClasses) ;
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
		return isset($this->arrObjects[$sName])? $this->arrObjects[$sName]: null ;
	}
	
	public function hasName($sName)
	{
		return array_key_exists($sName,$this->arrObjects) ;
	}
	
	public function has($object)
	{
		return in_array($object,$this->arrObjects) ;
	}
	
	private $arrObjects = array() ;
	
	private $arrAcceptClasses = array() ;
	
	private $aAddFilters ;
}

?>