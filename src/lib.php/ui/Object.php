<?php

namespace jc\ui ;

use jc\io\IOutputStream;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\Composite;

class Object extends Composite implements IObject
{
	public function __construct()
	{
		$this->addAcceptClasses('*') ;
	}

	// implement for IObject //////////////////	
	public function depth()
	{
		$aParent = $this->parent() ;
		return $aParent? $aParent->depth()+1: 0 ;
	}
	
	public function childAfter(self $aFind)
	{
		$aIter = $this->iterator() ;
		foreach($aIter as $aChild)
		{
			if($aFind==$aChild)
			{
				$aIter->next() ;
				return $aIter->current() ;
			}
		}
		
		return null ;
	}
	
	private $aParent ;
	
}

?>