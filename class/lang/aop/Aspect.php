<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\fs\FSO;
use org\jecat\framework\io\InputStreamCache;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\DocComment;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\pattern\composite\NamedObject;

class Aspect extends NamedObject implements \Serializable
{	
	static public function createFromToken(ClassDefine $aClassToken,$sAspectFilepath=null)
	{
		$sAspectName = $aClassToken->fullName() ;
		$aTokenPool = $aClassToken->parent() ;
		
		$aAspect = new self($sAspectName) ;
		
		// 先定义 pointcut
		foreach($aTokenPool->functionIterator($sAspectName) as $aMethodToken)
		{
			if( !$aDocCommentToken=$aMethodToken->docToken() or !$aDocComment=$aDocCommentToken->docComment() )
			{
				continue ;
			}
			
			// pointcut
			if( $aDocComment->hasItem('pointcut') )
			{
				$aPointcut = Pointcut::createFromToken($aMethodToken) ;
				$aAspect->pointcuts()->add($aPointcut) ;
			}
		}
		
		// 然后定义 advice
		foreach($aTokenPool->functionIterator($sAspectName) as $aMethodToken)
		{
			if( !$aDocCommentToken=$aMethodToken->docToken() or !$aDocComment=$aDocCommentToken->docComment() )
			{
				continue ;
			}
			
			if( $aDocComment->hasItem('advice') )
			{
				$aAdvice = Advice::createFromToken($aMethodToken,$aAspect) ;
				$aAspect->addAdvice($aAdvice) ;
			}
		}
		
		$aAspect->sAspectName = $sAspectName ;
		if($sAspectFilepath)
		{
			$aAspect->sAspectFilepath = FSO::tidyPath($sAspectFilepath) ;
			$aAspect->nAspectFilemtime = filemtime($aAspect->sAspectFilepath) ;
		}
		
		return $aAspect ;
	}
	
	static public function createAspectsFromCode($sSource,$sAspectName=null)
	{
		eval($sSource) ;
		
		$aClassCompiler = CompilerFactory::singleton()->create() ;
		$aAspectTokens = $aClassCompiler->scan( new InputStreamCache('<?php '.$sSource.' ?>') ) ;
		$aClassCompiler->interpret($aAspectTokens) ;
		
		if( $sAspectName===null )
		{
			$arrAspects = array() ;
			
			foreach($aAspectTokens->classIterator() as $aClassToken)
			{
				$arrAspects[] = self::createFromToken($aClassToken) ;
			}
			
			return $arrAspects ;
		}
		
		else
		{
			if( $aClassToken = $aAspectTokens->findClass($sAspectName) )
			{
				return self::createFromToken($aClassToken) ;
			}
			else
			{
				return null ;
			}
		}
	}
	
	public function addAdvice(Advice $aAdvice)
	{
		if(!$this->advices()->has($aAdvice))
		{
			$this->advices()->add($aAdvice) ;
		}
		
		foreach($aAdvice->forPointcuts() as $sPointcutName)
		{
			if( $aPointcut=$this->pointcuts()->getByName($sPointcutName) )
			{
				$aPointcut->advices()->add($aAdvice) ;
			}
		}
	}
		
	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function pointcuts()
	{
		if( !$this->aPointcuts )
		{
			$this->aPointcuts = new Container('org\\jecat\\framework\\lang\\aop\\Pointcut') ;
		}
		
		return $this->aPointcuts ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function advices()
	{
		if( !$this->aAdvices )
		{
			$this->aAdvices = new Container('org\\jecat\\framework\\lang\\aop\\Advice') ;
		}
	
		return $this->aAdvices ;
	}
	
	public function aspectName()
	{
		return $this->sAspectName ;
	}
	
	public function aspectFilepath()
	{
		return $this->sAspectFilepath ;
	}
	
	public function isValid()
	{
		if( $this->sAspectFilepath and $this->nAspectFilemtime )
		{
			return is_file($this->sAspectFilepath) and filemtime($this->sAspectFilepath) <= $this->nAspectFilemtime ;
		}
		return true ;
	}
	
	public function serialize ()
	{
		return serialize( array(
				'aPointcuts' => & $this->aPointcuts ,
				'aAdvices' => & $this->aAdvices ,
				'sAspectName' => & $this->sAspectName ,
				'sAspectFilepath' => & $this->sAspectFilepath ,
				'nAspectFilemtime' => & $this->nAspectFilemtime ,
		) ) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
	
		$this->aPointcuts =& $arrData['aPointcuts'] ;
		$this->aAdvices =& $arrData['aAdvices'] ;
		$this->sAspectName =& $arrData['sAspectName'] ;
		$this->sAspectFilepath =& $arrData['sAspectFilepath'] ;
		$this->nAspectFilemtime =& $arrData['nAspectFilemtime'] ;
		
		if($this->aPointcuts)
		{
			foreach($this->aPointcuts->iterator() as $aPointcut)
			{
				$aPointcut->setAspect($this) ;
			}
		}
		if($this->aAdvices)
		{
			foreach($this->aAdvices->iterator() as $aAdvice)
			{
				$aAdvice->setAspect($this) ;
				$this->addAdvice($aAdvice) ;
			}
		}
	}
	
	private $aPointcuts ;
	
	private $aAdvices ;
	
	private $sAspectName ;
	
	private $sAspectFilepath ;

	private $nAspectFilemtime = 0 ;
	
}

?>