<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\ObjectBase;
use jc\ui\IObject;
use jc\ui\IInterpreter;
use jc\lang\Object;
use jc\util\String;

class TreeBuilder extends Object implements IInterpreter
{
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath)
	{
		$aRoot = new ObjectBase(0,$aSource->length()-1,0,'') ;
		foreach($aObjectContainer->childrenIterator() as $aObject)
		{
			$aRoot->addChild($aObject) ;
		}
		
		$aObjectContainer->clearChildren() ;
		foreach($aRoot->childrenIterator() as $aObject)
		{
			$aObjectContainer->addChild($aObject) ;
		}
	}
}

?>