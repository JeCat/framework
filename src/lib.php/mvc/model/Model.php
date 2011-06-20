<?php
namespace jc\mvc\model ;

use jc\lang\Object;

class Model extends Object implements IModel
{
	public function __construct($bAggregarion=false)
	{
		parent::__construct() ;
		
		$this->bAggregarion = $bAggregarion ;
	}


	public function isAggregarion()
	{
		return $this->bAggregarion ;
	}
	public function setAggregarion($bAggregarion=true)
	{
		$this->bAggregarion = $bAggregarion ;
	}
		
	public function hasSerialized()
	{
		return $this->bSerialized ;
	}
	
	public function setSerialized($bSerialized=true)
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
		if( array_key_exists($sName, $this->arrData) )
		{
			return $this->arrData[$sName] ;
		}
		
		list($aModel,$sName) = $this->findDataByPath($sName) ;
		if($aModel)
		{
			return $aModel->data($sName) ;
		}
		
		return null ;
	}
	
	public function setData($sName,$sValue)
	{
		list($aModel,$sChildName) = $this->findDataByPath($sName) ;
		if($aModel)
		{
			$aModel->setData($sChildName,$sValue) ;
		}
		else 
		{
			$this->arrData[$sName]=$sValue ;
		}
	}
	
	public function hasData($sName)
	{
		if(array_key_exists($sName,$this->arrData))
		{
			return true ;
		}
		
		else 
		{
			list($aModel) = $this->findDataByPath($sName) ;
			return $aModel? true: false ;
		}
	}
	
	public function removeData($sName)
	{
		if(array_key_exists($sName,$this->arrData))
		{
			unset($this->arrData[$sName]) ;
		}
		
		else 
		{
			list($aModel) = $this->findDataByPath($sName) ;
			if($aModel)
			{
				$aModel->removeData($sName) ;
			}
		}
	}
	
	public function clearData()
	{
		$this->arrData = array() ;
	}
	
	public function dataIterator()
	{
		return new \ArrayIterator($this->arrData) ;
	}
	
	public function dataNameIterator()
	{
		return new \ArrayIterator( array_keys($this->arrData) ) ;
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
	
	protected function findDataByPath($sDataPath)
	{
		$arrSlices = explode('.', $sDataPath) ;
		if( count($arrSlices)>1 )
		{
			$sName = array_pop($arrSlices) ;
			$aModel = $this ;
			do{
				
				$sModelName = array_shift($arrSlices) ;
				
			}while( $aModel=$aModel->child($sModelName) and !empty($arrSlices) ) ;
			
			if( $aModel )
			{
				return array($aModel,$sName) ;
			}
		}
		
		return array(null,null) ;
	}

	public function __get($sName)
	{
		return $this->data($sName) ;
	}
	
	public function __set($sName,$value)
	{
		$this->setData($sName, $value) ;
	}
	
	private $arrData = array() ;
	
	private $arrChildren = array() ;
	
	private $bAggregarion = false ;
	
	private $bSerialized = false ;
}

?>