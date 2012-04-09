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
//  正在使用的这个版本是：0.7.1
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

use org\jecat\framework\lang\Type;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\fs\FSO;
use org\jecat\framework\io\InputStreamCache;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\compile\DocComment;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\pattern\composite\NamedObject;

class Aspect extends NamedObject implements \Serializable, IBean
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
			$aAspect->setAspectFilepath(FSO::tidyPath($sAspectFilepath)) ;
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
	public function setAspectFilepath($sFilepath)
	{
		$this->sAspectFilepath = $sFilepath ;
		$this->nAspectFilemtime = filemtime($sFilepath) ;
	}
	
	public function aspectFilemtime()
	{
		return $this->nAspectFilemtime ;
	}
	
	public function isValid()
	{
		if( $this->sAspectFilepath and $this->nAspectFilemtime )
		{
			if( !is_file($this->sAspectFilepath) or filemtime($this->sAspectFilepath)>$this->nAspectFilemtime )
			{
				return false ;
			}
		}
		
		foreach( $this->advices()->iterator() as $aAdvice )
		{
			if( !$aAdvice->isValid() )
			{
				return false ;
			}
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
	
	// IBean
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,BeanFactory $aBeanFactory=null)
	{
		$aBean = new self() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace) ;
		}
		return $aBean ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null)
	{
		$aPointcut = new Pointcut('default_pointcut') ;
		$aPointcut->setAspect($this) ;
		$this->pointcuts()->add($aPointcut) ;
		
		$nAdviceIdx = 0 ;
		foreach($arrConfig as $key=>&$item)
		{
			if(!is_int($key))
			{
				continue ;
			}
			// jointpoint
			if( is_string($item) )
			{
				foreach( array('JointPointMethodDefine','JointPointCallFunction','JointPointNewObject') as $sJointpointClass )
				{
					if( $aJointPoint=call_user_func(array(__NAMESPACE__.'\\jointpoint\\'.$sJointpointClass,'createFromDeclare'),$item) )
					{
						$aPointcut->jointPoints()->add($aJointPoint) ;
						$aJointPoint->setPointcut($aPointcut) ;
						break ;
					}
				}				
			}
			
			// advice
			else if( is_callable($item,true) )
			{
				// 源文
				$sSource = Type::reflectFunctionBody($item) ;
				$aRefFunc = is_array($item)? new \ReflectionMethod($item[0],$item[1]): new \ReflectionFunction($item) ;
				
				// 函数定义时声明的 access 和 static
				if( $aRefFunc instanceof \ReflectionMethod )
				{
					if( $aRefFunc->isPrivate() )
					{
						$sAccess = 'private' ;
					}
					else if( $aRefFunc->isProtected() )
					{
						$sAccess = 'protected' ;
					}
					else if( $aRefFunc->isPublic() )
					{
						$sAccess = 'public' ;
					}
				
					$bStatic = $aRefFunc->isStatic() ;					
					$sAdviceName = $aRefFunc->getDeclaringClass()->getName().'::'.$aRefFunc->getName() ;
				}
				else
				{
					$sAdviceName = 'nameless_' . $nAdviceIdx++ ;
					$sAccess = 'private' ;
					$bStatic = false ;
				}
								
				// 函数定义 注释中的 @use 和 @advice
				$arrUseDeclare = array() ;
				if( $sComment=$aRefFunc->getDocComment() )
				{
					$aDocComment = new DocComment($sComment) ;
					
					// @use
					$arrUseDeclare = $aDocComment->items('use')?: array() ;
					
					// @advice
					$sPosition = $aDocComment->item('advice')?: Advice::after ;
				}
				else
				{
					$sPosition = Advice::after ;
				}
				
				$aAdvice = new Advice($sAdviceName,$sSource,$sPosition) ;
				$aAdvice->addPointcutName($aPointcut->name()) ;
				$aAdvice->setDefineFile($aRefFunc->getFileName()) ;
				$aAdvice->setAccess($sAccess) ;
				$aAdvice->setStatic($bStatic) ;
				foreach($arrUseDeclare as &$sUseDclare)
				{
					$aAdvice->addUseDeclare($sUseDclare) ;
				}
				
				$aPointcut->advices()->add($aAdvice) ;
				$this->advices()->add($aAdvice) ;
				$aAdvice->setAspect($this) ;				
			}
		}		
		
		$this->arrBeanConfig =& $arrConfig ;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}
	
	private $aPointcuts ;
	
	private $aAdvices ;
	
	private $sAspectName ;
	
	private $sAspectFilepath ;

	private $nAspectFilemtime = 0 ;
	
}
