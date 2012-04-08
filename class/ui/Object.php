<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\util\HashTable;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\system\Application;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\pattern\composite\Composite;

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
	
	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function properties($bAutoCreate=true)
	{
		if(!$this->aProperties)
		{
			$this->aProperties = new HashTable() ;
		}
		return $this->aProperties ;
	}
	
	/**
	 * @return ObjectContainer
	 */
	public function objectContainer()
	{
		$aParent = $this ;
		while($aParent = $aParent->parent())
		{
			if( $aParent instanceof ObjectContainer )
			{
				return $aParent ;
			}
		}
	}
	
	private $aProperties ;
}

?>