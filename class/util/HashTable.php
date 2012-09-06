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
//  正在使用的这个版本是：0.8
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

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Object;

class HashTable extends Object implements IHashTable, \ArrayAccess, \Iterator, \Serializable 
{
	public function __construct(array $arrDatas=array(),$sClass=null)
	{
		$this->arrDatas = $arrDatas ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrDatas) ;
	}

	// implement IHashTable
	public function get($sName)
	{
		return isset($this->arrDatas[$sName])? $this->arrDatas[$sName]: null ;
	}
	public function &getRef($sName)
	{
		if( !array_key_exists($sName, $this->arrDatas) )
		{
			$this->arrDatas[$sName] = null ;
		}
		
		return $this->arrDatas[$sName] ;
	}

	public function set($sName,$Value)
	{
		if( $this->sClass )
		{
			Assert::type($this->sClass,$Value,'Value') ;
		}
		
		$oldVal = isset($this->arrDatas[ $sName ])? $this->arrDatas[ $sName ]: null ;
		$this->arrDatas[ $sName ] = $Value ;
		return $oldVal ;
	}
	public function setRef($sName,&$Value)
	{
		$this->arrDatas[ $sName ] = &$Value ;
	}

	public function has($sName)
	{
		return array_key_exists($sName,$this->arrDatas) ;
	}
	
	public function hasValue($value)
	{
		return in_array($value,$this->arrDatas,true) ;
	}

	public function remove($sName)
	{
		unset($this->arrDatas[ $sName ]) ;
	}

	public function clear()
	{
		$this->arrDatas = array() ;
	}
	
	public function count()
	{
		return count($this->arrDatas) ;
	}

	public function end()
	{
		return end($this->arrDatas) ;
	}
	public function prev()
	{
		return prev($this->arrDatas) ;
	}
	
	// implement ArrayAccess
	public function offsetExists($offset)
	{
		return $this->has($offset) ;	
	}

	public function offsetGet($offset)
	{	
		return $this->get($offset) ;
	}

	public function offsetSet($offset,$value)
	{
		return $this->set($offset,$value) ;		
	}

	public function offsetUnset($offset) {
		return $this->unset($offset) ;	
	}

	// implement Iterator
	/**
	 * 
	 * @return mixed
	 */
	public function & current ()
	{
		$key = key($this->arrDatas) ;
		if($key===false)
		{
			return $null = null ;
		}
		else
		{
			return $this->arrDatas[$key] ;
		}
	}

	/**
	 * 
	 * @return mixed
	 */
	public function next ()
	{
		return next($this->arrDatas) ;
	}

	/**
	 * 
	 * @return mixed
	 */
	public function key ()
	{
		return key($this->arrDatas) ;
	}

	/**
	 * 
	 * @return mixed
	 */
	public function valid ()
	{
		// 使用 null 作为数字索引，会被转换成空字符串 ''，因此可以使用 key()===null 来检查迭代状态
		//
		// $arr = array(null=>1,2,3) ;
		// key($arr)===''
		$k = key($this->arrDatas) ;
		return key($this->arrDatas)!==null ;
	}

	public function rewind ()
	{
		return reset($this->arrDatas) ;
	}
	
	
	/**
	 * 
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function nameIterator() {
		return new \org\jecat\framework\pattern\iterate\ArrayIterator(array_keys($this->arrDatas)) ;
	}

	/**
	 * 
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function valueIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator(array_values($this->arrDatas)) ;
	}
	
	public function add($Value)
	{
		$this->arrDatas[] = $Value ;
	}
	
	public function reverse () 
	{
		krsort($this->arrDatas) ;
	}
	
	public function acceptClass($sClass)
	{
		return $this->sClass ;
	}
	public function setAcceptClass($sClass)
	{
		$this->sClass = $sClass ;
	} 
	
	public function removeByValue($val)
	{
		$key=array_search($val,$this->arrDatas,!is_object($val)) ;
		if( $key!==false )
		{
			unset($this->arrDatas[$key]) ;
		}
	}
	
	public function serialize ()
	{
		return serialize( array(
				'arrDatas' => &$this->arrDatas ,
				'sClass' => &$this->sClass ,
		) ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
		
		$this->arrDatas =& $arrData['arrDatas'] ;
		$this->sClass =& $arrData['sClass'] ;
	}
	
	public function __get($sName)
	{
		return $this->get($sName) ;
	}
	public function __set($sName,$Value)
	{
		return $this->set($sName,$Value) ;
	}
	
	protected $arrDatas = array() ;
	
	private $sClass = array() ;
}


