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
