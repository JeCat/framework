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
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\io\OutputStreamBuffer;

use org\jecat\framework\mvc\view\ViewAssembler;

use org\jecat\framework\mvc\view\ViewAssemblySlot;

use org\jecat\framework\util\EventManager;

use org\jecat\framework\mvc\view\View;
use org\jecat\framework\system\ApplicationFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\controller\Controller;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\io\PrintStream;
use org\jecat\framework\lang\Object;

class Response extends Object
{
	const beforeRespond = 'beforeRespond' ;
	const afterRespond = 'afterRespond' ;
	const afterRenderViews = 'afterRenderViews' ;
	
	public function __construct(PrintStream $aDevice=null)
	{
		$this->aDevice = $aDevice ;
	}
	
	/**
	 * @wiki /MVC模式/控制器/请求-回应(request-response)
	 * 
	 * ===控制器 请求===
	 * TODO ...
	 * 
	 * ===控制器 响应===
	 * 控制器的执行结果如何提供给控制器的客户（(^)控制器的客户(client)既可以是系统，也可以是用户），由[b]响应(Response)[/b]对像负责。
	 * 
	 * 在目前框架的设计中，控制器主要有三种”内容“可供输出：
	 * * 控制器的消息队列
	 * * 控制器的视图
	 * * 控制器“放置”到[b]响应(Response)[/b]对像中的变量
	 * 由[b]响应(Response)[/b]对像来决定向什么设备输出这些内容输出哪些内容。
	 * 
	 * [^]\
	 * 在默认情况下，控制器会向系统提供一个 org\jecat\framework\mvc\controller\Response 对像做为[b]响应[/b]对像，它会将默认的输出管道做为控制器的输出设备；\
	 * 并根据使用[b]请求(Request)[/b]对像中的参数决定输出策略。
	 * 你也可以给控制器设置完全不同的[b]响应[/b]对像。\
	 * [/^]
	 * 
	 * 
	 * Controller类默认提供的Response对像，会根据[b]请求(Request)[/b]对像中的一些特殊参数来输出控制器的执行结果：
	 * 
	 * 
	 * =参数 rspn=
	 * rspn 参数指定了控制器向[b]响应(response)[/b]对像的输出管道，以何种形式输出何种内容。rspn参数可以是以下值：
	 * * rspn=[b]msgqueue.json[/b]
	 * 以json格式输出控制器消息队列中的内容
	 * * rspn=[b]msgqueue.xml[/b]
	 * 以xml格式输出控制器消息队列中的内容
	 * * rspn=[b]msgqueue.html[/b]
	 * 以html格式输出控制器消息队列中的内容
	 * * rspn=[b]msgqueue[/b]
	 * (和 msgqueue.html 相同)
	 * * rspn=[b]var.php[/b]
	 * 以php语法格式输出控制器执行后的所有结果变量					
	 * * rspn=[b]var.xml[/b]
	 * 以xml格式输出控制器执行后的所有结果变量
	 * * rspn=[b]var.json[/b]			
	 * 以json格式输出控制器执行后的所有结果变量
	 * * rspn=[b]var[/b]
	 * (和 var.json 相同)
	 * * rspn=[b]view.noframe[/b]			
	 * 禁止显示控制器 frame 部分的视图
	 * * rspn=[b]noframe[/b]			
	 * (和 view.noframe 相同)
	 * * rspn=[b]view.inframe （默认）[/b]			
	 * 在 控制器提供的frame的视图 中显示控制器的视图，这是 rspn参数的缺省值
	 * * rspn=[b]inframe[/b]			
	 * (和 view.inframe 相同)
	 * * rspn=[b]view （默认）[/b]			
	 * (和 view.inframe 相同)
	 * * rspn=[b]disable[/b]			
	 * 禁止输出任何内容（但不会禁止 rspn.debug.* 相关的内容）
	 * 
	 * [^]如果控制器中没有视图(或视图都被禁用)，在 view.* 模式下，系统会输出控制器的消息队列[/^]
	 * 
	 * 
	 * ==调式相关参数==
	 * 还有一些用于调式的响应(Response)参数。
	 * 
	 * =参数 rspn.debug.db.log=
	 * 打印整个系统在执行过程中，数据库执行SQL的情况；只要提供这个参数，等于任何值都有效。
	 * 
	 * =参数 rspn.debug.model.struct=
	 * 打印控制器的模型结构和数据内容。
	 * 该参数可以是一个表示指定模型的名称的字符串，或表示所有模型的”星号“（*）
	 * 
	 */
	public function respond(Controller $aController)
	{
		$aEventManager = EventManager::singleton() ;
		
		// 触发事件
		$arrEventArgvs = array($this,$aController) ;
		$aEventManager->emitEvent(__CLASS__,self::beforeRespond,$arrEventArgvs) ;
		
		
		switch ($aController->params()->get('rspn'))
		{
		// msgqueue ------------
		case 'msgqueue.json' :
			break ;
			
		case 'msgqueue.xml' :
			break ;
			
		case 'msgqueue' :
		case 'msgqueue.html' :
			
			$aController->messageQueue()->display(null,$this->device()) ;
			
			break ;
			
		// var ------------
		case 'var' :
		case 'var.json' :
			$this->device()->write(json_encode($this->arrReturnVariables)) ;
			
			break ;
			
		case 'var.xml' :
			break ;
			
		case 'var.php' :
			$this->device()->write(var_export($this->arrReturnVariables,true)) ;
			break ;
			
		// view ------------	
		default :
		case 'view' :
		case 'view.inframe' :
			if( $aFrame = $aController->frame() )
			{
				$aMainView = $aFrame->mainView() ;
			}
			// 没有 break ，进入下面的 case 
		case 'noframe' :
		case 'view.noframe' :
			
			if(empty($aMainView))
			{
				$aMainView = $aController->mainView() ;
			}
			
			// 控制器没有有效视图
			$nValidViews = 0 ;
			foreach($aController->viewIterator() as $aView)
			{
				if($aView->isEnable())
				{
					$nValidViews ++ ;
				}
			}
			if(!$nValidViews)
			{
				// 临时提供一个仅显示消息队列的视图
				$aTmpView = new View('tmp_view_for_msgqueue') ;
				$aController->addView($aTmpView) ;
				$aController->messageQueue()->display(null,$this->device()) ;
			}
			
			// 装配视图
			ViewAssembler::singleton()->assemble($aController) ;
			
			// 显示视图
			$aOutput = new OutputStreamBuffer() ;
			$aMainView->render($aOutput) ;
			$this->device()->write($aOutput) ;

			// 触发事件
			$arrEventArgvs2 = array($this,$aMainView,$aController) ;
			$aEventManager->emitEvent(__CLASS__,self::afterRenderViews,$arrEventArgvs2) ;
			
			break ;
			
		case 'disable' :
			// nothing todo
			break ;
		}
		
		// 打印数据库的执行日志
		if( $aController->params()->has('rspn.debug.db.log') )
		{
			$this->device()->write( '<hr /><h3>数据库执行记录：</h3>' ) ;
			
			// 按执行时间排序
			$arrLogs = DB::singleton()->executeLog(false) ;
			foreach($arrLogs as $nIdx=>&$arrOneLog)
			{
				$arrOneLog['idx'] = $nIdx ;
			}
			usort($arrLogs,function($a,$b){
				if($a['time']==$b['time'])
				{
					return 0 ; 
				}
				return $a['time']<$b['time'] ? -1: 1 ;
			}) ;
			
			$fTotal = 0 ;
			foreach($arrLogs as $arrLog)
			{
				$fTotal += $arrLog['time'] ;
				$this->device()->write( "<div style='padding-top:10px'>[{$arrLog['idx']}]=>耗时:{$arrLog['time']} <pre>{$arrLog['sql']}</pre></div>" ) ;
			}
			$this->device()->write( "\r\n<br />DB共执行了 ".count($arrLogs)." 条SQL，总计时间：{$fTotal}\r\n<hr />" ) ;
			
		}
		
		// 打印模型结构
		if( $aController->params()->has('rspn.debug.model.struct') )
		{
			$sModelName = $aController->params()->get('rspn.debug.model.struct') ;
			
			$arrModelNames = array() ;
			if($sModelName=='*')
			{
				// 控制器自己的模型
				foreach($aController->modelNameIterator() as $sModelName)
				{
					$this->device()->write( '<hr /><h3>控制器'.$aController->name().'的模型数据结构：</h3>' ) ;
					$this->printDebugModelStruct($aController,$sModelName) ;
				}
				
				// 子控制器的模型
				foreach($aController->iterator() as $aChildController)
				{
					$this->device()->write( '<hr /><h3>控制器'.$aChildController->name().'的模型数据结构：</h3>' ) ;
					foreach($aChildController->modelNameIterator() as $sModelName)
					{
						$this->printDebugModelStruct($aChildController,$sModelName) ;
					}
				}
			}
			else
			{
				$this->device()->write( '<hr /><h3>控制器'.$aController->name().'的模型数据结构：</h3>' ) ;
				$this->printDebugModelStruct($aController,$sModelName) ;
			}
		}
		
		// 触发事件
		$aEventManager->emitEvent(__CLASS__,self::afterRespond,$arrEventArgvs) ;
	}
	
