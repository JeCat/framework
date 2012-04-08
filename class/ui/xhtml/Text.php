<?php

namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\pattern\composite\IContainedable;

class Text extends ObjectBase
{
	public function source()
	{
		// 作为聚合对象时，拼接子对象
		if( $this->count() )
		{
			$sSource = '' ;
			foreach($this->iterator() as $aChild)
			{
				$sSource.= $aChild->source() ;
			}
			return $sSource ;
		}
			
		else 
		{
			return parent::source() ;
		}
	}
	
	public function separateChildren()
	{
		if( !$sSource=parent::source() or !$this->count() )
		{
			return ;
		}
		
		$arrNewChildren = array() ;
		
		$nIdx = $this->position() ;
		foreach($this->iterator() as $aChild)
		{
			$nLen = $aChild->position() - $nIdx ;
			if($nLen)
			{
				$arrNewChildren[] = new Text(
						$nIdx
						, $nIdx+$nLen-1
						, $this->line()
						, substr($sSource, $nIdx-$this->position(), $nLen)
				) ;
			}
			
			$arrNewChildren[] = $aChild ;
			$nIdx = $aChild->endPosition() + 1 ;
			
			$this->remove($aChild) ;
		}
		
		if( $this->endPosition()>=$nIdx )
		{
			$arrNewChildren[] = new Text(
					$nIdx
					, $this->endPosition()
					, $this->line()
					, substr($sSource, $nIdx-$this->position())
			) ;
		}
		
		foreach($arrNewChildren as $aChild)
		{
			$this->add($aChild) ;
		}
		
		$this->setSource('') ;
	}
}

?>