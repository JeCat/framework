<?php
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\util\Stack;

use org\jecat\framework\mvc\model\db\orm\Selecter;

use org\jecat\framework\mvc\model\db\orm\Prototype;

use org\jecat\framework\db\sql\StatementFactory;

use org\jecat\framework\db\sql\Update;

use org\jecat\framework\db\DB;

use org\jecat\framework\lang\Exception;

class Category extends Model 
{	
	const top = 1 ;
	const end = null ;
	
	public function leftPoint()
	{
		return (int)$this->data('lft') ;
	}
	public function rightPoint()
	{
		return (int)$this->data('rgt') ;
	}
	
	public function leftColumn()
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		return '`'.($aOrmPrototype->getColumnByAlias('lft')?:'lft').'`' ;
	}
	public function rightColumn()
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		return '`'.($aOrmPrototype->getColumnByAlias('rgt')?:'rgt').'`' ;
	}
	
	/**
	 * 将自己插入到 $aCategory 后面，与 $aCategory 同等层级
	 */
	public function insertBefore(Category $aCategory=null)
	{
		$this->insertCategoryToPoint(
			$aCategory===self::end?  self::end: $aCategory->rightPoint()+1
		) ;
	}
	
	/**
	 * 将自己做为 $aCategory 的下级分类，插入到 $aCategory 的末尾
	 */
	public function insertEndOf(Category $aCategory)
	{
		$this->insertCategoryToPoint( $aCategory->rightPoint() ) ;
	}
	
	/**
	 * 
	 * 添加一个节点到目标($nTarget)位置
	 * 
	 * 如果传入的是目标的lft,则加到目标左侧同级位置
	 * 如果传入目标的rgt,则加到目标内部末尾
	 * 如果传入本类top属性,则加载整个队列最前
	 * 如果传入本类end属性,则加在整个队列的最后
	 * 
	 * @param int $nTarget 目标位置
	 * @throws CategoryPointException
	 */
	public function insertCategoryToPoint($nTarget=self::end)
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$nOriLft = $this->leftPoint() ;
		$nOriRgt = $this->rightPoint() ;
				
		$sLftClm = $this->leftColumn() ;
		$sRgtClm = $this->rightColumn() ;
		
		$aSqlFactory = StatementFactory::singleton() ;
		
		if($nTarget===self::end)
		{
			$nTarget = $this->endRightFoot($aOrmPrototype,$sRgtClm) + 1 ;
		}
		if($nTarget<1)
		{
			throw new CategoryPointException("插入临接表时，目标位置不得小于1，传入的值为：%d",$nTarget) ;
		}
		
		// 检查是否交叉
		if( $nTarget!=0 and $nTarget>=$nOriLft and $nTarget<=$nOriRgt )
		{
			throw new CategoryPointException("无法将临接记录(%d-%d)移动到位置%d的右侧",array($nOriLft,$nOriRgt,$nTarget)) ;
		}
		
		// 检查当前分类的左右点是否有效
		$this->checkPoints($nOriLft,$nOriRgt) ;

		$aUpdate = $aSqlFactory->createUpdate($aOrmPrototype->tableName()) ;
		$aUpdate->criteria()->setLimit(-1) ;
			
		// 移动已经存在的记录
		if( $nOriRgt )
		{
			// 整体转移到0以前
			// --------------------
			$nTmpMove = $nOriRgt+1 ;
			$this->moveCategory($aUpdate,-$nTmpMove,$nOriLft,$nOriRgt,$sLftClm,$sRgtClm) ;
			
			// 移动 源位置 和 目标位置 之间的记录
			// --------------------
			$nMove = $nOriRgt<$nTarget? ($nOriLft-$nOriRgt-1): ($nOriRgt+1-$nOriLft) ;
			
			// 从左向右移动
			if($nOriRgt<$nTarget)
			{
				$this->moveFeet($aUpdate,$sLftClm,nMove,$nOriRgt,$nTarget) ;	// 移动 lft
				$this->moveFeet($aUpdate,$sRgtClm,nMove,$nOriRgt,$nTarget) ;	// 移动 rgt
			}
			// 从右向左移动
			else
			{
				$this->moveFeet($aUpdate,$sLftClm,nMove,$nTarget-1,$nOriLft) ;	// 移动 lft
				$this->moveFeet($aUpdate,$sRgtClm,nMove,$nTarget-1,$nOriLft) ;	// 移动 rgt
			}
					
			// 将 源记录 移动至 目标位置
			// --------------------
			$this->moveCategory( $aUpdate, abs($nMove)+$nTarget, null, 0, $sLftClm, $sRgtClm ) ;
		}
		
		// 插入新记录
		else
		{
			// 腾出空间
			// --------------------
			// 移动 lft
			$this->moveFeet($aUpdate,$sLftClm,2,$nTarget-1,null) ;	
			// 移动 rgt
			$this->moveFeet( $aUpdate,$sRgtClm,2,$nTarget-1,null) ;	
			
			$this->setData('lft', $nTarget) ;
			$this->setData('rgt', $nTarget+1) ;
			$this->save() ;
		}
	}
	
	/**
	 * 删除分类
	 */
	public function delete()
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$nOriLft = $this->leftPoint() ;
		$nOriRgt = $this->rightPoint() ;
		$sLftClm = $this->leftColumn() ;
		$sRgtClm = $this->rightColumn() ;
		
		DB::singleton()->execute("delete from ".$aOrmPrototype->tableName()." where {$sLftClm}>={$nOriLft} and {$sRgtClm}<={$nOriRgt}") ;
		
		$nMove = $nOriRgt-$nOriLft+1 ;
		$aUpdate = StatementFactory::singleton()->createUpdate($aOrmPrototype->tableName()) ;
		
		$this->moveFeet($aUpdate,$sLftClm,-$nMove,$nOriLft) ;
		$this->moveFeet($aUpdate,$sRgtClm,-$nMove,$nOriLft) ;
		
		
		// Unserialize
		$fnSetUnserialize = function(Category $aCategory,$fnSetUnserialize)
		{
			$aCategory->setSerialized(false) ;
			foreach($aCategory->childCategoryIterator() as $aChildCategory)
			{
				$fnSetUnserialize($aChildCategory,$fnSetUnserialize) ;
			}
		} ;
		
		$fnSetUnserialize($this,$fnSetUnserialize) ;
	}
	
	/**
	 * 建立分类所属关系的树形结构
	 * $aCategories 中的分类必须按照 lft 排序
	 * 
	 * 改造参数$aCategories中的元素,将元素间的关系保存在元素的属性中,$aCategories的迭代顺序依然不变
	 * 如果提供 $aRoot 参数，则将所有第一层分类add给 $aRoot分类，并将add后的$aRoot作为函数返回值返回,
	 * 如果 $aRoot=null，则返回一个包含所有 第一层分类的数组
	 */
	static public function buildTree(\Iterator $aCategories,self $aRoot=null)
	{
		$aParentStack = new Stack() ;
		
		foreach($aCategories as $aCategory)
		{
			for(; $aParent=$aParentStack->get(); $aParentStack->out() )
			{
				if( $aParent->lft < $aCategory->lft and $aParent->rgt > $aCategory->rgt )
				{
					break ;
				}
			}
			
			if($aParent)
			{
				$aParent->addChildCategory($aCategory) ;
			}
			else
			{
				if($aRoot)
				{
					$aRoot->addChildCategory($aCategory) ;
				}
				else
				{
					$arrTopCategories[] = $aCategory ;
				}
			}
			
			$aParentStack->put($aCategory) ;
		}
		
		return $aRoot?: (isset($arrTopCategories)?$arrTopCategories:array()) ;
	}
	
	/**
	 * 加载原型中的所有分类,作为迭代器返回
	 * 返回的迭代器的元素中不包含他们之间的关系,如果需要分类间的关系,使用本类的buildTree方法
	 * @return \Iterator
	 */
	static public function loadTotalCategory(Prototype $aPrototype)
	{
		$aCategoryList = new Model($aPrototype,true) ;
		
		$aCriteria = clone $aPrototype->criteria() ;
		$aCriteria->addOrderBy('lft',false) ;
		
		if( !$aCategoryList->load($aCriteria) )
		{
			return new \EmptyIterator();
		}
		
		return $aCategoryList->childIterator() ;
	}
	
	/**
	 * 加载所有属于自己的下级分类，并建立树形结构
	 */
	public function loadTree()
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$aCategoryList = new Model($aOrmPrototype,true) ;
		
		$aCriteria = clone $aOrmPrototype->criteria() ;
		$aCriteria->addOrderBy('lft',false)
				->where()
					->gt('lft',$aRootCategory->lft)
					->lt('rgt',$aRootCategory->rgt) ;
			
		if( !$aCategoryList->load($aCriteria) )
		{
			return ;
		}
		
		self::buildTree($aCategoryList->childIterator(),$this) ;
	}
	
	public function childCategoryIterator()
	{
		return new \ArrayIterator($this->arrChildCategories) ;
	}
	
	public function depth()
	{
		return (int) $this->__category_depth ;
	}
	public function setDepth($nDepth)
	{
		return $this->__category_depth = $nDepth ;
	}
	
	private function addChildCategory(Category $aCategory)
	{
		$this->addChild($aCategory) ;
		
		if( !in_array($aCategory,$this->arrChildCategories,true) )
		{
			$this->arrChildCategories[] = $aCategory ;
		}
		
		$aCategory->setDepth($this->depth()+1) ;
	}
	
	private function endRightFoot(Prototype $aPrototype,$sRightColumn=null)
	{
		$sTableName = $aPrototype->tableName() ;
		if(!$sRightColumn)
		{
			$sRightColumn = $this->rightColumn() ;
		}
		
		$aRecords = DB::singleton()->query("select {$sRightColumn} as rgt from {$sTableName} order by {$sRightColumn} desc limit 1 ;") ;
		if( !$aRecords->rowCount() )
		{
			return 0 ;
		}
		return (int)$aRecords->field('rgt') ;
	}
	
	private function moveFeet(Update $aUpdate,$sColumn,$nMove,$gtLft=null,$ltRgt=null)
	{
		$aCriateria = $aUpdate->criteria() ;
		$aCriateria->where()->clear() ;
		if($gtLft!==null)
		{
			$aCriateria->where()->gt($sColumn,$gtLft) ;
		}
		if($ltRgt!==null)
		{
			$aCriateria->where()->lt($sColumn,$ltRgt) ;
		}
		
		$aUpdate->clearData() ;
		$aUpdate->set($sColumn,"{$sColumn}+${nMove}") ;
		
		DB::singleton()->execute($aUpdate) ;
	}
	
	private function moveCategory(Update $aUpdate,$nMove,$gtLft,$ltRgt,$sLftClm,$sRgtClm)
	{
		$aCriateria = $aUpdate->criteria() ;
		$aCriateria->where()->clear() ;
		
		if($gtLft!==null)
		{
			$aCriateria->where()->gt('lft',$gtLft) ;
		}
		if($ltRgt!==null)
		{
			$aCriateria->where()->lt('rgt',$ltRgt) ;
		}
		
		$aUpdate->clearData() ;
		$aUpdate->set('lft',"{$sLftClm}+${nMove}") ;
		$aUpdate->set('rgt',"{$sRgtClm}+${nMove}") ;
		
		DB::singleton()->execute($aUpdate) ;
	}
	
	static public function checkPoints($nLft,$nRgt)
	{		
		if( !$nLft and !$nRgt )
		{
			return true ;
		}
		
		if(  !$nLft and $nRgt  )
		{
			throw new CategoryPointException("临接表记录Left/Right值无效，数据可能已经被损坏：%d/%d",array($nLft,$nRgt)) ;
		}
		else if(  $nLft and !$nRgt  )
		{
			throw new CategoryPointException("临接表记录Left/Right值无效，数据可能已经被损坏：%d/%d",array($nLft,$nRgt)) ;
		}
		else
		{
			if( $nLft>=$nRgt )
			{
				throw new CategoryPointException("临接表记录Left/Right值无效，数据可能已经被损坏：%d/%d",array($nLft,$nRgt)) ;
			}
			else if( (($nRgt-$nLft)%2)!=1 )
			{
				throw new CategoryPointException("临接表记录Left/Right值无效，数据可能已经被损坏：%d/%d",array($nLft,$nRgt)) ;
			}
			else
			{
				return true ;
			}
		}
	}
	
	private $arrChildCategories = array() ;
}
