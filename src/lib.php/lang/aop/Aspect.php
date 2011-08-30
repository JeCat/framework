<?php
namespace jc\lang\aop ;

use jc\lang\compile\DocComment;
use jc\pattern\composite\Container;
use jc\pattern\composite\NamedObject;

class Aspect extends NamedObject
{
	public function __construct($sAspectName)
	{
		// $this->classLoader()->searchClass($sAspectName) ;
		try {
			$aClassRef = new \ReflectionClass($sAspectName) ;
		}
		catch(\Exception $e)
		{
			throw new Exception("指定的class(%s)不存在",$sAspectName,$e) ;
		}
		
		foreach($aClassRef->getMethods() as $aMethodRef)
		{
			$aDoc = new DocComment( $aMethodRef->getDocComment() ) ;
			
			if( $aDoc->has('pointcut') )
		}
	}
	
	/**
	 * @return jc\pattern\IContainer
	 */
	public function pointcuts()
	{
		if( !$this->aPointcuts )
		{
			$this->aPointcuts = new Container('jc\\aop\\Pointcut') ;
		}
		
		return $this->aPointcuts ;
	}
	
	private $aPointcuts ;
	
}

?>