<?php
namespace org\jecat\framework\mvc\model;

use org\jecat\framework\system\Response;

use org\jecat\framework\io\IOutputStream;

use org\jecat\framework\lang\Object;

abstract class AbstractModel extends Object implements IModel, \Serializable
{
	public function __construct($bList)
	{
		$this->setList($bList) ;
	}
	
	// for child model ///////////////////////////////
	
	public function isEmpty()
	{
		return empty($this->arrDatas) and empty($this->arrChildren) ;
	}
	
	public function addChild(IModel $aModel, $sName = null)
	{
		if ($sName)
		{
			$this->arrChildren [$sName] = $aModel;
		}
		else
		{
			$this->arrChildren [] = $aModel;
		}
	}
	
	public function removeChild(IModel $aModel)
	{
		unset ( $this->arrChildren [$aModel->name ()] );
	}
	
	public function clearChildren()
	{
		$this->arrChildren = array ();
	}
	
	public function childrenCount()
	{
		return count ( $this->arrChildren );
	}
	
	/**
	 * @return IModel
	 */
	public function child($sName)
	{
		return isset ( $this->arrChildren [$sName] ) ? $this->arrChildren [$sName] : null;
	}
	
	/**
	 * @return IIterator
	 */
	public function childIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( $this->arrChildren );
	}
	
	/**
	 * @return IIterator
	 */
	public function childNameIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( array_keys ( $this->arrChildren ) );
	}
	
	// for data ///////////////////////////////
	public function data($sName)
	{
		if ($this->isEmpty ())
		{
			return null;
		}
		
		$sData = $this->_data($sName) ;
		if($sData!==null)
		{
			return $sData ;
		}
		
		list ( $aModel, $sName ) = $this->findDataByPath ( $sName );
		if ($aModel)
		{
			return $aModel->data ( $sName );
		}
		
		return null;
	}
	
	public function setData($sName, $sValue, $bChanged=true)
	{
		list ( $aModel, $sChildName ) = $this->findDataByPath ( $sName );
		if ($aModel)
		{
			$aModel->setData ( $sChildName, $sValue ,$bChanged);
		}
		else
		{
			if ($this->isEmpty ())
			{
				$this->arrDatas = array ();
			}
			if( $this->hasData($sName)===false or $this->data($sName) !== $sValue ){
				$this->arrDatas [$sName] = $sValue;
				if($bChanged){
					$this->setChanged($sName);
				}
			}
		}
		
		return $this ;
	}
	
	public function hasData($sName)
	{
		if ($this->isEmpty ())
		{
			return false;
		}
		
		if ($this->_data($sName)!==null)
		{
			return true;
		}
		
		else
		{
			list ( $aChildModel, $sName ) = $this->findDataByPath ( $sName );
			return $aChildModel ? $aChildModel->_data($sName)!==null : false ;
		}
	}
	
	public function removeData($sName)
	{
		if ($this->isEmpty ())
		{
			return;
		}
		
		if (array_key_exists ( $sName, $this->arrDatas ))
		{
			unset ( $this->arrDatas [$sName] );
			$this->removeChanged($sName);
		}
		
		else
		{
			list ( $aModel ) = $this->findDataByPath ( $sName );
			if ($aModel)
			{
				$aModel->removeData ( $sName );
			}
		}
	}
	
	public function clearData()
	{
		if ($this->isEmpty ())
		{
			return;
		}
		$this->clearChanged();
	}
	
	public function dataIterator()
	{
		if ($this->isEmpty ())
		{
			return new \EmptyIterator ();
		}
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( $this->arrDatas );
	}
	
	public function dataNameIterator()
	{
		if ($this->isEmpty ())
		{
			return new \EmptyIterator ();
		}
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( array_keys ( $this->arrDatas ) );
	}
	
	///////////////////////////////////////////
	public function offsetExists($offset)
	{
		return $this->hasData ( $offset );
	}
	
	public function offsetGet($offset)
	{
		return $this->data ( $offset );
	}
	
	public function offsetSet($offset, $value)
	{
		return $this->setData ( $offset, $value );
	}
	
	public function offsetUnset($offset)
	{
		return $this->removeData ( $offset );
	}
	
	// implement Iterator
	/**
	 * 
	 * @return mixed
	 */
	public function current()
	{
		if ($this->isEmpty ())
		{
			return null;
		}
		return current ( $this->arrDatas );
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function next()
	{
		if ($this->isEmpty ())
		{
			return null;
		}
		return next ( $this->arrDatas );
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function key()
	{
		if ($this->isEmpty ())
		{
			return null;
		}
		return key ( $this->arrDatas );
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function valid()
	{
		// 使用 null 作为数字索引，会被转换成空字符串 ''，因此可以使用 key()===null 来检查迭代状态
		//
		// $arr = array(null=>1,2,3) ;
		// key($arr)===''
		if ($this->isEmpty ())
		{
			return false;
		}
		
		return key ( $this->arrDatas ) !== null;
	}
	
	public function rewind()
	{
		if ($this->isEmpty ())
		{
			return false;
		}
		return reset ( $this->arrDatas );
	}
	
	protected function findDataByPath($sDataPath)
	{
		$arrSlices = explode ( '.', $sDataPath );
		if (count ( $arrSlices ) > 1)
		{
			$sName = array_pop ( $arrSlices );
			$aModel = $this;
			do
			{
				$sModelName = array_shift ( $arrSlices );
			} while ( $aModel = $aModel->child ( $sModelName ) and ! empty ( $arrSlices ) );
			
			if ($aModel)
			{
				return array ($aModel, $sName );
			}
		}
		
		return array (null, null );
	}
	
	public function __get($sName)
	{
		$nNameLen = strlen ( $sName );
		if ($nNameLen > 5 and substr ( $sName, 0, 5 ) == 'model')
		{
			$sModelName = substr ( $sName, 5 );
			return $this->child ( $sModelName );
		}
		
		return $this->data ( $sName );
	}
	
	public function __set($sName, $value)
	{
		$this->setData ( $sName, $value );
	}
	
	// misc
	public function printStruct(IOutputStream $aOutput = null, $nDepth = 0)
	{
		if (! $aOutput)
		{
			$aOutput = Response::singleton()->printer();
		}
		
		$aOutput->write ( "<pre>\r\n" );
		
		$aOutput->write ( str_repeat ( "\t", $nDepth ) );
		$aOutput->write ( ($this->isList()? "[Model List]" : "[Model]") );
		$aOutput->write ( "\r\n" );
		
		if (!empty($this->arrDatas))
		{
			foreach ( $this->arrDatas as $sName => $value )
			{
				$aOutput->write ( str_repeat ( "\t", $nDepth ) . "{$sName}: " . strval ( $value ) . "\r\n" );
			}
		}
		
		foreach ( $this->childNameIterator () as $sName )
		{
			$aOutput->write ( str_repeat ( "\t", $nDepth ) . "\"{$sName}\" =>\r\n" );
			$this->child ( $sName )->printStruct ( $aOutput, $nDepth + 1 );
		}
		
		$aOutput->write ( "</pre>" );
	}
	
	public function setChildrenData($sName, $value)
	{
		foreach ( $this->childIterator () as $aChild )
		{
			$aChild->setData ( $sName, $aChild );
		}
		return $this ;
	}
	
	/**
	 * @param string $sName	$sName=null返回一个数组，或返回指定数据项的“是否变化”状态
	 */
	public function changed($sName=null)
	{
		if($sName)
		{
			return isset($this->arrChanged[$sName])? true: false ;
		}
		else
		{
			return $this->arrChanged ;
		}
	}
	
	public function clearChanged()
	{
		$this->arrChanged = array();
	}
	
	public function setChanged($sName,$bChanged=true)
	{
		if($bChanged)
		{
			$this->arrChanged[$sName] = $sName ;
		}
		else
		{
			unset($this->arrChanged[$sName]) ;
		}
	}

	public function serialize ()
	{
		return serialize( array(
				'arrDatas' => &$this->arrDatas ,
				'arrChildren' => &$this->arrChildren ,
				'arrChanged' => &$this->arrChanged ,
		) ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
		
		$this->arrDatas =& $arrData['arrDatas'] ;
		$this->arrChildren =& $arrData['arrChildren'] ;
		$this->arrChanged =& $arrData['arrChanged'] ;
	}
	protected function _data(&$sName)
	{
		return isset($this->arrDatas[$sName])?  $this->arrDatas[$sName]: null ;
	}
	
	public function isList()
	{
		return $this->bList ;
	}
	public function setList($bList=true)
	{
		$this->bList = $bList? true: false ;
		return $this ;
	}
	
	private $bList = false ;
	
	private $arrDatas = null;
	
	private $arrChildren = array ();
	
	private $arrChanged = array ();
}
?>
