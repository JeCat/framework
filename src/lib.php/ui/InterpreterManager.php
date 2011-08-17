<?php
namespace jc\ui ;

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
	
	public function add($aInterpreter,$sName=null,$bAdoptRelative=true)
	{
		parent::add($aInterpreter) ;
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
	public function parse(IFile $aSourceFile)
	{
		$aSource = String::createFromFile($aSourceFile) ;
		$aObjectContainer = new UIObject() ;
		
		foreach($this->iterator() as $aInterpreter)
		{
			$aInterpreter->parse($aSource,$aObjectContainer,$aSourceFile) ;
		}
		
		return $aObjectContainer ;
	}
}

?>