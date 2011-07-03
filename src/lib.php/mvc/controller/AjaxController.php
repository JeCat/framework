<?php
namespace jc\mvc\controller ;

use jc\system\Response;
use jc\lang\Exception;

class AjaxAction extends Controller 
{
	public function process()
	{
		if( !empty($this->aParams['controller']) )
		{
			$aController = $this->application()->accessRouter()->createController($this->aParams['controller']) ;
			if(!$aController)
			{
				throw new Exception('为 AjaxAction 控制器提供的执行参数：controller (%s)无效 。',$this->aParams['controller']) ;
			}
		}
		else if( !empty($this->aParams['class']) )
		{
			if( !class_exists($this->aParams['class']) )
			{
				throw new Exception('为 AjaxAction 控制器提供的执行参数：class (%s)无效 ，无法找到这个类',$this->aParams['class']) ;
			}
			
			$aController = new $this->aParams['class'] ;
		}
		else 
		{
			throw new Exception('AjaxAction 控制器缺少必要的执行参数：controller 或 class 。') ;
		}
		
		$this->aParams['noframe'] = '1' ;
		
		$aController->mainRun( $this->aParams ) ;
		
		$this->application()->response()->printer()->write(
			json_encode($aController->messageQueue())
		) ;
	}
}

?>