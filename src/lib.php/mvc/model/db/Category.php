<?php
namespace jc\mvc\model\db ;


use jc\db\sql\StatementFactory;

use jc\db\sql\Update;

use jc\db\DB;

use jc\lang\Exception;

class Category extends Model 
{	
	const top = 0 ;
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
	
	public function insertCategory(Category $aRightOf=null)
	{
		$this->insertCategoryToPoint(
			$aRightOf? $aRightOf->rightPoint(): self::top
		) ;
	}
	
	public function insertCategoryToPoint($nRightOf=self::end)
	{
		if( !$aOrmPrototype = $this->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$nOriLft = $this->leftPoint() ;
		$nOriRgt = $this->rightPoint() ;
		
		$aSqlFactory = $aOrmPrototype->statementFactory() ;
		
		if($nRightOf==self::end)
		{
			$nRightOf = $this->endRightFoot($aOrmPrototype) ;
		}
		
		// 检查是否交叉
		if( $nRightOf>=$nOriLft and $nRightOf<=$nOriRgt )
		{
			throw new CategoryPointException("无法将临接记录(%d-%d)移动到位置%d的右侧",array($nOriLft,$nOriRgt,$nRightOf)) ;
		}
		
		// 检查当前分类的左右点是否有效
		$this->checkPoints($nOriLft,$nOriRgt) ;

		$aUpdate = $aOrmPrototype->statementUpdate() ;
		
		$sLftClm = $this->leftColumn() ;
		$sRgtClm = $this->rightColumn() ;'`'.$aOrmPrototype->getColumnByAlias('rgt').'`' ;
			
		// 移动已经存在的记录
		if( $nOriRgt )
		{
			// 整体转移到0以前
			// --------------------
			$nTmpMove = $nOriRgt+1 ;
			$this->moveCategory($aUpdate,-$nTmpMove,$nOriLft,$nOriRgt,$sLftClm,$sRgtClm) ;
			
			// 移动 源位置 和 目标位置 之间的记录
			// --------------------
			$nMove = $nOriRgt<$nRightOf? ($nOriLft-$nOriRgt-1): ($nOriRgt+1-$nOriLft) ;
			
			// 移动 lft
			$this->moveFeet($aUpdate,'lft',nMove,$nOriRgt,$nRightOf,$sLftClm) ;
			
			// 移动 rgt
			$this->moveFeet( $aUpdate,'rgt',nMove,$nOriRgt
						, $nRightOf + ( $nOriRgt<$nRightOf? 0: 1 )		// << 从右向左移动时，目标位置上的记录不变 
																		// >> 当从左向右移动时，目标位置上的记录也需要左移 以便空出空间
						, $sRgtClm ) ;
			
			// 将 源记录 移动至 目标位置
			// --------------------
			$nMove = abs($nMove) + $nRightOf+1 ;
			$this->moveCategory($aUpdate,$nMove,null,0,$sLftClm,$sRgtClm) ;
		}
		
		// 插入新记录
		else
		{
			// 腾出空间
			// --------------------
			// 移动 lft
			$this->moveFeet($aUpdate,'lft',2,$nRightOf,null,$sLftClm) ;
			// 移动 rgt
			$this->moveFeet( $aUpdate,'rgt',2,null,$nRightOf,$sRgtClm ) ;
			
			$this->setData('lft', $nRightOf+1) ;
			$this->setData('right', $nRightOf+2) ;
			$this->save() ;
		}
	}
	
	public function deleteCategory()
	{
		
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
	
	private function moveFeet(Update $aUpdate,$foot,$nMove,$gtLft,$ltRgt,$sColumn)
	{
		$aCriateria = $aUpdate->criteria() ;
		$aCriateria->restriction()->clear() ;
		if($gtLft!==null)
		{
			$aCriateria->restriction()->gt($foot,$gtLft) ;
		}
		if($ltRgt!==null)
		{
			$aCriateria->restriction()->lt($foot,$ltRgt) ;
		}
		
		$aUpdate->clearData() ;
		$aUpdate->set($foot,"{$sColumn}+${nMove}") ;
		
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
}

?>