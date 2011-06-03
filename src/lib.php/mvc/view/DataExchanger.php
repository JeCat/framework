<?php
namespace jc\mvc\view ;

use jc\lang\Exception;
use jc\lang\Object;

class DataExchanger
{
	const MODEL_TO_WIDGET = 1 ;
	const WIDGET_TO_MODEL = 2 ;
	

	public function link($sWidgetId,$sModelName)
	{
		if( !isset($this->arrLinks[$sWidgetId]) )
		{
			$this->arrLinks[$sWidgetId] = array() ;
		}
		
		if( !in_array($sModelName,$this->arrLinks[$sWidgetId]) )
		{
			$this->arrLinks[$sWidgetId][] = $sModelName ;
		}
	}
	
	public function unlink($sWidgetId,$sModelName=null)
	{
		if( !isset($this->arrLinks[$sWidgetId]) )
		{
			return ;
		}
		
		if($sModelName===null)
		{
			unset($this->arrLinks[$sWidgetId]) ;
		}
		else 
		{
			unset($this->arrLinks[$sWidgetId][$sModelName]) ;
		}
	}

	public function exchange(IView $aView,$nWay=self::MODEL_TO_WIDGET)
	{
		if( !$aView->model() )
		{
			throw new Exception("视图尚未设置模型，无法进行数据交换") ;
		}
		
		foreach(array_keys($this->arrLinks) as $sWidgetId)
		{
			$this->exchangeWidget($aView,$sWidgetId,$nWay) ;
		}
	}
	
	public function exchangeWidget(IView $aView,$sWidgetId,$nWay=self::MODEL_TO_WIDGET)
	{
		if( !isset($this->arrLinks[$sWidgetId]) )
		{
			return ;
		}
		
		if( !$aModel=$aView->model() )
		{
			throw new Exception("视图尚未设置模型，无法进行数据交换") ;
		}
		
		if( !$aWidget=$aView->widget($sWidgetId) )
		{
			throw new Exception("视图中缺少指定的窗体（%s），无法进行数据交换",$sWidgetId) ;
		}
		
		switch ($nWay)
		{
			// 从模型数据到ui窗体
			case self::MODEL_TO_WIDGET :
				
				$sModelName = end($this->arrLinks[$sWidgetId]) ;
				$aWidget->setValue( $aModel->data($sModelName) ) ;
				
				break ;

			// 从ui窗体到模型控件
			case self::WIDGET_TO_MODEL :
				
				$widgetVal = $aWidget->value() ;
				foreach($this->arrLinks[$sWidgetId] as $sModelName)
				{
					$aModel->setData($sModelName,$widgetVal) ;
				}
				
				break ;
				
			default: 
				throw new Exception("参数（\$nWay）无效") ;
				break ;
		}
	}
	
	
	private $arrLinks = array() ;
}

?>