<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\ui\IObject;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\ui\ObjectContainer;

class PatchSlotPath
{
	private function __construct()
	{}
	
	/**
	 * @return PatchSlotPath
	 */
	static public function parsePath($sPath)
	{
		$aPath = new self() ;
		
		$arrSegments = explode('/',$sPath) ;
		foreach( $arrSegments as $sSegment )
		{
			$sSegment = trim($sSegment) ;
			
			if( empty($sSegment) )
			{
				continue ;
			}
			
			$aPath->arrSegments[] = PatchSlotPathSegment::parseSegment($sSegment) ;
		}
		
		return $aPath ;
	}
	
	/**
	 * @return org\jecat\framework\ui\xhtml\ObjectBase
	 */
	public function localObject(ObjectContainer $aObjectContainer)
	{
		$aObject = $aObjectContainer ;
		$arrProcessedSegments = array() ;
		
		foreach($this->arrSegments as $aSegment)
		{
			$aObject = $aSegment->localObject($aObject) ;
			$arrProcessedSegments[] = $aSegment ;
			
			if(!$aObject)
			{
				$sProcessedSegments = '/'.implode("/", $arrProcessedSegments) ;
				throw new Exception(
						"无法根据路径 %s(%s) 找到对应的对象"
						, array(
							$this->__toString() ,
							$sProcessedSegments ,
						)
				) ;
			}
		}
		
		return $aObject ;
	}
	
	public function __toString()
	{
		return '/' . implode('/', $this->arrSegments) ;
	}

	static public function reflectXPath(IObject $aParentObject,$sParentXPath='')
	{
		$arrChildIdxies = array() ;
		echo __METHOD__ ;
		foreach($aParentObject->iterator() as $aChildObject)
		{
			echo $sType = PatchSlotPathSegment::xpathType($aChildObject) ;
			if( !isset($arrChildIdxies[$sType]) )
			{
				$arrChildIdxies[$sType] = 0 ;
			}echo $arrChildIdxies[$sType] ;
				
			$sXPath = $sParentXPath.'/'.$sType.'@'.($arrChildIdxies[$sType]++) ;
			$aChildObject->properties()->set('xpath',$sXPath) ;
			
			self::reflectXPath($aChildObject,$sXPath) ;
		}
	}
	
	private $arrSegments = array() ;
}

?>