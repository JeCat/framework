<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\NamedObject;

class Advice extends NamedObject
{
	const around = 'around' ;
	const before = 'before' ;
	const after = 'after' ;
	
	static private $arrPositionTypes = array(
		self::around, self::before, self::after
	) ;
	
	public function __construct($sName,$sSource,$sPosition=self::after,FunctionDefine $aToken=null)
	{
		if( !in_array($sPosition,self::$arrPositionTypes) )
		{
			throw new Exception("传入了无效的\$sPosition参数值：%s",$sPosition) ;
		}
		
		parent::__construct($sName) ;
		
		$this->sSource = $sSource ;
		$this->sPosition = $sPosition ;
	
		// access
		if( $aToken and $aAccessToken=$aToken->accessToken() )
		{
			$this->sAccess = $aAccessToken->targetCode() ;
		}
		
		// static
		$this->bStatic = ($aToken and $aToken->staticToken())? true: false ;

		// signtrue
		if($aToken)
		{
			$this->sSigntrue = '' ;
			if( $aClass = $aToken->belongsClass() )
			{
				$this->sSigntrue = $aClass->fullName().'::' ;
			}
			if( $aFunction = $aToken->belongsFunction() )
			{
				$this->sSigntrue.= $aFunction->name().'()' ;
			}
		}
		if(!$this->sSigntrue)
		{
			$this->sSigntrue = $sName ;
		}
	}

	static public function createFromToken(FunctionDefine $aFunctionDefine)
	{
		if( !$aClassDefine=$aFunctionDefine->belongsClass() )
		{
			throw new Exception("传入的 \$aFunctionDefine 参数无效，必须是一个类方法的定义Token") ;
		}
		
		if( !$aDocToken = $aFunctionDefine->docToken() )
		{
				throw new Exception("传入了无效Advice %s::%s() ：没有DocComment申明。",array(
					$aClassDefine->fullName()
					, $aFunctionDefine->name()
				)) ;
		}
		
		$aDocComment = $aDocToken->docComment() ;

		$sPosition = null ;
		if( $aDocComment->hasItem('advice') )
		{
			$sPosition = $aDocComment->item('advice') ;
			$sPosition = trim($sPosition) ;
			$sPosition = strtolower($sPosition) ;
		}
		if(!$sPosition)
		{
			$sPosition = self::after ;
		}

		return new self($aFunctionDefine->name(),$aFunctionDefine->bodySource(),$sPosition,$aFunctionDefine) ;
	}
	
	public function position()
	{
		return $this->sPosition ;
	}
	
	public function source()
	{
		return $this->sSource ;
	}
	
	public function token()
	{
		return $this->aToken ;
	}
	
	public function isStatic()
	{
		$this->bStatic ;
	}
	
	public function access()
	{
		$this->sAccess ;
	}
	
	public function signtrue()
	{
		return $this->sSigntrue ;
	}
	
	private $sSource ;
	
	private $sPosition ;
	
	private $sAccess = 'private' ;
	
	private $bStatic = false ;
	
	private $sSigntrue ;
}

?>