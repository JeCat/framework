<?php
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\orm\Selecter;
use org\jecat\framework\mvc\model\IModel ;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\controller\Response;

class ModelList extends Model implements \Iterator
{
	public function load($values=null,$keys=null)
	{
		$this->nTotalCount = -1 ;
		
		return parent::load($values,$keys) ;
	}
	public function save($bForceCreate=false)
	{
		foreach($this as $aChildModel)
		{
			if( !$aChildModel->save($bForceCreate) )
			{
				return false ;
			}
		}
		return true ;
	}
	
	public function delete()
	{
		foreach($this as $aChildModel)
		{
			if( !$aChildModel->delete() )
			{
				return false ;
			}
		}
		return true ;
	}
	
	public function data($sName,$nModelIdx=null)
	{
		if($nModelIdx===null)
		{
			return ($aShareModel=$this->shareModel())? $aShareModel->data($sName): null ;
		}
		else
		{
			$this->transDataName($sName) ;
			return isset($this->arrDataSheet[$nModelIdx][$sName])?
				$this->arrDataSheet[$nModelIdx][$sName]: null ;
		}
	}
	
	public function setData($sName,$value,$nModelIdx=null)
	{
		if($nModelIdx===null)
		{
			if($aShareModel=$this->shareModel())
			{
				$aShareModel->setData($sName,$value) ;
			}
		}
		else
		{
			$this->transDataName($sName) ;
			$this->arrDataSheet[$nModelIdx][$sName] = $value ;
		}
		
		return $this ;
	}
	
	public function childIndex(Model $aChild)
	{
		return $aChild->nDataRow ;
	}
	
	public function child($name)
	{
		if( is_int($name) and $name>=0 )
		{
			if( count($this->arrDataSheet)<=$name )
			{
				return null ;
			}
			else
			{
				$this->nDataRow = $name ;
				return $this->shareModel() ;
			}
		}
		else
		{
			return parent::child($name) ;
		}
	}
	
	public function addChild(IModel $aModel, $sName = null)
	{
		throw new Exception("ModelList 的 addChild 方法被禁用") ; 
	}
	
	public function removeChild(IModel $aModel)
	{
		if( $aModel!==$this->aShareModel )
		{
			return ;
		}
		
		usset($this->arrDataSheet[$aModel->nDataRow]) ;
		
		if( (--$aModel->nDataRow)<0 )
		{
			$aModel->nDataRow = 0 ;
		}		
	}
	
	public function clearChildren()
	{
		$this->arrDataSheet = array() ;
		$this->nDataRow = 0 ;
	}
	
	public function childrenCount()
	{
		return is_array($this->arrDataSheet)? count($this->arrDataSheet): 0 ;	
	}
	
	public function childIterator()
	{
		return $this ;
	}
	
	public function sortChildren($callback,$bDesc=false)
	{
		// 冒泡排序 法
		$nLen = count($this->arrDataSheet) ;
		$aModelA = $this->shareModel() ;
		$aModelB = new Model($this->prototype()) ;
		$aModelB->arrDataSheet =& $this->arrDataSheet ;
		
		for($m=$nLen-1;$m>0;$m--)
		{
			for($n0=0;$n0<$m;$n0++)
			{
				$aModelA->nDataRow = $n0 ;
				$aModelB->nDataRow = $n1 = $n0 + 1 ;
				
				$nRes = call_user_func($callback,$aModelA,$aModelB) ;
				
				// 如果 $bDesc=true and $aModelA > $aModelB 
				// 或者 $bDesc=false and $aModelA < $aModelB  
				// 则调换数据的位置
				if( ($bDesc and $nRes<0) or (!$bDesc and $nRes>0) )
				{
					$tmp =& $this->arrDataSheet[$n0] ;
					$this->arrDataSheet[$n0] =& $this->arrDataSheet[$n1] ;
					$this->arrDataSheet[$n1] =& $tmp ;
				}				
			}
		}
	}
	
	/**
	 * 在模型列表中新增一个子模型
	 */
	public function createChild()
	{
		if( !$this->prototype() )
		{
			throw new Exception("模型没有缺少对应的原型，无法为其创建子模型") ;
		}
		
		$this->nDataRow = count($this->arrDataSheet) ;
		$this->arrDataSheet[$this->nDataRow] = array() ;
		
		return $this->shareModel() ;
	}
	
