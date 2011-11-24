<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\CallFunction;

use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token ;

class JointPointCallFunction extends JointPoint
{
	public function __construct($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		parent::__construct($sWeaveClass,$sWeaveMethodNamePattern) ;
		
		$this->setCallFunctionNamePattern( $sCallFunctionNamePattern );
		$this->setCallFunctionNameRegexp( self::transRegexp($sCallFunctionNamePattern) );
	}
	
	public function matchExecutionPoint(Token $aToken)
	{
		return preg_match( $this->callFunctionNameRegexp(),$aToken->sourceCode() )? true: false ;
	}

	public function callFunctionNameRegexp()
	{
		return $this->sCallFunctionNameRegexp;
	}
	
	public function callFunctionNamePattern()
	{
		return $this->sCallFunctionNamePattern;
	}
	
	public function setCallFunctionNameRegexp($sCallFunctionNameRegexp)
	{
		$this->sCallFunctionNameRegexp = $sCallFunctionNameRegexp;
	}
	
	public function setCallFunctionNamePattern($sCallFunctionNamePattern)
	{
		$this->sCallFunctionNamePattern = $sCallFunctionNamePattern;
	}
	
	private $sCallFunctionNamePattern ;
	
	private $sCallFunctionNameRegexp ;
}

?>