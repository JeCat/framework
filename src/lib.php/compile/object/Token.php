<?php
namespace jc\compile\object ;

class Token extends AbstractObject
{
	const T_BRACE_OPEN = '{' ;
	const T_BRACE_CLOSE = '}' ;
	const T_BRACE_SQUARE_OPEN = '[' ;
	const T_BRACE_SQUARE_CLOSE = ']' ;
	const T_BRACE_ROUND_OPEN = '(' ;
	const T_BRACE_ROUND_CLOSE = ')' ;
	const T_SEMICOLON = ';' ;
	
	static private $arrExtTypes = array(
			self::T_BRACE_OPEN => 'Token::T_BRACE_OPEN',
			self::T_BRACE_CLOSE => 'Token::T_BRACE_CLOSE',
			self::T_BRACE_SQUARE_OPEN => 'Token::T_BRACE_SQUARE_OPEN',
			self::T_BRACE_SQUARE_CLOSE => 'Token::T_BRACE_SQUARE_CLOSE',
			self::T_BRACE_ROUND_OPEN => 'Token::T_BRACE_ROUND_OPEN',
			self::T_BRACE_ROUND_CLOSE => 'Token::T_BRACE_ROUND_CLOSE',
			self::T_SEMICOLON => 'Token::T_SEMICOLON',
	) ; 

	public function __construct($nType,$sSource,$nPostion)
	{
		parent::__construct($sSource,$nPostion) ;
		
		$this->nType = $nType ;
		$this->setTokenType($nType) ;
		
		$this->sType = $this->tokenTypeName() ;
	}
	
	protected function cloneOf(self $aOther)
	{
		$this->nType = $aOther->nType ;
		
		$this->setSourceCode($aOther->sourceCode()) ;
		$this->setTargetCode($aOther->targetCode()) ;
		$this->setPosition($aOther->position()) ;
		
		$this->sType = $this->tokenTypeName() ;
	}

	public function setTokenType($nType)
	{
		$this->nType = $nType ;
	}
	public function tokenType($bOri=false)
	{		
		if( !$bOri and $this->nType==T_STRING and isset(self::$arrExtTypes[$this->sourceCode()]) )
		{
			return $this->sourceCode() ;
		}
		
		else 
		{
			return $this->nType ;
		}
	}

	public function tokenTypeName()
	{
		$type = $this->tokenType() ;
		return isset(self::$arrExtTypes[$type])? self::$arrExtTypes[$type]: token_name($type) ;
	}

	/**
	 * @return jc\pattern\composite\IContainer
	 */
	public function objectPool()
	{
		return $this->parent() ;
	}

	public function setBelongsNamespace(NamespaceDeclare $aToken)
	{
		$this->aNamespace = $aToken ;
	}
	public function belongsNamespace()
	{
		return $this->aNamespace ;
	}
	public function setBelongsClass(ClassDefine $aToken)
	{
		$this->aClass = $aToken ;
	}
	public function belongsClass()
	{
		return $this->aClass ;
	}
	public function setBelongsFunction(FunctionDefine $aToken)
	{
		$this->aFunction = $aToken ;
	}
	public function belongsFunction()
	{
		return $this->aFunction ;
	}
	
	public function belongsSignature()
	{
		$sSignature = '' ;
	
		if($this->aNamespace)
		{
			$sSignature.= $this->aNamespace->name().'\\' ;
		}
		if($this->aClass)
		{
			$sSignature.= $this->aClass->name().'::' ;
		}
		if($this->aFunction)
		{
			$sSignature.= $this->aFunction->name().'()' ;
		}
		
		return $sSignature? ('['.$sSignature.']'): '' ;
	}
	
	private $nType ;

	private $aNamespace ;
	private $aClass ;
	private $aFunction ;
}

?>