	/**
	 * 从数据表中加载一个符合条件的子模型
	 */
	public function loadChild($values=null,$keys=null)
	{
		$aChild = $this->createChild() ;
	
		$arrArgvs = func_get_args() ;
		if( !call_user_func_array( array($aChild,'load'), $arrArgvs ) )
		{
			// 移除模型
			$this->removeChild($aChild) ;
			return ;
		}
	
		return $aChild ;
	}
	
	/**
	 * 从已经加载的子模型中查找符合条件的模型
	 */
	public function findChildBy($values,$keys=null)
	{
		if(!$keys)
		{
			$keys = $this->prototype()->primaryKeys() ;
		}
		$keys = (array)$keys ;
		$values = (array)$values ;
		
		$keys = array_values($keys) ;
		$values = array_values($values) ;
		
		foreach( $this as $aChild )
		{
			foreach($values as $nIdx=>$sValue)
			{
				if( isset($keys[$nIdx]) and $aChild->data($keys[$nIdx])!=$sValue )
				{
					continue(2) ;
				}
			}
			return $aChild ;
		}
		
		return null ;
	}
	
	/**
	 * 如果符合传入条件的模型不存在，尝试从数据表中加载这个模型
	 */
	public function buildChild($values=null,$keys=null)
	{
		if( !$aChildModel=$this->findChildBy($values,$keys) and !$aChildModel=$this->loadChild($values,$keys) )
		{
			$aChildModel = $this->createChild(true,true) ;
			
			if( $keys )
			{
				$values = (array) $values ;
				foreach((array) $keys as $i=>$sKey)
				{
					$aChildModel->setData($sKey,$values[$i]) ;
				}
			}
		}
		
		return $aChildModel ;
	}
	
	public function totalCount()
	{
		if($this->nTotalCount<0)
		{
			$this->nTotalCount =Selecter::singleton()->totalCount(DB::singleton(),$this->prototype()) ;
		}
		return $this->nTotalCount ;
	}
	
	protected function printStructData(IOutputStream $aOutput = null, $nDepth = 0)
	{
		// nothing todo
	}
	protected function printStructChildren(IOutputStream $aOutput = null, $nDepth = 0)
	{
		// 模型
		if($this->childrenCount())
		{
			foreach ( $this as $nIdx=>$aChildModel )
			{
				$aChildModel->printStruct ( $aOutput, $nDepth+1, "<b>[{$nIdx}] => </b>" );
			}
		}
		else
		{
			$aOutput->write ( str_repeat ( "\t", $nDepth+1 ) ) ;
			$aOutput->write ( "&lt; empty &gt;\r\n") ;
		}
	}
	
	// implements \Iterator ----------------------------------
	public function current ()
	{
		return $this->shareModel() ;
	}
	
	public function next ()
	{
		if( $aShareModel = $this->shareModel() )
		{
			$aShareModel->nDataRow ++ ;
		}
	}
	
	public function key ()
	{
		if( $aShareModel = $this->shareModel() )
		{
			return $aShareModel->nDataRow ;
		}
		else 
		{
			return null ;
		}
	}
	
	public function valid ()
	{
		$aShareModel = $this->shareModel() ;
		return $aShareModel and is_array($aShareModel->arrDataSheet) and $aShareModel->nDataRow < count($aShareModel->arrDataSheet) ;
	}
	
	public function rewind ()
	{
		if( $aShareModel = $this->shareModel() )
		{
			$aShareModel->nDataRow = 0 ;
		}
	}
	
	/**
	 * @return ModelList
	 */
	static public function belongsModelList(Model $aModel)
	{
		return $aModel->data('__belongsModelList') ;
	}
	
	/**
	 * @return Model
	 */
	public function shareModel()
	{
		if( !$this->aShareModel and $aPrototype=$this->prototype() )
		{
			$this->aShareModel = $aPrototype->createModel(false) ;
			$this->segmentalizeChild($this->aShareModel) ;
			$this->aShareModel->data('__belongsModelList',$this) ;
		}
		
		return $this->aShareModel ;
	}
	
	public function isList()
	{
		return true ;
	}
	
	public function __clone()
	{
		$this->aShareModel = null ;
		
		$arrDataSheet = array() ;
		$nDataRow = 0 ;
		$this->arrDataSheet =& $arrDataSheet ;
		$this->nDataRow =& $nDataRow ;
	}
	
	private $aShareModel ;
	
	private $nTotalCount = -1 ;
}
