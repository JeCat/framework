<?php
namespace jc\ui ;

use jc\io\IInputStream;

use jc\fs\IFile;

use jc\pattern\composite\Container;
use jc\util\String;
use jc\ui\Object as UIObject;
use jc\util\HashTable;

class InterpreterManager extends Container
{
	public function __construct()
	{
		$this->addAcceptClasses('jc\\ui\\IInterpreter') ;
	}
	
	public function remove($aInterpreter)
	{
		parent::remove($aInterpreter) ;
	}
	
	public function iterate()
	{
		return new \jc\pattern\iterate\ArrayIterator($this->arrInterpreters) ;
	}
	
	/**
	 * @return IObject
	 */
	public function parse(IInputStream $aSourceInput)
	{
		$aSource = new String ;
		$aSourceInput->readInString($aSource) ;
		
		$aObjectContainer = new UIObject() ;
		
		foreach($this->iterator() as $aInterpreter)
		{
			$aInterpreter->parse($aSource,$aObjectContainer) ;
		}
		
		return $aObjectContainer ;
	}
}

?>