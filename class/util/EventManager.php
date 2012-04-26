<?php
namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Object;

class EventManager extends Object implements \Serializable
{
	public function registerEventHandle($sClass,$sEvent,$fnHandler,array $arrCallbackArgvs=null,$sourceObject='*')
	{
		Assert::isCallback($fnHandler) ;
		
		if( is_object($sourceObject) )
		{
			$sourceObject = spl_object_hash($sourceObject) ;
		}
		
		$this->arrEventHandles[$sClass][$sEvent][$sourceObject][] = array($fnHandler,$arrCallbackArgvs) ;
	}
	
	public function emitEvent($sClass,$sEvent,array & $arrArgvs=array(),$sourceObject='*')
	{
		if( is_object($sourceObject) )
		{
			$sourceObject = spl_object_hash($sourceObject) ;
		}
		
		if(!empty($this->arrEventHandles[$sClass][$sEvent][$sourceObject]))
		{
			foreach($this->arrEventHandles[$sClass][$sEvent][$sourceObject] as &$handler)
			{
				// 合并注册时提供的参数
				if( $handler[1] )
				{
					foreach($handler[1] as &$callbackArgv)
					{
						$arrArgvs[] =& $callbackArgv ;
					}
				}
				
				call_user_func_array($handler[0],$arrArgvs) ;

				// 清理注册时提供的参数
				if( $handler[1] )
				{
					for($i=count($handler[1]);$i>0;$i--)
					{
						array_pop($arrArgvs) ;
					}
				}				
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
					if( empty($fnHandler[0]) or empty($fnHandler[1]) )
					{
						throw new \Exception("事件回调函数类型错误：".var_export($fnHandler,1)) ;
					}
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
