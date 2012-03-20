<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\compile\DocComment;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\NamedObject;

class Advice extends NamedObject implements \Serializable
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

	static public function createFromToken(FunctionDefine $aFunctionDefine,Aspect $aAspect)
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

		$aAdvice = new self($aFunctionDefine->name(),$aFunctionDefine->bodySource(),$sPosition,$aFunctionDefine) ;
		$aAdvice->aDefineAspect = $aAspect ;
		
		// for pointcut
		foreach($aDocComment->itemIterator('for') as $sPointcutName)
		{
			if(!$aPointcut = $aAspect->pointcuts()->getByName($sPointcutName))
			{
				throw new Exception("定义Aspect %s 的 Advice %s 时，申明了一个不存在的 Pointcut: %s 。",array(
						$sAspectName
						, $aAdvice->name()
						, $sPointcutName
				)) ;
			}
			$aAdvice->arrForPointcuts[] = $sPointcutName ;
		}
		
		return $aAdvice ;
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
		return $this->bStatic ;
	}
	public function setStatic($bStatic)
	{
		$this->bStatic = $bStatic ;		
	} 
	
	public function addUseDeclare($sUseDclare)
	{
		if( !in_array($sUseDclare,$this->arrUseDeclares) )
		{
			$this->arrUseDeclares[] = $sUseDclare ;
		}
	}
	
	public function access()
	{
		return $this->sAccess ;
	}
	public function setAccess($sAccess)
	{
		$this->sAccess = $sAccess ;
	}
	
	public function signtrue()
	{
		return $this->sSigntrue ;
	}
	
	public function setAspect(Aspect $aAspect)
	{
		$this->aDefineAspect = $aAspect ;
	}
	public function aspect()
	{
		return $this->aDefineAspect ;
	}
	
	public function defineFile()
	{
		return $this->sDefineFile ;
	}
	public function setDefineFile($sDefineFile)
	{
		$this->sDefineFile = $sDefineFile ;
		$this->nDefineFilemtime = filemtime($sDefineFile) ;
	}
	
	public function forPointcuts()
	{
		return $this->arrForPointcuts ;
	}
	public function addPointcutName($sPointcutName)
	{
		if(!in_array($sPointcutName,$this->arrForPointcuts))
		{
			$this->arrForPointcuts[] = $sPointcutName ;
		}
	}
	
	public function serialize ()
	{
		return serialize( array(
			'sSource' => & $this->sSource ,
			'sPosition' => & $this->sPosition ,
			'sAccess' => & $this->sAccess ,
			'bStatic' => & $this->bStatic ,
			'sSigntrue' => & $this->sSigntrue ,
			'arrForPointcuts' => & $this->arrForPointcuts ,
			'sDefineFile' => & $this->sDefineFile ,
			'nDefineFilemtime' => & $this->nDefineFilemtime ,
			'sName' => $this->name() ,
		) ) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
		
		$this->sSource =& $arrData['sSource'] ;
		$this->sPosition =& $arrData['sPosition'] ;
		$this->sAccess =& $arrData['sAccess'] ;
		$this->bStatic =& $arrData['bStatic'] ;
		$this->sSigntrue =& $arrData['sSigntrue'] ;
		$this->arrForPointcuts =& $arrData['arrForPointcuts'] ;
		$this->sDefineFile =& $arrData['sDefineFile'] ;
		$this->nDefineFilemtime =& $arrData['nDefineFilemtime'] ;
		$this->setName($arrData['sName']) ;
	}
	
	public function isValid()
	{
		if( $this->sDefineFile and $this->nDefineFilemtime )
		{
			return is_file($this->sDefineFile) and filemtime($this->sDefineFile) <= $this->nDefineFilemtime ;
		}
		return true ;
	}
	
	private $arrUseDeclares = array() ;
	
	private $sSource ;
	
	private $sPosition = Advice::after ;
	
	private $sAccess = 'private' ;
	
	private $bStatic = false ;
	
	private $sSigntrue ;
	
	private $aDefineAspect ;
	
	private $sDefineFile ;

	private $nDefineFilemtime ;

	private $arrForPointcuts = array() ;
	
	protected $arrBeanConfig ;
}

?>