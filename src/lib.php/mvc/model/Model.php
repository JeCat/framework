<?php
namespace jc\mvc\model ;

use jc\lang\Object;
use jc\util\HashTable;

abstract class Model extends Object implements IModel
{
	public function __construct($bAggregarion=false)
	{
		parent::__construct() ;
		
		$this->bAggregarion = $bAggregarion ;
		$this->aData = new HashTable() ;
	}

	
	public function isAggregarion()
	{
		return $this->bAggregarion ;
	}
		
	public function hasSerialized()
	{
		return $this->bSerialized ;
	}
	
	protected function setSerialized($bSerialized)
	{
		$this->bSerialized = $bSerialized ;
	}
	

	// for child model ///////////////////////////////
	public function addChild(IModel $aModel,$sName=null)
	{
		if($sName)
		{
			$this->arrChildren[$sName] = $aModel ;
		}
		else 
		{
			$this->arrChildren[] = $aModel ;
		}
	}
	
	public function removeChild(IModel $aModel)
	{
		unset($this->arrChildren[$aModel->name()]) ;
	}
	
	public function clearChildren()
	{
		$this->arrChildren = array() ; 
	}
	
	public function childrenCount()
	{
		return count($this->arrChildren) ;
	}

	/**
	 * @return IModel
	 */
	public function child($sName)
	{
		return isset($this->arrChildren[$sName])?
				$this->arrChildren[$sName]: null ;
	}

	/**
	 * @return IIterator
	 */
	public function childIterator()
	{
		return new \ArrayIterator($this->arrChildren) ;
	}

	// for data ///////////////////////////////
	public function data($sName)
	{
		return isset($this->arrData[$sName])?
					$this->arrData[$sName]: null ;
	}
	
	public function setData($sName,$sValue)
	{
		$this->arrData[$sName] = $sValue ;
	}
	
	public function hasData($sName)
	{
		return isset($this->arrData[$sName]) ;
	}
	
	public function removeData($sName)
	{
		unset($this->arrData[$sName]) ;
	}
	
	public function clearData()
	{
		$this->arrData = array() ;
	}
	
	public function dataIterator()
	{
		return new \ArrayIterator($this->arrData) ;
	}
	
	
	///////////////////////////////////////////
	public function offsetExists($offset)
	{
		return $this->hasData($offset) ;	
	}

	public function offsetGet($offset)
	{	
		return $this->data($offset) ;
	}

	public function offsetSet($offset,$value)
	{
		return $this->setData($offset,$value) ;		
	}

	public function offsetUnset($offset) {
		return $this->removeData($offset) ;	
	}

	// implement Iterator
	/**
	 * 
	 * @return mixed
	 */
	public function current ()
	{
		return current($this->arrDatas) ;
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
		 
		return key($this->arrDatas)!==null ;
	}

	public function rewind ()
	{
		return reset($this->arrDatas) ;
	}
	
	
	private $aPrototype ;
	
	private $arrData = array() ;
	
	private $arrChildren = array() ;
	
	private $bAggregarion = false ;
	
	private $bSerialized = false ;
}

?>