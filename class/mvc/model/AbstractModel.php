<?php
namespace org\jecat\framework\mvc\model;

use org\jecat\framework\mvc\controller\Response;
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
		
		return $this->findDataByPath($sName,$aModel,$bDataExist) ;
	}
	
	public function setData($sName, $sValue, $bChanged=true)
	{
		$data = $this->findDataByPath($sName,$aModel,$bDataExist) ;
		
		// 无法直接操作 被定位模型的 arrData 和 arrChanged 属性
		if(!$aModel instanceof self)
		{
			return $aModel->setData($sName,$sValue,$bChanged) ;
		}
		
		// 数据在子模型中
		if( !$bDataExist or $data!==$sValue )
		{
			$aModel->arrDatas[$sName] =& $sValue ;
			
			if($bChanged)
			{
				self::_setChanged($aModel,$sName,true) ;
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
		
		$this->findDataByPath ($sName,$aModel,$bDataExists) ;
		return $bDataExists ;
	}
	
	public function removeData($sName)
	{
		if ($this->isEmpty ())
		{
			return;
		}
		
		$data = $this->findDataByPath($sName,$aModel,$bDataExist) ;
		
		// 无法直接操作 被定位模型的 arrData 和 arrChanged 属性
		if(!$aModel instanceof self)
		{
			return $aModel->removeData($sName) ;
		}
		
		// 数据在子模型中
		if( $bDataExist )
		{
			unset($aModel->arrDatas[$sName]) ;
			
			// TODO
			// 是否应该 remove changed 有待考虑
			self::_setChanged($aModel,$sName,false) ;
		}
		
		return $this ;
	}
	
	public function clearData()
	{
		$this->arrDatas = null ;
		$this->arrChildren = array() ;
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
		
	/**
	 * 定位数据所在的子模型。
	 */
	protected function findDataByPath(&$sDataName,&$aModel,&$bDataExist)
	{
		$aModel = $this ;
			
		// 当前模型
		if( !empty($this->arrDatas) and key_exists($sDataName,$this->arrDatas) )
		{
			$bDataExist = true ;
			return $this->arrDatas[$sDataName] ;
		}
		
		// 子模型中的数据
		$pos = strpos($sDataName,'.') ;
		if ( $pos!==false )
		{
			$sChildName = substr($sDataName,0,$pos) ;
			
			if($aChildModel=$this->child($sChildName))
			{
				$sDataName = substr($sDataName,$pos+1) ;
				return $aChildModel->findDataByPath($sDataName,$aModel,$bDataExist) ;
			}
		}
		
		// 当前模型没有数据，子模型中也没有数据
		$bDataExist = false ;
		return null ;
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
			return !empty($aModel->arrChanged[$sName]) ;
		}
		else
		{
			$this->findDataByPath($sName,$aModel,$bDataExist) ;
			
			// 不存在的数据
			if(!$bDataExist)
			{
				return false ;
			}
			
			if(!$aModel instanceof self)
			{
				return $aModel->changed($sName) ;
			}
			else
			{
				return $aModel->arrChanged ;
			}
		}
	}
	
	public function clearChanged()
	{
		$this->arrChanged = array();
	}
	
	public function setChanged($sName,$bChanged=true)
	{
		$this->findDataByPath($sName,$aModel,$bDataExist) ;
		
		if(!$aModel instanceof self)
		{
			$aModel->setChanged($sName,$bChanged) ;
		}
		else
		{
			self::_setChanged($aModel,$sName,$bChanged) ;
		}
		
		return $this ;
	}
	
	static protected function _setChanged(self $aModel,$sName,$bChanged=true)
	{
		if($bChanged)
		{
			$aModel->arrChanged[$sName] = $sName ;
		}
		else
		{
			unset($aModel->arrChanged[$sName]) ;
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
	
	private $arrChildren = array ();
	
	protected $arrDatas ;
	
	protected $arrChanged = array ();
}


class _ExceptionDataNotExists extends \Exception
{}