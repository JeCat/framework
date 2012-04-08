<?php
namespace org\jecat\framework\mvc\view\layout ;

use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\io\OutputStreamBuffer;

class ViewLayoutItemDevice extends OutputStreamBuffer
{
	public function __construct(ViewLayoutFrame $aFrame,IView $aItem)
	{
		$this->aFrame = $aFrame ;
		$this->aItem = $aItem ;
		$aItem->outputStream()->redirect($this) ;
	}

	public function bufferBytes($bClear=true)
	{
		if( empty($this->arrBuffer) )
		{
			return '' ;
		}
		else
		{			
			$sBuffBytes = ViewLayoutFrame::renderWrapperHeader(
					$this->aItem
					, ($this->aItem instanceof ViewLayoutFrame)? 'jc-view-layout-frame': 'jc-view-layout-item'
					, $this->aFrame->type()==ViewLayoutFrame::type_horizontal? 'float:left;': null
			) ;
		
			$sBuffBytes.= parent::bufferBytes($bClear) ;
			
			$sBuffBytes.= "</div>" ;
			
			return $sBuffBytes ;
		}
	}

	
	
	private $aFrame ;
	private $aItem ;
	private $aItemDevice ;
}
