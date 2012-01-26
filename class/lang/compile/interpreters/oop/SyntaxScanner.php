<?php

namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\pattern\iterate\IReversableIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\lang\compile\IInterpreter;
use org\jecat\framework\lang\Object;

class SyntaxScanner extends Object implements IInterpreter
{
	public function __construct()
	{
		$this->aPHPCodeParser = new PHPCodeParser() ;
		
		$this->arrParsers[] = new NamespaceDeclareParser() ;
		$this->arrParsers[] = new UseDeclareParser() ;
		$this->arrParsers[] = new ClassDefineParser() ;
		$this->arrParsers[] = new FunctionDefineParser() ;
		$this->arrParsers[] = new CallFunctionParser() ;
		$this->arrParsers[] = new ParameterParser() ;
		//$this->arrParsers[] = new FunctionCallParser() ;
		//$this->arrParsers[] = new NewObjectParser() ;
		//$this->arrParsers[] = new ThrowParser() ;
	}
	
	public function analyze(TokenPool $aTokenPool)
	{
		$aState = new State() ;
		$aTokenPoolIter = $aTokenPool->iterator() ;
		
		$t = microtime(true) ;$cnt = 0 ;
		foreach($aTokenPoolIter as $aToken)
		{$cnt ++ ;
			// 扫描php代码
			$this->aPHPCodeParser->parse($aTokenPool,$aTokenPoolIter,$aState) ;
			if( !$aState->isPHPCode() )
			{
				continue ;
			}
				
			foreach($this->arrParsers as $aParser)
			{
				$aParser->parse($aTokenPool,$aTokenPoolIter,$aState) ;
			}
			
			$aToken->setBelongsNamespace($aState->currentNamespace()) ;
			$aToken->setBelongsClass($aState->currentClass()) ;
			$aToken->setBelongsFunction($aState->currentFunction()) ;
		}
		echo 'foreach:',microtime(true)-$t, ' tokens:', $cnt, '<br />' ;
	}
	
	
	private $arrParsers ;
	private $aPHPCodeParser ;
}

?>
