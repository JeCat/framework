<?php

namespace jc\ui\xhtml ;


use jc\fs\File;
use jc\lang\Type;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\CompilerBase;
use jc\util\match\RegExp;

class Compiler extends CompilerBase
{
	public function __construct()
	{
		$this->aExpressionCompiler = new ExpressionCompiler() ;
	}

	/**
	 * @return jc\ui\ICompiled
	 */
	public function loadCompiled($sCompiledPath)
	{
		return new Compiled($sCompiledPath) ;
	}
	
	public function compileExpression($sSource)
	{
		return $this->aExpressionCompiler->compile($sSource) ;
	}
	
	public function expressionCompiler()
	{
		return $this->aExpressionCompiler ;
	}
	
	public function setExpressionCompiler(ExpressionCompiler $aExpressionCompiler)
	{
		$this->aExpressionCompiler = $aExpressionCompiler ;
	}
	
	private $aExpressionCompiler ;
	
}

?>