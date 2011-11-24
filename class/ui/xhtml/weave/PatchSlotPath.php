<?php
namespace jc\ui\xhtml\weave ;

use jc\lang\Exception;

use jc\ui\ObjectContainer;

class PatchSlotPath
{
	private public function __construct()
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
	 * @return jc\ui\xhtml\ObjectBase
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

	
	private $arrSegments = array() ;
}

?>