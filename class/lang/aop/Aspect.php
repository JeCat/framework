<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\io\InputStreamCache;

use org\jecat\framework\lang\compile\CompilerFactory;

use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\DocComment;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\pattern\composite\NamedObject;

class Aspect extends NamedObject
{	
	static public function createFromToken(ClassDefine $aClassToken)
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
				$aAdvice = Advice::createFromToken($aMethodToken) ;
				
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
					
					$aPointcut->advices()->add($aAdvice) ;
				}
			}
		}
		
		$aAspect->sAspectName = $sAspectName ;
		
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
	
	public function aspectName()
	{
		return $this->sAspectName ;
	}
	
	private $aPointcuts ;
	
	private $sAspectName ;
	
}

?>