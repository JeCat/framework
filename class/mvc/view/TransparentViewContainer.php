<?php 
namespace org\jecat\framework\mvc\view ;

/**
 * 对上级视图容器透明的视图容器类
 */
class TransparentViewContainer extends View
{
 	public function add($object,$sName=null,$bTakeover=true)
	{
		if( $object instanceof TransparentViewContainer )
		{
			if(!$sName)
			{
				$sName = $object->name() ;
			}
			
			if( isset($this->arrChildContainers[$sName]) )
			{
				throw new Exception("名称为：%s 的子视图在视图 %s 中已经存在，无法添加同名的子视图",array($sName,$this->name())) ;
			}
				
			$this->arrChildContainers[$sName] = $object ;
			
			if($bTakeover)
			{
				$object->setParent($this) ;
			}
		}
		else 
		{
			parent::add($object,$sName,$bTakeover) ;
		}
	}
	public function remove($object)
	{
		if( $object instanceof TransparentViewContainer )
		{
			if($this->arrChildContainers)
			{
				$pos = array_search($object,$this->arrChildContainers,true) ;
				if( $pos!==false )
				{
					unset($this->arrChildContainers[$pos]) ;	
				}
			}
		}
		else 
		{
			parent::remove($object) ;
		}
	}
	public function count()
	{
		$nCnt = parent::count() ;
		
		// for child container's children
		if($this->arrChildContainers)
		{
			foreach($this->arrChildContainers as $aContainer)
			{
				$nCnt+= $aContainer->count() ;
			}
		}
		
		return $nCnt ;
	}
	
	public function getByName($sName)
	{
		if( $aView = parent::getByName($sName) )
		{
			return $aView ;
		}
		
		if($this->arrChildContainers)
		{
			foreach($this->arrChildContainers as $sContainerName=>$aContainer)
			{
				$sNameFix = $sContainerName.'-' ;
				$nNameFixLen = strlen($sNameFix) ;
				
				if( strlen($sName)>$nNameFixLen and substr($sName,0,$nNameFixLen)==$sNameFix )
				{
					$sRealViewName = substr($sName,$nNameFixLen) ;
					if( $aView = $aContainer->getByName( $sRealViewName ) )
					{
						return $aView ;
					}
				}
			}
		}
	}
	
	public function getName($object)
	{
		if( $sName = parent::getName($object) )
		{
			return $sName ;
		}
		
		if($this->arrChildContainers)
		{
			foreach($this->arrChildContainers as $sContainerName=>$aContainer)
			{
				if($sName = $aContainer->getName($object))
				{
					return $sContainerName . '-' . $sName ;
				}
			}
		}
	}
	
	public function iterator()
	{
		$aViewIterator = parent::iterator() ;
		
		// for child container's children
		if($this->arrChildContainers)
		{
			foreach($this->arrChildContainers as $aContainer)
			{
				if($aContainer->count())
				{
					if(empty($aMergedIterator))
					{
						$aMergedIterator = new \AppendIterator() ;
						$aMergedIterator->append($aViewIterator) ;
					}
					$aMergedIterator->append($aContainer->iterator()) ;
				}
			}
		}
		
		// return merged iterators
		if(!empty($aMergedIterator))
		{
			return $aMergedIterator ;			
		}
		// only self iterator
		else 
		{
			return $aViewIterator ;
		}
	}

	public function nameIterator()
	{
		$aViewIterator = parent::nameIterator() ;
		
		// for child container's children
		if($this->arrChildContainers)
		{
			foreach($this->arrChildContainers as $sContainerName=>$aContainer)
			{
				if($aContainer->count())
				{
					if(empty($aMergedIterator))
					{
						$aMergedIterator = new \AppendIterator() ;
						$aMergedIterator->append($aViewIterator) ;
					}
					$aMergedIterator->append(
						new _TransparentViewContainerNameIterator($aContainer,$sContainerName.'-')
					) ;
				}
			}
		}
		
		// return merged iterators
		if(!empty($aMergedIterator))
		{
			return $aMergedIterator ;			
		}
		// only self iterator
		else 
		{
			return $aViewIterator ;
		}
	}
	
	private $arrChildContainers ;
}

class _TransparentViewContainerNameIterator extends \IteratorIterator
{
	public function __construct (TransparentViewContainer $aContainer,$sContainerName)
	{
		$this->sContainerName = $sContainerName ;
		parent::__construct($aContainer->nameIterator()) ;
	}
	
	public function current()
	{
		return $this->sContainerName . parent::current() ;
	}
	
	private $sContainerName ;
}