	private function printDebugModelStruct(Controller $aController,$sModelName)
	{
		$this->device()->write( "<div style='padding-top:10px'><h4>[模型：{$sModelName}]</h4>" ) ;
		if( $aModel=$aController->modelByName($sModelName) )
		{
			$this->device()->write( "<pre>" ) ;
			$aModel->printStruct($this->device()) ;
			$this->device()->write( "</pre>" ) ;
		}
		else
		{
			$this->device()->write( "模型名称{$sModelName}无效" ) ;
		}
		$this->device()->write( "</div>" ) ;		
	}
	
	// ------------------------
	public function putReturnVariable($aVar,$key=null)
	{
		if($key===null)
		{
			$this->arrReturnVariables[] =& $aVar ;
		}
		else
		{
			$this->arrReturnVariables[$key] =& $aVar ;
		}
	}
	public function returnVariable($key=null)
	{
		
	}
	public function removeReturnVariable($key)
	{
		
	}
	public function clearReturnVariables()
	{
		$this->arrReturnVariables = null ;
	}
	public function returnVariableKeyIterator()
	{
		
	}
	
	// ------------------------
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\io\PrintSteam
	 */
	public function device($bAutoCreate=true)
	{
		if( !$this->aDevice and $bAutoCreate )
		{
			$this->aDevice = ApplicationFactory::singleton()->createResponseDevice() ;
		}
		return $this->aDevice ;
	}
	
	public function setdevice(PrintStream $aDevice)
	{
		$this->aDevice = $aDevice ;
	}

	public function output($sBytes)
	{
		if( $aFilters = $this->filters() )
		{
			list($sBytes) = $aFilters->handle($sBytes) ;
		}
		
		$this->aDevice->println($sBytes) ;
	}
	
	/**
	 * @return IFilterMangeger
	 */
	public function filters()
	{
		return $this->aFilters ;
	}
	
	public function setFilters(IFilterMangeger $aFilters)
	{
		$this->aFilters = $aFilters ;
	}
	
	/**
	 * alias for device
	 * @return org\jecat\framework\io\IOutputStream
	 */
	public function printer()
	{
		return $this->aDevice ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aDevice ;
	
	private $aFilters ;
	
	private $arrReturnVariables ;
}



