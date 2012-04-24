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

use org\jecat\framework\lang\Type;

/**
 * 
 * @example aaa/bbb/ccc
 *
 */
class DataSrc extends HashTable implements IDataSrc, \ArrayAccess, \Iterator
{
	public function __construct(array &$arrDatas=null,$bByRef=false)
	{
		if($arrDatas!==null)
		{
			if($bByRef)
			{
				$this->arrDatas = &$arrDatas ;
			}
			else
			{
				$this->arrDatas = $arrDatas ;
			}
		}
	}
	

	/**
	 * 
	 * @return void
	 */
	public function setDataByRef(array &$arrDatas)
	{
		$this->arrDatas = &$arrDatas ;
	}
	
	
	// implement ArrayAccess
	public function offsetGet($offset)
	{	
		return $this->get($offset) ;
	}
	
	// implement IHashTable
	public function get($sName)
	{
		// disable data
		if( $this->arrDisables and array_key_exists($sName, $this->arrDisables) )
		{
			return null ;
		} 
		
		if(isset($this->arrDatas[ $sName ]))
		{
			return $this->arrDatas[ $sName ] ;
		}
		
		// 从 Childs 中找数据
		foreach($this->arrChildren as $aChild)
		{
			$Data = $aChild->get($sName) ;
			if( $Data!==null )
			{
				return $Data ;
			}
		}
		
		return null ;
	}

	public function has($sName)
	{
		// disable data
		if( $this->arrDisables and array_key_exists($sName, $this->arrDisables) )
		{
			return false ;
		} 
		
		if( parent::has($sName) )
		{
			return true ;
		}
		
		// 在 exclude中的数据，不向 childs中寻找
		if(in_array($sName,$this->arrExclude)){
			return false ;
		}
		
		// 从 Childs 中找数据
		foreach($this->arrChildren as $aChild)
		{
			if( $aChild->has($sName) )
			{
				return true ;
			}
		}
		
		return false ;
	}
	
	public function &getRef($sName)
	{
		// disable data
		if( $this->arrDisables and array_key_exists($sName, $this->arrDisables) )
		{
			return null ;
		} 
		
		if(isset($this->arrDatas[ $sName ]))
		{
			return $this->arrDatas[ $sName ] ;
		}
		
		// 从 Childs 中找数据
		foreach($this->arrChildren as $aChild)
		{
			$Data = &$aChild->getRef($sName) ;
			if( $Data!==null )
			{
				return $Data ;
			}
		}
		
		$this->arrDatas[ $sName ] = null ;
		return $this->arrDatas[ $sName ] ;
	}
	

	public function int($sName)
	{
		return intval($this->get($sName)) ;
	}
	public function float($sName)
	{
		return floatval($this->get($sName)) ;
	}
	public function bool($sName)
	{
		return !in_array( strtolower($this->get($sName)), array(0,'false') ) ;
	}
	public function string($sName)
	{
		return strval($this->get($sName)) ;
	}
	public function quoteString($sName)
	{
		return addslashes( $this->string($sName) ) ;
	}
	
	/**
	 * 
	 * @return void
	 */
	public function addChild(IHashTable $aParams)
	{
		if( !in_array($aParams,$this->arrChildren,true) )
		{
			$this->arrChildren[] = $aParams ;
		}
	}
	
	/**
	 * 
	 * @return void
	 */
	public function removeChild(IHashTable $aParams)
	{
		$nIdx = array_search($aParams,$this->arrChildren,true) ;
		if($nIdx!==false)
		{
			unset($this->arrChildren[$nIdx]) ;
		}
	}
	
	/**
	 * 
	 * @return void
	 */
	public function clearChild()
	{
		$this->arrChildren = array() ;
	}	
	
	/**
	 * 
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function childIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrChildren) ;
	}

	
	public function values(/*$sKey1,...$sKeyN*/)
	{
		$arrRet = array() ;
		foreach(func_get_args() as $sName)
		{
			$arrRet[] = $this->get($sName) ;
		}
		
		return $arrRet ;
	}
	
	public function disableData($sName)
	{		
		$this->arrDisables[$sName] = $sName ;
	}
	public function enableData($sName)
	{
		unset($this->arrDisables[$sName]) ;
	}
	public function clearDisabled()
	{
		$this->arrDisables = null ;
	}
	
	public function toUrlQuery()
	{
		$arrData = array() ;
		$this->exportToArray($arrData) ;
		
		ksort($arrData) ;
		
		return http_build_query($arrData) ;
	}
	
	public function exportToArray(array &$arrToArray)
	{		
		foreach($this->childIterator() as $aChild)
		{
			$aChild->exportToArray($arrToArray) ;
		}
		
		foreach($this->nameIterator() as $sDataName)
		{
			$arrToArray[$sDataName] = $this->get($sDataName) ;
		}
	}

	static public function compare(IDataSrc $aDataSrc,$otherDataSrc)
	{
		if( $otherDataSrc instanceof IDataSrc )
		{
			foreach($otherDataSrc->nameIterator() as $sName)
			{
				$value =& $otherDataSrc->getRef($sName) ;
				$thisValue =& $aDataSrc->getRef($sName) ;
			
				if( is_object($value) )
				{
					if( $thisValue!==$value )	// 对象 用 === 判断
					{
						return false ;
					}
				}
				else if( $thisValue!=$value )	// 普通变量 用 == 判断
				{
					return false ;
				}
			}
		}
		else 
		{
			if( is_string($otherDataSrc) )
			{
				parse_str($otherDataSrc,$otherDataSrc) ;
			}
			else if( !is_array($otherDataSrc) )
			{
				return false ;
			}
			
			foreach($otherDataSrc as $name=>&$value)
			{
				$thisValue =& $aDataSrc->getRef($name) ;
			
				if( is_object($value) )
				{
					if( $thisValue!==$value )	// 对象 用 === 判断
					{
						return false ;
					}
				}
				else if( $thisValue!=$value )	// 普通变量 用 == 判断
				{
					return false ;
				}
			}
		}
		
		return true ;
	}
	
	static public function sortQuery($dataSrc)
	{
		if( is_string($dataSrc) )
		{
			parse_str($dataSrc,$dataSrc) ;
		}
		else if( !is_array($dataSrc) )
		{
			return (string)$dataSrc ;
		}
		
		self::recursionSortArray($dataSrc) ;
		
		return http_build_query($dataSrc) ;
	}
	static private function recursionSortArray(&$dataSrc)
	{
		ksort($dataSrc) ;
	
		foreach($dataSrc as &$item)
		{
			if( is_array($item) )
			{
				self::recursionSortArray($item) ;
			}
		}
	}
	
	public function setExclude($exclude){
		Type::toArray($exclude) ;
		$this->arrExclude = $exclude ;
	}
	
	protected $arrChildren = array() ;
	
	private $arrDisables ;
	
	private $arrExclude = array () ;
}


