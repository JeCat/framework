<?php
namespace jc\lang\aop\jointpoint ;

use jc\lang\compile\object\CallFunction;

use jc\lang\compile\object\FunctionDefine;
use jc\lang\compile\object\Token ;

class JointPointNewObject extends JointPoint
{
	public function __construct($sNewObjectPattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		parent::__construct($sWeaveClass,$sWeaveMethodNamePattern) ;
		
		$this->setNewObjectPattern( $sNewObjectPattern );
		$this->setNewObjectRegexp( self::transRegexp($sNewObjectPattern) );
	}
	
	public function matchExecutionPoint(Token $aToken)
	{
		return preg_match( $this->newObjectRegexp(),$aToken->sourceCode() )? true: false ;
	}
	
	static public function transRegexp($sPartten)
	{
		$sPartten = preg_quote($sPartten) ;
		$sPartten = str_replace('\\*', '.*', $sPartten) ;
		
		return '`new ' . $sPartten . '`is' ;
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