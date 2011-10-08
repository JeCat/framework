<?php
namespace jc\mvc\model;

use jc\io\IOutputStream;

use jc\lang\Object;

abstract class AbstractModel extends Object implements IModel, \Serializable
{
	public function __construct($bAggregation = false)
	{
		parent::__construct ();
		
		$this->bAggregation = $bAggregation;
	}
	
	public function isAggregation()
	{
		return $this->bAggregation;
	}
	public function setAggregation($bAggregation = true)
	{
		$this->bAggregation = $bAggregation;
	}
	
	// for child model ///////////////////////////////
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
	 * 这个方法仅返回聚合模式的child，他只是为了开发人员从函数名上就能判断返回模型的类型
	 * @return IModel
	 */
	public function childAgg($sName)
	{
		$aChild = $this->child ( $sName );
		return ($aChild and $aChild->isAggregation ()) ? $aChild : null;
	}
	
	/**
	 * @return IIterator
	 */
	public function childIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator ( $this->arrChildren );
	}
	
	/**
	 * @return IIterator
	 */
	public function childNameIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator ( array_keys ( $this->arrChildren ) );
	}
	
	// for data ///////////////////////////////
	public function data($sName)
	{
		if ($this->isEmpty ())
		{
			return null;
		}
		
		$arrKeys = array_keys ( $this->arrDatas );
		if (array_key_exists ( $sName, $this->arrDatas ))
		{
			return $this->arrDatas [$sName];
		}
		
		list ( $aModel, $sName ) = $this->findDataByPath ( $sName );
		if ($aModel)
		{
			return $aModel->data ( $sName );
		}
		
		return null;
	}
	
	public function setData($sName, $sValue)
	{
		list ( $aModel, $sChildName ) = $this->findDataByPath ( $sName );
		if ($aModel)
		{
			$aModel->setData ( $sChildName, $sValue );
		}
		else
		{
			if ($this->isEmpty ())
			{
				$this->arrDatas = array ();
			}
			$this->arrDatas [$sName] = $sValue;
			$this->setChanged($sName);
		}
	}
	
	public function hasData($sName)
	{
		if ($this->isEmpty ())
		{
			return false;
		}
		
		if (array_key_exists ( $sName, $this->arrDatas ))
		{
			return true;
		}
		
		else
		{
			list ( $aModel ) = $this->findDataByPath ( $sName );
			return $aModel ? true : false;
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
		return new \jc\pattern\iterate\ArrayIterator ( $this->arrDatas );
	}
	
	public function dataNameIterator()
	{
		if ($this->isEmpty ())
		{
			return new \EmptyIterator ();
		}
		return new \jc\pattern\iterate\ArrayIterator ( array_keys ( $this->arrDatas ) );
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
			$aOutput = $this->application ()->response ()->printer ();
		}
		
		$aOutput->write ( "<pre>\r\n" );
		
		$aOutput->write ( str_repeat ( "\t", $nDepth ) );
		$aOutput->write ( ($this->isAggregation () ? "[Aggregation Model]" : "[Model]") );
		$aOutput->write ( "\r\n" );
		
		if (! $this->isEmpty ())
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
	}
	
	public function isEmpty()
	{
		return $this->arrDatas === null;
	}
	
	public function changed()
	{
		return $this->arrChanged;
	}
	
	public function clearChanged()
	{
		$this->arrChanged = array();
	}
	
	public function removeChanged($sName)
	{
		unset($this->arrChanged[$sName]);
	}
	
	public function setChanged($sName)
	{
		$this->arrChanged[] = $sName;
		$this->arrChanged = array_unique($this->arrChanged);
	}
	
	private $arrDatas = null;
	
	private $arrChildren = array ();
	
	private $bAggregation = false;
	
	private $arrChanged = array ();
}
?>