<?php
namespace jc\util ;

use jc\lang\Factory;
use jc\lang\Object;

class DataSrc extends HashTable implements IDataSrc
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
		if( !substr($offset,1,1)=='<' )
		{
			return ;
		}
		
		$sModifier = substr($offset,0,1) ;
		$sRealName = substr($offset,2) ;
		
		$arrTypecastFuncNames = array(
			'i' => 'getInt' ,
			'f' => 'getFloat' ,
			'b' => 'getBool' ,
			's' => 'getString' ,
			'q' => 'getQuoteString' ,
		) ;
		
		if(isset($arrTypecastFuncNames[$sModifier]))
		{
			$sFunc = $arrTypecastFuncNames[$sModifier] ; 
			return $this->$sFunc($sRealName) ;
		}
		else
		{
			return $this->get($offset) ;
		}
	}
	
	// implement IHashTable
	public function get($sName)
	{
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
		if( parent::has($sName) )
		{
			return true ;
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
	public function addChild(IDataSrc $aParams)
	{
		if( !in_array($aParams,$this->arrChildren) )
		{
			$this->arrChildren[] = $aParams ;
		}
	}
	
	/**
	 * 
	 * @return void
	 */
	public function removeChild(IDataSrc $aParams)
	{
		$nIdx = array_search($this->arrChildren, $aParams) ;
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
	 * @return \Iterator
	 */
	public function nameIterator() {
		return new \ArrayIterator(array_keys($this->arrDatas)) ;
	}

	/**
	 * 
	 * @return \Iterator
	 */
	public function valueIterator()
	{
		return new \ArrayIterator(array_values($this->arrDatas)) ;
	}

	/**
	 * 
	 * @return \Iterator
	 */
	public function childIterator()
	{
		return new \ArrayIterator($this->arrChildren) ;
	}


	protected $arrChildren = array() ;
}

?>