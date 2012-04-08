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

use org\jecat\framework\io\IRedirectableStream;
use org\jecat\framework\io\OutputStreamBuffer;

class ViewAssemblySlot extends OutputStreamBuffer implements \Serializable
{
	const soft = 3 ;
	const hard = 5 ;
	const xhard = 7 ;
	
	const container_use_controller = 'controller' ;
	const container_use_view = 'view' ;
	
	const layout_vertical = 'v' ;
	const layout_horizontal = 'h' ;
	// const layout_tab = 'tab' ;
	
	static private $arrModeName = array(
			'soft' => self::soft ,
			'hard' => self::hard ,
			'xhard' => self::xhard ,
	) ;
			
	public function __construct($priority=self::soft,array $arrXPaths=null,$sLayout=self::layout_vertical,$sContainerFor=self::container_use_controller)
	{
		if( isset(self::$arrModeName[$priority]) )
		{
			$this->nPriority = self::$arrModeName[$priority] ;
		}
		else
		{
			$this->nPriority = (int) $priority ;
		}
		
		$this->arrXPaths = $arrXPaths ;
		$this->sContainerFor = $sContainerFor ;
	}
	
	/**
	 * 装配视图，将视图渲染后的buffer装配起来
	 * @see org\jecat\framework\mvc\view.IView::assembly()
	 */
	static public function assembly(IView $aParentView)
	{
		if( !$aOutputBuffer = $aParentView->outputStream(false) )
		{
			return ;
		}
		
		$arrRawData =& $aOutputBuffer->bufferRawDatas() ;
		foreach($arrRawData as &$data)
		{
			if( $data instanceof ViewAssemblySlot )
			{
				$data->pullinViews($aParentView) ;
			}
		}
	}
	
	public function pullinViews(IView $aView)
	{
		if($this->arrXPaths)
		{
			if( $this->sContainerFor==self::container_use_controller )
			{
				// 视图还没有添加给控制器
				if( !$aView->controller() )
				{
					return ;
				}
				$aViewContainer = $aView->controller()->mainView() ;
			}
			else if( $this->sContainerFor==self::container_use_view )
			{
				$aViewContainer = $aView ;
			}
			else
			{
				$this->write("<view>标签遇到无效的container类型：".$this->sContainerFor) ;
				return ;
			}
			
			// 找指定 xpath 的view
			foreach($this->arrXPaths as $sXPath)
			{
				if( $aFoundView=View::xpath($aViewContainer,$sXPath) )
				{
					$this->pullinOneView($aFoundView) ;
				}
			}
		}
		else
		{
			// 找 view container 所有的view
			foreach($aView->iterator() as $aView)
			{
				$this->pullinOneView($aView) ;
			}
		}
	}
	
	private function pullinOneView(IView $aView)
	{
		// 视图已经渲染
		if( ($aOutputBuffer=$aView->outputStream(false)) instanceof IRedirectableStream )
		{
			// 已经被装配到一个 ViewAssemblySlot 中了
			if( ($aRedirectionDev=$aOutputBuffer->redirectionDev()) instanceof ViewAssemblySlot )
			{
				// 比较两个 slot 的优先级
				if( $this->priority() >$aRedirectionDev->priority() )
				{
					$aOutputBuffer->redirect($this) ;
				}
			}
			// 未被装配的流浪视图
			else
			{
				$aOutputBuffer->redirect($this) ;
				
				// 装配这个视图的buffer
				self::assembly($aView) ;
			}
		}		
	}
	
	public function write($content,$nLen=null,$bFlush=false)
	{
		if( $content instanceof OutputStreamBuffer
				and $aProperties=$content->properties(true)
				and $aView=$aProperties->get('_view')
				and $aView instanceof IView )
		{
			$this->arrBuffer[ $aView->id() ] = $content ;
		}
		else
		{
			return parent::write($content,$nLen,$bFlush) ;
		}
	}
	
	public function bufferBytes($bClear=true)
	{
		$sBytes = '' ;
	
		if(!empty($this->arrBuffer))
		{
			$sBytes.= '<div class="jc-view-layout-frame">' ;
		
			foreach ($this->arrBuffer as $sViewId=>$contents)
			{
				if($contents instanceof OutputStreamBuffer)
				{
					// id
					$sViewId = 'layout-item-' . $sViewId ;
					// class/style
					$sClass = ($contents instanceof self)? 'jc-view-layout-frame': 'jc-view-layout-item' ;
					$sStyle = $this->sLayout==self::layout_horizontal? " style='float:left;'": null ;
					
					$sBytes.= "<div id='{$sViewId}' class='{$sClass}'{$sStyle}>" ;
					$sBytes.= strval($contents) ;
					$sBytes.= '</div>' ;
				}
				
				else
				{
					$sBytes.= strval($contents) ;
				}
			}
			
			$sBytes.= '<div class="jc-view-layout-end-item"></div></div>' ;
		}
	
		if($bClear)
		{
			$this->clear() ;
		}
	
		return $sBytes ;
	}
	
	public function priority()
	{
		return $this->nPriority ;
	}
	
	public function layout()
	{
		return $this->sLayout ;
	}
	
	public function serialize()
	{
		$arrData['arrXPaths'] =& $this->arrXPaths ;
		$arrData['nPriority'] =& $this->nPriority ;
	
		return serialize($arrData) ;
	}
	public function unserialize($sData)
	{
		$arrData = unserialize($sData) ;
		
		$this->arrXPaths =& $arrData['arrXPaths'] ;
		$this->nPriority =& $arrData['nPriority'] ;
	}
	
	private $arrXPaths ;
	
	private $sContainerFor ;
	
	private $nPriority = 0 ;
	
	private $sLayout ;	
	
}


