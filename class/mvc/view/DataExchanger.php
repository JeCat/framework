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
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\lang\Exception;

/**
 * @wiki /MVC模式/数据交换和数据校验/数据交换
 * ==数据交换(Data Exchage)==
 * JeCat 提供一种机制，用于模型和视图窗体之间的数据自动交换。
 * 1.MODEL_TO_WIDGET:从模型数据到ui窗体
 * 2.WIDGET_TO_MODEL：从ui窗体到模型控件
 * 3.WIDGET和MODEL之间的数据交换的设置
 * 	*设置widget的属性exchange,属性值为Model所对应的数据表的字段
 * 	*调用数据的交换方法exchangeData,方法的参数有MODEL_TO_WIDGET和WIDGET_TO_MODEL两种
 * @author qusong
 *
 */

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
				$aWidget->setValueFromString( $aModel->data($sModelName) ) ;
				
				break ;

			// 从ui窗体到模型控件
			case self::WIDGET_TO_MODEL :
				
				$widgetVal = $aWidget->valueToString() ;
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

