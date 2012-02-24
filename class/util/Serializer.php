<?php
namespace org\jecat\framework\util ;


class Serializer
{
	public function serialize($variable)
	{
		
	}
	public function unserilize($data)
	{
		
	}
	
	public function _serialize($variable)
	{
		if( $variable instanceof \IArraySerializable )
		{
			$arrSerialized = $variable->serialize(true) ;
			
			foreach($arrSerialized as $sName=>&$child)
			{
				// 检查 并 转存对像
				if( is_object($child) )
				{
					$sObjId = spl_object_hash($child) ;
					
					// 遇到新的对像
					if( !$this->arrInstances or !in_array($child,$this->arrInstances) )
					{
						$this->arrInstances[$sObjId] = $child ;
						$this->arrInstanceSerializeds[$sObjId] = $this->_serialize($child) ;
					}
					
					$child = '-ins-:'.$sObjId ;
				}
				
				/*else
				{
					$child = $this->_serialize($child) ;
				}*/
			}
			
			// 留下标记
			$arrSerialized['-serializer-'] = __CLASS__ ;
			$arrSerialized['-instances-'] = $this->arrInstanceSerializeds ;
			
			return serialize($arrSerialized) ;
		}
		else
		{
			return serialize($variable) ;
		}
	}
	
	public function _unserilize($data)
	{
		$variable = unserilize($data) ;
		
		// 检查标记
		if( !is_array($variable) or empty($variable['-serializer-']) )
		{
			return $variable ;
		}
		
		//清理标记
		unset($variable['-serializer-']) ;
		
		// 检查 还原 对像
		foreach($variable as $sName=>&$child)
		{
			
		}
	}
	
	
	
	private $arrInstances ;
	
	private $arrInstanceSerializeds ;
}

?>