<?php
namespace org\jecat\framework\lang\compile\object ;

class Token extends AbstractObject
{
	const T_BRACE_OPEN = '{' ;
	const T_BRACE_CLOSE = '}' ;
	const T_BRACE_SQUARE_OPEN = '[' ;
	const T_BRACE_SQUARE_CLOSE = ']' ;
	const T_BRACE_ROUND_OPEN = '(' ;
	const T_BRACE_ROUND_CLOSE = ')' ;
	const T_SEMICOLON = ';' ;
	const T_COLON = ',' ;
	const T_BIT_AND = '&' ;
	const T_EQUAL = '=' ;
	
	static private $arrExtTypes = array(
			self::T_BRACE_OPEN => 'Token::T_BRACE_OPEN',
			self::T_BRACE_CLOSE => 'Token::T_BRACE_CLOSE',
			self::T_BRACE_SQUARE_OPEN => 'Token::T_BRACE_SQUARE_OPEN',
			self::T_BRACE_SQUARE_CLOSE => 'Token::T_BRACE_SQUARE_CLOSE',
			self::T_BRACE_ROUND_OPEN => 'Token::T_BRACE_ROUND_OPEN',
			self::T_BRACE_ROUND_CLOSE => 'Token::T_BRACE_ROUND_CLOSE',
			self::T_SEMICOLON => 'Token::T_SEMICOLON',
			self::T_COLON => 'Token::T_COLON',
			self::T_BIT_AND => 'Token::T_BIT_AND',
			self::T_EQUAL => 'Token::T_EQUAL',
	) ; 

	public function __construct($nType,$sSource,$nPostion=0,$nLine=0)
	{
		parent::__construct($sSource,$nPostion,$nLine) ;
		
		$this->nType = $nType ;
		$this->setTokenType($nType) ;
		
		$this->sType = $this->tokenTypeName() ;
	}
	
	public function cloneOf(self $aOther)
	{
		$this->nType = $aOther->nType ;
		
		$this->setSourceCode($aOther->sourceCode()) ;
		$this->setTargetCode($aOther->targetCode()) ;
		$this->setPosition($aOther->position()) ;
		$this->setBelongsClass($aOther->belongsClass()) ;
		$this->setBelongsFunction($aOther->belongsFunction()) ;
		$this->setBelongsNamespace($aOther->belongsNamespace()) ;
		$this->setLine($aOther->line()) ;
		
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
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function objectPool()
	{
		return $this->parent() ;
	}

	public function setBelongsNamespace(NamespaceDeclare $aToken=null)
	{
		$this->aNamespace = $aToken ;
	}
	/**
	 * @return NamespaceDeclare
	 */
	public function belongsNamespace()
	{
		return $this->aNamespace ;
	}
	public function setBelongsClass(ClassDefine $aToken=null)
	{
		$this->aClass = $aToken ;
	}
	/**
	 * @return ClassDefine
	 */
	public function belongsClass()
	{
		return $this->aClass ;
	}
	public function setBelongsFunction(FunctionDefine $aToken=null)
	{
		$this->aFunction = $aToken ;
	}
	/**
	 * @return FunctionDefine
	 */
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
