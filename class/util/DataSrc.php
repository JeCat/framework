<?php
namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Factory;
use org\jecat\framework\lang\Object;

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
		
		return null ;
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
	
	protected $arrChildren = array() ;
	
	private $arrDisables ;
}

?>