<?php
namespace jc\mvc\model\db ;


use jc\util\Stack;

use jc\mvc\model\db\orm\Selecter;

use jc\mvc\model\db\orm\Prototype;

use jc\db\sql\StatementFactory;

use jc\db\sql\Update;

use jc\db\DB;

use jc\lang\Exception;

class Category extends Model 
{	
	const top = 1 ;
	const end = -1 ;
	
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
			$aCategory? $aCategory->rightPoint()+1: self::end
		) ;
	}
	
	/**
	 * 将自己做为 $aCategory 的下级分类，插入到 $aCategory 的末尾
	 */
	public function insertEndOf(Category $aCategory)
	{
		$this->insertCategoryToPoint( $aCategory->rightPoint() ) ;
	}
	
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
		
		if($nTarget==self::end)
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
	public function deleteCategory()
	{
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
		
		$aCategoryList = new ModelList($aOrmPrototype) ;
		
		$aCriteria = clone $aOrmPrototype->criteria() ;
		$aCriteria	->addOrderBy('lft',false)
					->restriction()
						->gt('lft',$aRootCategory->lft)
						->lt('rgt',$aRootCategory->rgt) ;
			
		if( !$aCategoryList->load($aCriteria) )
		{
			return ;
		}
		
		$aParentStack = new Stack() ;
		$aParentStack->put($this) ;
		
		foreach($aCategoryList->childIterator() as $aCategory)
		{
			for(; $aParent=$aParentStack->get(); $aParentStack->out() )
			{
				if( $aParent->lft < $aCategory->lft() and $aParent->rgt > $aCategory->rgt )
				{
					break ;
				}
			}
			if(!$aParent)
			{
				throw new CategoryPointException("临接表操作遇到数据错误，数据表%s中的数据可能已经遭到损坏",$aOrmPrototype->tableName()) ;
			}
			
			$aParent->addChildCategory($aCategory) ;
		}
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
		$aCriateria->restriction()->clear() ;
		if($gtLft!==null)
		{
			$aCriateria->restriction()->gt($sColumn,$gtLft) ;
		}
		if($ltRgt!==null)
		{
			$aCriateria->restriction()->lt($sColumn,$ltRgt) ;
		}
		
		$aUpdate->clearData() ;
		$aUpdate->set($sColumn,"{$sColumn}+${nMove}") ;
		
		DB::singleton()->execute($aUpdate) ;
	}
	
	private function moveCategory(Update $aUpdate,$nMove,$gtLft,$ltRgt,$sLftClm,$sRgtClm)
	{
		$aCriateria = $aUpdate->criteria() ;
		$aCriateria->restriction()->clear() ;
		
		if($gtLft!==null)
		{
			$aCriateria->restriction()->gt('lft',$gtLft) ;
		}
		if($ltRgt!==null)
		{
			$aCriateria->restriction()->lt('rgt',$ltRgt) ;
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

?>