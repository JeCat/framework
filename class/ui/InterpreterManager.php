<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\IInputStream;

use org\jecat\framework\fs\File;

use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\Object as UIObject;

class InterpreterManager extends Container
{
	public function __construct()
	{
		$this->addAcceptClasses('org\\jecat\\framework\\ui\\IInterpreter') ;
	}
	
	public function remove($aInterpreter)
	{
		parent::remove($aInterpreter) ;
	}
	
	public function iterate()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrInterpreters) ;
	}
	
	/**
	 * @return IObject
	 */
	public function parse(IInputStream $aSourceInput,ObjectContainer $aObjectContainer,UI $aUI)
	{
		$aSource = new String ;
		$aSourceInput->readInString($aSource) ;
				
		foreach($this->iterator() as $aInterpreter)
		{
			$aInterpreter->parse($aSource,$aObjectContainer,$aUI) ;
		}
	}

	public function compileStrategySignture()
	{
		$seed = __CLASS__."\r\n" ;
		foreach($this->iterator() as $aInterpreter)
		{
			$seed.= $aInterpreter->compileStrategySignture()."\r\n" ;
		}
		return md5($seed) ;
	}
}

?>