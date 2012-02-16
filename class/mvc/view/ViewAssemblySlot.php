<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\io\IOutputStream;

use org\jecat\framework\io\IRedirectableStream;
use org\jecat\framework\io\OutputStreamBuffer;

class ViewAssemblySlot extends OutputStreamBuffer implements \Serializable
{
	const soft = 3 ;
	const hard = 5 ;
	const xhard = 7 ;
	
	const container_use_controller = 'controller' ;
	const container_use_view = 'view' ;
	
	static private $arrModeName = array(
			'soft' => self::soft ,
			'hard' => self::hard ,
			'xhard' => self::xhard ,
	) ;
			
	public function __construct($priority=self::soft,array $arrXPaths=null,$nContainerFor=self::container_use_controller)
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
		$this->nContainerFor = $nContainerFor ;
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
			if( $this->nContainerFor==self::container_use_controller )
			{
				// 视图还没有添加给控制器
				if( !$aView->controller() )
				{
					return ;
				}
				$aViewContainer = $aView->controller()->mainView() ;
			}
			else if( $this->nContainerFor==self::container_use_view )
			{
				$aViewContainer = $aView ;
			}
			else
			{
				$this->write("<view>标签遇到无效的container类型：".$this->nContainerFor) ;
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
	
	public function priority()
	{
		return $this->nPriority ;
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
	
	private $nContainerFor ;
	
	private $nPriority = 0 ;
	
}
