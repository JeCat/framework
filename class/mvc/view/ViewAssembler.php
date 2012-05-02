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

use org\jecat\framework\system\Application;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class ViewAssembler extends Object
{
	const weak = 1 ;
	const soft = 3 ;
	const hard = 5 ;
	const xhard = 7 ;
	static private $mapModeName = array(
			'weak' => self::weak ,
			'soft' => self::soft ,
			'hard' => self::hard ,
			'xhard' => self::xhard ,
	) ;
	
	const layout_vertical = 'v' ;
	const layout_horizontal = 'h' ;
	static private $mapLayoutItemStyles = array(
			self::layout_vertical => 'jc-layout-item-vertical' ,
			self::layout_horizontal => 'jc-layout-item-horizontal' ,
	) ;
	static private $mapLayoutFrameStyles = array(
			self::layout_vertical => 'jc-frame-vertical' ,
			self::layout_horizontal => 'jc-frame-horizontal' ,
	) ;

	const container_use_controller = 'controller' ;
	const container_use_view = 'view' ;
	
	/////////////
	public function assemble()
	{
		foreach( $this->arrAssemblyListRepos as $sId=>&$arrAssemblyList )
		{
			if( empty($arrAssemblyList['filter']) )
			{
				continue ;
			}
			if( empty($arrAssemblyList['filter']['view']) )
			{
				throw new Exception('视图装配单缺少 view 信息。') ;
			}
			
			// echo "<br />\r\nassemble list: '", $sId, "'----------<br />\r\n" ;
						
			if($arrAssemblyList['filter']['xpaths'])
			{
				// 使用 xpath ， 从视图所属的控制器开始
				if( !$aController=$arrAssemblyList['filter']['view']->controller() )
				{
					throw new Exception('视图尚未加入控制器，无法通过 xpath 查找其它视图。') ;
				}
				$aViewContainer = $aController->mainView() ;
				
				// 找指定 xpath 的view
				foreach($arrAssemblyList['filter']['xpaths'] as $sXPath)
				{
					if( $aFoundView=View::findXPath($aViewContainer,$sXPath) )
					{
						// echo 'found view by xpath : ' , $sXPath, "<br />\r\n" ;
						$this->assembleView($arrAssemblyList,$aFoundView) ;
					}
				}
			}
			else
			{
				// 找 view 的所有下级视图
				foreach($arrAssemblyList['filter']['view']->iterator() as $aView)
				{
					if( $aView->parent() === $arrAssemblyList['filter']['view'] )
					{
						// echo 'found child view : ' , $aView->xpath(), "<br />\r\n" ;
						$this->assembleView($arrAssemblyList,$aView) ;
					}
				}
			}
			
			// 用完了
			unset($arrAssemblyList['filter']) ;
		}
		
		// echo '<pre>' ;
		/*$arrAssemblyListRepos = $this->arrAssemblyListRepos ;
		foreach( $arrAssemblyListRepos as $sId=>&$arrAssemblyList )
		{
			if(empty($arrAssemblyList['items']))
			{
				//unset($arrAssemblyListRepos[$sId]) ;
			}
			else
			{
				foreach($arrAssemblyList['items'] as &$arrItem)
				{
					unset($arrItem['object']) ;
				}
			}
		}
		print_r($arrAssemblyListRepos) ;*/
		// echo '</pre>' ;
	}
	
	private function assembleView(& $arrAssemblyList, IView $aView)
	{
		if( !$sViewId = $aView->id() )
		{
			throw new Exception("在装配视图时，遇到未注册的视图。") ;
		}
		
		// 视图已经装配过
		if( isset($this->arrViewAssemblyRecords[$sViewId]) )
		{
			// echo "view {$sViewId} has assembled, pre priority: {$this->arrViewAssemblyRecords[$sViewId]['priority']}, this time priority: {$arrAssemblyList['filter']['priority']} <br />\r\n" ;
			
			// 比较两个 slot 的优先级
			if( $arrAssemblyList['filter']['priority'] > $this->arrViewAssemblyRecords[$sViewId]['priority'] )
			{
				// echo "remove {$sViewId} --- <<< {$this->arrViewAssemblyRecords[$sViewId]['list']['id']} @ {$this->arrViewAssemblyRecords[$sViewId]['listpos']} <br />" ;
				
				// 从原装配单中清除
				unset( $this->arrViewAssemblyRecords[$sViewId]['list']['items'][ $this->arrViewAssemblyRecords[$sViewId]['listpos'] ] ) ;
				unset( $this->arrViewAssemblyRecords[$sViewId] ) ;
				
			}
			
			else
			{
				return ;
			}
		}
		
		// 写入装配单
		$arrAssemblyList['items'][] = array(
				'type' => 'view' ,
				'id' => $sViewId ,
				'object' => $aView ,
		) ;
		
		// 记录装配状态
		end($arrAssemblyList['items']) ;
		$nPos = key($arrAssemblyList['items']) ;
		$this->arrViewAssemblyRecords[$sViewId] = array(
				'list' => &$arrAssemblyList ,
				'listpos' => $nPos ,
				'priority' => $arrAssemblyList['filter']['priority'] ,
		) ;

		// echo "assembling view [ {$sViewId} +++ >>> {$arrAssemblyList['id']} @ {$nPos} ] as priority {$arrAssemblyList['filter']['priority']} <br />" ;
	}
	
	static public function filterModeToPriority($sMode)
	{
		return isset(self::$mapModeName[$sMode])? self::$mapModeName[$sMode]: self::soft ; 
	}
	
	public function defineAssemblyList($sId,$sLayout=self::layout_vertical,array $arrFilter=null)
	{
		if( isset($this->arrAssemblyListRepos[$sId]) )
		{
			throw new Exception("ID为 %s 的视图装配单已经存在，正在重复定义视图装配单",$sId) ;
		}
		
		$this->arrAssemblyListRepos[$sId] = array(
				'id' => $sId ,
				'type' => 'frame' ,
				'filter' => $arrFilter ,
				'layout' => $sLayout ,
				'items' => array() ,
		) ;
	}
	
	public function displayAssemblyList($sId,IOutputStream $aDevice)
	{
		if( !isset($this->arrAssemblyListRepos[$sId]) )
		{
			throw new Exception("根据装配单显示视图时遇到了不存在的装配单ID:%s",$sId) ;
		}
		
		$this->_displayAssemblyList($this->arrAssemblyListRepos[$sId],$aDevice) ;
	}
	
	private function _displayAssemblyList(array & $arrAssemblyList,IOutputStream $aDevice,$sParentFrameLayout=null)
	{
		if(empty($arrAssemblyList['items']))
		{
			return ;
		}
		
		$aDevice->write($this->htmlWrapper($arrAssemblyList,$sParentFrameLayout)) ;
		$aDebugging = Application::singleton()->isDebugging() ;
			
		foreach( $arrAssemblyList['items'] as &$arrAssemblyItem)
		{
			// 视图
			if( $arrAssemblyItem['type']==='view' )
			{
				if(empty($arrAssemblyItem['object']))
				{
					if( empty($arrAssemblyItem['id']) )
					{
						throw new Exception("视图类型的装配内容，缺少视图注册ID") ;	
					}
					
					if( !$arrAssemblyItem['object'] = View::findRegisteredView($arrAssemblyItem['id']) )
					{
						throw new Exception("在根据装配单输出视图时，无法根据提供的视图ID找到视图对像：%s，该视图可能不存在或未注册。",$arrAssemblyItem['id']) ;	
					}
				}

				$bEmptyView = $arrAssemblyItem['object']->template()? false: true ;
				if( !$bEmptyView )
				{
					$aDevice->write($this->htmlWrapper($arrAssemblyItem,$arrAssemblyList['layout'])) ;
					if($aDebugging)
					{
						$aDevice->write("<!-- view name: ".$arrAssemblyItem['object']->name()." -->\r\n") ;
					}
				}
				
				$arrAssemblyItem['object']->render($aDevice) ;
				
				if( !$bEmptyView )
				{
					$aDevice->write("</div>\r\n") ;
				}
			}
	
			// 另一个 装配单
			else if( $arrAssemblyItem['type']==='frame' )
			{
				$this->_displayAssemblyList($arrAssemblyItem,$aDevice,$arrAssemblyList['layout']) ;
			}
			
			else 
			{
				throw new Exception("无效的装配内容类型：%s",$arrAssemblyItem['type']) ;	
			}
		}
		
		$aDevice->write("<div class='jc-layout-item-end'></div></div>\r\n") ;
	}
	
	public function htmlWrapper(array $arrAssemblyItem,$sLayout=null)
	{
		if(empty($arrAssemblyItem['classes']))
		{
			$arrAssemblyItem['classes'] = array() ;
		}
		if(empty($arrAssemblyItem['styles']))
		{
			$arrAssemblyItem['styles'] = array() ;
		}
		
		$arrAssemblyItem['classes'][] = 'jc-layout' ;
		if($sLayout)
		{
			$arrAssemblyItem['classes'][] = self::$mapLayoutItemStyles[$sLayout] ;
		}
		
		if( $arrAssemblyItem['type']==='frame' )
		{
			$arrAssemblyItem['classes'][] = 'jc-frame' ;
			$arrAssemblyItem['classes'][] = self::$mapLayoutFrameStyles[$arrAssemblyItem['layout']] ;
		}
		else 
		{
			$arrAssemblyItem['classes'][] = 'jc-view' ;
		}
		
		$sClasses = implode(' ',$arrAssemblyItem['classes']) ;
		$sStyles = implode(' ',$arrAssemblyItem['styles']) ;
		
		return "<div id=\"{$arrAssemblyItem['id']}\" class=\"$sClasses\" style=\"$sStyles\">\r\n" ;
	}

	/**
	 * array(
	 *		'id' => 'xxx' ,
	 *		'type' => 'frame/view' ,
	 *		'filter' => array(
	 *			'view' => '' ,
	 *			'xpaths' => array() ,
	 *		) ,
	 *		'layout' => 'v/h' ,
	 *		'items' => array() ,
	 *		'object' => aView ,
	 * )
	 */
	private $arrAssemblyListRepos = array() ;
	
	private $arrViewAssemblyRecords = array() ;
	
	
}


