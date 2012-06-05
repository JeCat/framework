<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\lang\aop ;

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

