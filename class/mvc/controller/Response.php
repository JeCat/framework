<?php
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\pattern\composite\IContainer;

use org\jecat\framework\mvc\model\IModel;

use org\jecat\framework\db\DB;

use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\io\PrintStream;
use org\jecat\framework\lang\Object ;

class Response extends Object
{	
	public function __construct(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}
	
	/**
	 * @wiki /MVC模式/请求-响应
	 * 
	 * === 控制器 请求 ===
	 * TODO ...
	 * 
	 * === 控制器 响应 ===
	 * 控制器的执行结果如何提供给控制器的客户（(^)控制器的客户(client)既可以是系统，也可以是用户），由[b]响应(Response)[/b]对像负责。
	 * 
	 * 在控制器的[b]请求(request)[/b]中有一些影响[b]响应(response)[/b]的特殊参数：
	 * 
	 * = 参数 rspn =
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
	 * 
	 * 
	 * == 调式相关参数 ==
	 * 还有一些用于调式的响应(Response)参数。
	 * 
	 * = 参数 rspn.debug.db.log =
	 * 打印整个系统在执行过程中，数据库执行SQL的情况；只要提供这个参数，等于任何值都有效。
	 * 
	 * = 参数 rspn.debug.model.struct =
	 * 打印控制器的模型结构和数据内容。
	 * 该参数可以是一个表示指定模型的名称的字符串，或表示所有模型的”星号“（*）
	 */
	public function process(IController $aController)
	{
		switch ($aController->params()->get('rspn'))
		{
		// msgqueue ------------
		case 'msgqueue.json' :
			break ;
			
		case 'msgqueue.xml' :
			break ;
			
		case 'msgqueue' :
		case 'msgqueue.html' :
			
			$aController->messageQueue()->display() ;
			
			break ;
			
		// var ------------
		case 'var' :
		case 'var.json' :
			$this->printer()->write(json_encode($this->arrReturnVariables)) ;
			break ;
			
		case 'var.xml' :
			break ;
			
		case 'var.php' :
			$this->printer()->write(var_export($this->arrReturnVariables,true)) ;
			break ;
			
		// view ------------
		case 'noframe' :
		case 'view.noframe' :
			$aController->renderMainView($aController->mainView()) ;
			$aController->displayMainView($aController->mainView(),$this->printer()) ;
			break ;
			
		case 'view' :
		case 'view.inframe' :
		default :
			
			if( $aFrame = $aController->frame() )
			{
				$aController->renderMainView($aFrame->mainView()) ;
				
				$aController->displayMainView($aFrame->mainView(),$this->printer()) ;
			}
			else
			{
				$aController->renderMainView($aController->mainView()) ;
				
				$aController->displayMainView($aController->mainView(),$this->printer()) ;
			}
			
			break ;
		}
		
		// 打印数据库的执行日志
		if( $aController->params()->has('rspn.debug.db.log') )
		{
			$this->printer()->write( '<hr /><h3>数据库执行记录：</h3>' ) ;
			
			foreach(DB::singleton()->executeLog(false) as $arrLog)
			{
				$this->printer()->write( "<div style='padding-top:10px'>[耗时:{$arrLog['time']}] <pre>{$arrLog['sql']}</pre></div>" ) ;
			}
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
					$this->printer()->write( '<hr /><h3>控制器'.$aController->name().'的模型数据结构：</h3>' ) ;
					$this->printDebugModelStruct($this,$sModelName) ;
				}
				
				// 子控制器的模型
				foreach($aController->iterator() as $aChildController)
				{
					$this->printer()->write( '<hr /><h3>控制器'.$aChildController->name().'的模型数据结构：</h3>' ) ;
					foreach($aChildController->modelNameIterator() as $sModelName)
					{
						$this->printDebugModelStruct($aChildController,$sModelName) ;
					}
				}
			}
			else
			{
				$this->printer()->write( '<hr /><h3>控制器'.$aController->name().'的模型数据结构：</h3>' ) ;
				$this->printDebugModelStruct($this,$sModelName) ;
			}
		}
	}
	
	private function printDebugModelStruct(IContainer $aController,$sModelName)
	{
		$this->printer()->write( "<div style='padding-top:10px'><h4>[模型：{$sModelName}]</h4>" ) ;
		if( $aModel=$aController->modelByName($sModelName) )
		{
			$this->printer()->write( "<pre>" ) ;
			$aModel->printStruct($this->printer()) ;
			$this->printer()->write( "</pre>" ) ;
		}
		else
		{
			$this->printer()->write( "模型名称{$sModelName}无效" ) ;
		}
		$this->printer()->write( "</div>" ) ;		
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
	public function printer()
	{
		return $this->aPrinter ;
	}
	
	public function setPrinter(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}

	public function output($sBytes)
	{
		if( $aFilters = $this->filters() )
		{
			list($sBytes) = $aFilters->handle($sBytes) ;
		}
		
		$this->aPrinter->println($sBytes) ;
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
	 * @return org\jecat\framework\io\IOutputStream
	 */
	public function device()
	{
		return $this->aPrinter ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aPrinter ;
	
	private $aFilters ;
	
	private $arrReturnVariables ;
}

