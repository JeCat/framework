<?php
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\pattern\composite\Container;

class TokenPool extends Container
{
	public function __construct($sSourceFilepath=null)
	{
		parent::__construct('org\\jecat\\framework\\lang\\compile\\object\\AbstractObject') ;
		$this->sSourceFilepath = $sSourceFilepath ;
	}
	
	public function add($object,$sName=null,$bAdoptRelative=true)
	{
		parent::add($object,$sName,$bAdoptRelative) ;
	}
	
	public function addClass(ClassDefine $aClass)
	{
		$this->arrClasses[$aClass->fullName()] = $aClass ;
	}
	
	public function addFunction(FunctionDefine $aFunction)
	{
		if( $aClass=$aFunction->belongsClass() )
		{
			$sClassName = $aClass->fullName() ;
		}
		else
		{
			$sClassName = '' ;
		}
		
		if( !$sFuncName = $aFunction->name() )
		{
			$sFuncName = '' ;
		}
		
		$this->arrMethods[$sClassName][$sFuncName] = $aFunction ;
	}

	public function findClass($sClassName)
	{
		return isset($this->arrClasses[$sClassName])? $this->arrClasses[$sClassName]: null ;
	}
	
	public function findFunction($sFunctionName,$sClassName='')
	{
		return isset($this->arrMethods[$sClassName][$sFunctionName])? $this->arrMethods[$sClassName][$sFunctionName]: null ;
	}
	
	/**
	 * @return Token
	 */
	public function findTokenBySource($sSource,$nSeek=0)
	{
		$nFound = 0 ;
		foreach($this->iterator() as $aToken)
		{
			if( $aToken->sourceCode()===$sSource )
			{
				if($nSeek===$nFound++)
				{
					return $aToken ;
				}
			}
		}
		
		return ;
	}

	public function classIterator()
	{
		return new ArrayIterator($this->arrClasses) ;
	}
	public function functionIterator($sClassName='')
	{
		return isset($this->arrMethods[$sClassName])?
					new ArrayIterator($this->arrMethods[$sClassName]):
					new ArrayIterator() ;
	}
	
	
	public function addUseDeclare(UseDeclare $aUseToken)
	{
		if( !$sName = $aUseToken->name() )
		{
			throw new ClassCompileException(null,$aUseToken,"编译class时遇到无效的 use 关键词") ;
		}
	
		$this->arrNamespaces[$sName] = $aUseToken->fullName() ;
	}
	
	public function findName($name,NamespaceDeclare $aBelongNamespace=null)
	{
		if( $name instanceof Token )
		{
			$sName = $name->sourceCode() ;
			if( $name->belongsNamespace() )
			{
				$aBelongNamespace = $name->belongsNamespace() ;
			}
		}
		else
		{
			$sName = (string)$name ;
		}
		
		if( isset($this->arrNamespaces[$sName]) )
		{
			return $this->arrNamespaces[$sName] ;
		}
		else if( $aBelongNamespace )
		{
			return $aBelongNamespace->name() . '\\' . $sName ;
		}
		else
		{
			return $sName ;
		}
	}
	
	public function sourcePath()
	{
		return $this->sSourceFilepath ;
	}
	
	private $arrClasses = array() ;
	private $arrMethods = array() ;
	private $arrNamespaces ;
	private $sSourceFilepath ;
}

?>