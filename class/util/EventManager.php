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
		
		return $this ;
	}
	
	/**
	 * 触发一个事件，依次执行注册给该事件的所有回调函数
	 * 如果没有任何一个回调函数提供返回值，则返回null
	 * @return EventReturnValue
	 */
	public function emitEvent($sClass,$sEvent,array & $arrArgvs=array(),$sourceObject='*')
	{
		if( is_object($sourceObject) )
		{
			$sourceObject = spl_object_hash($sourceObject) ;
		}
		
		$aReturnValue = null ;

		foreach( array('*',$sourceObject) as $sObjectKey )
		{
			if(!empty($this->arrEventHandles[$sClass][$sEvent][$sObjectKey]))
			{
				foreach($this->arrEventHandles[$sClass][$sEvent][$sObjectKey] as &$handler)
				{
					// 检查事件的返回值
					$return = $this->runHandler($handler) ;
					
					if( $return instanceof EventReturnValue )
					{
						$aReturnValue = $return ;
						
						if($return->stopEvent())
						{
							return $aReturnValue ;
						}
					}
				}
			}
		}
		
		return $aReturnValue ;
	}
	
	private function runHandler(& $fnHandler)
	{
		if( is_array($fnHandler[0]) )
		{
			$sCallCode = "\$return = \$fnHandler[0][0]".(is_string($fnHandler[0][0])? '::': '->')."\$fnHandler[0][1](" ;
				
		}
		else
		{
			$sCallCode = "\$return = \$fnHandler[0](" ;
		}
		for($nIdx=0;$nIdx<count($arrArgvs);$nIdx++)
		{
			if($nIdx)
			{
				$sCallCode.= ',' ;
			}
			$sCallCode.= "\$arrArgvs[{$nIdx}]" ;
		}
		
		if( $fnHandler[1] )
		{
			for($nIdx=0;$nIdx<count($fnHandler[1]);$nIdx++)
			{
				if($nIdx)
				{
					$sCallCode.= ',' ;
				}
				$sCallCode.= "\$fnHandler[1][{$nIdx}]" ;
			}
		}
				
		$sCallCode.= ') ;' ;
		
		eval($sCallCode) ;
				
		// 检查事件的返回值
		return $return ;
	} 

	public function unserialize($data)
	{
		$this->arrEventHandles = unserialize($data) ;
	}
	public function serialize()
	{
		// 检查
		foreach($this->arrEventHandles as $sClass=>&$arrEventList)
		{
			foreach($arrEventList as $sEvent=>&$arrObjectList)
			{
				foreach($arrObjectList as $sObjectId=>&$arrHandlerList)
				{
					foreach($arrHandlerList as &$arrHandler)
					{
						$fnHandler =& $arrHandler[0] ;
						
						// 方法
						if( is_array($fnHandler) )
						{
							if( empty($fnHandler[0]) or empty($fnHandler[1]) )
							{
								throw new Exception("事件回调函数类型错误：%s",var_export($fnHandler,1)) ;
							}
							// 对像方法
							if(is_object(reset($fnHandler)))
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
			}
		}
		
		return serialize($this->arrEventHandles) ;
	}
	
	private $arrEventHandles = array() ;
}
