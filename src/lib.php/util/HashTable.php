<?php

namespace jc\util ;

class HashTable implements IHashTable
{
	public function __construct(array $arrDatas=array())
	{
		$this->arrDatas = $arrDatas ;
	}

	// implement IHashTable
	public function get($sName)
	{
		return $this->arrDatas[$sName]?: null ;
	}

	public function set($sName,&$Value)
	{
		$this->arrDatas[ $sName ] = &$Value ;
	}

	public function has($sName)
	{
		return array_key_exists($this->arrDatas,$sName) ;
	}

	public function remove($sName)
	{
		unset($this->arrDatas[ $sName ]) ;
	}

	public function clear()
	{
		$this->arrDatas = array() ;
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
	
	
	protected $arrDatas = array() ;
}

?>