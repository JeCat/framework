<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\CallFunction;

use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token ;

class JointPointNewObject extends JointPoint
{
	public function __construct($sNewObjectPattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		parent::__construct($sWeaveClass,$sWeaveMethodNamePattern) ;
		
		$this->setNewObjectPattern( $sNewObjectPattern );
		$this->setNewObjectRegexp( self::transRegexp($sNewObjectPattern) );
	}
	
	static public function createFromDeclare($sDeclare)
	{
		
	}
	public function exportDeclare($bWithClass=true)
	{
		return 'new '.($bWithClass?$this->weaveClass():'') ;
	}
	
	public function matchExecutionPoint(Token $aToken)
	{
		return preg_match( $this->newObjectRegexp(),$aToken->sourceCode() )? true: false ;
	}

	public function newObjectRegexp()
	{
		return $this->sNewObjectRegexp;
	}
	
	public function newObjectPattern()
	{
		return $this->sNewObjectPattern;
	}
	
	public function setNewObjectRegexp($sNewObjectRegexp)
	{
		$this->sNewObjectRegexp = $sNewObjectRegexp;
	}
	
	public function setNewObjectPattern($sNewObjectPattern)
	{
		$this->sNewObjectPattern = $sNewObjectPattern;
	}
	
	private $sNewObjectPattern ;
	
	private $sNewObjectRegexp ;
}

?>