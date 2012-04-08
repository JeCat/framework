<?php
namespace org\jecat\framework\util ;


/**
 * 要求对像提供可以返回/接收数组参数的 serialize()/unserializ() 重载版本
 */
interface IArraySerializable extends \Serializable
{
	public function serialize ($bToArray=false) ;

	public function unserialize ($arrSerialized) ;
	
	
}
