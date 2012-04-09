<?php
namespace org\jecat\framework\mvc ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Object;

class MVCEventManager extends Object implements \Serializable
{
	public function registerEventHandle($sEventType,$fnHandler,$sControllerClass=null,$sViewXPath=null,$sWidghtId=null)
	{
		Assert::isCallback($fnHandler) ;
		
		$sKey = $sControllerClass .'-'. $sViewXPath .'-'. $sWidghtId .'-'. $sEventType ;
		$this->arrEventHandles[$sKey][] = $fnHandler ;
	}
	
	public function emitEvent($sEventType,array & $arrArgvs=array(),$sControllerClass=null,$sViewXPath=null,$sWidghtId=null)
	{
		$sKey = $sControllerClass .'-'. $sViewXPath .'-'. $sWidghtId .'-'. $sEventType ;
		if(!empty($this->arrEventHandles[$sKey]))
		{
			foreach($this->arrEventHandles[$sKey] as &$fnHandler)
			{				
				call_user_func_array($fnHandler,$arrArgvs) ;
			}
		}
	}

	public function unserialize($data)
	{
		$this->arrEventHandles = unserialize($data) ;
	}
	public function serialize()
	{
		// 检查
		foreach($this->arrEventHandles as &$arrHandlerList)
		{
			foreach($arrHandlerList as &$fnHandler)
			{
				// 方法
				if( is_array($fnHandler) )
				{
					// 对像方法
					if(is_object($fnHandler[0]))
					{
						throw new \Exception("无法序列化回调函数：%s::%s()",array(get_class($fnHandler[0]),$fnHandler[1])) ;
					}
				}
				
				// 
				else if( is_string($fnHandler) )
				{}
				
				// 匿名函数
				else
				{
					$aRefFunc = new \ReflectionFunction($fnHandler) ;
					throw new \Exception("无法序列化匿名函数，定义位置 File: %s ; Line: %d",array($aRefFunc->getFileName(),$aRefFunc->getStartLine())) ;
				}
			}
		}
		
		return serialize($this->arrEventHandles) ;
	}
	
	private $arrEventHandles = array() ;
}

?>