<?php

namespace jc\ui ;

use jc\system\Response;

use jc\system\Application;

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
	
	
	public function summary()
	{
		return "[Class:".get_class($this)."]" ;
	}

	public function printStruct(IOutputStream $aDevice=null,$nDepth=0)
	{
		if(!$aDevice)
		{
			$aDevice = Response::singleton()->printer() ;
		}
		
		$aDevice->write( str_repeat("\t",$nDepth) . $this->summary() . "\r\n" ) ;
		foreach ($this->iterator() as $aChild)
		{
			$aChild->printStruct($aDevice,$nDepth+1) ;
		}
	}
	
	private $aParent ;
	
}

?>