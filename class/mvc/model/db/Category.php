<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\util\Stack;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\db\sql\Update;
use org\jecat\framework\db\DB;

class Category extends Object 
{	
	const top = 1 ;
	const end = null ;
	
	public function __construct(Model $aModel)
	{
		$this->aModel = $aModel ;
	}
	
	/**
	 * 将自己插入到 $aCategory 后面，与 $aCategory 同等层级
	 */
	public function insertBefore(Model $aCategoryModel=null)
	{
		$this->insertCategoryToPoint(
			$aCategoryModel===self::end?  self::end: self::rightPoint($aCategoryModel)+1
		) ;
	}
	
	/**
	 * 将自己做为 $aCategory 的下级分类，插入到 $aCategory 的末尾
	 */
	public function insertEndOf(Model $aCategoryModel)
	{
		$this->insertCategoryToPoint( self::rightPoint($aCategoryModel) ) ;
	}
	
	/**
	 * 
	 * 移动一个节点到目标($nTarget)位置
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
		if( !$aOrmPrototype = $this->aModel->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$nOriLft = self::leftPoint($this->model()) ;
		$nOriRgt = self::rightPoint($this->model()) ;
				
		$sLftClm = self::leftColumn($aOrmPrototype) ;
		$sRgtClm = self::rightColumn($aOrmPrototype) ;
		
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

		$aUpdate = new Update($aOrmPrototype->tableName()) ;
		$aUpdate->criteria()->setLimit(-1) ;
			
		// 移动已经存在的记录
		if( $nOriRgt )
		{
			// 整体转移到0以前
			// --------------------
			$nTmpMove = $nOriRgt+1 ;
			$this->moveCategory($aUpdate,-$nTmpMove,$nOriLft-1,$nOriRgt+1,$sLftClm,$sRgtClm) ;
			
			// 移动 源位置 和 目标位置 之间的记录
			// --------------------
			if($nOriRgt<$nTarget)		// 从左向右移动
			{
				$nSpeceMove = $nOriLft-$nOriRgt-1 ;
				$nTmpMove = $nTarget ;	// 目标位置已经改变,实际一定距离不用考虑 $nSpeceMove 
			}
			else							// 从右向左移动
			{
				$nSpeceMove = $nOriRgt+1-$nOriLft ;
				$nTmpMove = abs($nSpeceMove)+$nTarget ;
			}
			
			// 从左向右移动
			if($nOriRgt<$nTarget)
			{
				$this->moveFeet($aUpdate,$sLftClm,$nSpeceMove,$nOriRgt,$nTarget) ;	// 移动 lft
				$this->moveFeet($aUpdate,$sRgtClm,$nSpeceMove,$nOriRgt,$nTarget) ;	// 移动 rgt
			}
			// 从右向左移动
			else
			{
				$this->moveFeet($aUpdate,$sLftClm,$nSpeceMove,$nTarget-1,$nOriLft) ;	// 移动 lft
				$this->moveFeet($aUpdate,$sRgtClm,$nSpeceMove,$nTarget-1,$nOriLft) ;	// 移动 rgt
			}
					
			// 将 源记录 移动至 目标位置
			// --------------------
			$this->moveCategory( $aUpdate, $nTmpMove, null, 0, $sLftClm, $sRgtClm ) ;
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
			
			$this->aModel->setData('lft', $nTarget) ;
			$this->aModel->setData('rgt', $nTarget+1) ;
			$this->aModel->save() ;
		}
	}
	
	/**
	 * 删除分类
	 */
	public function delete()
	{
		if( !$aOrmPrototype = $this->aModel->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$nOriLft = self::leftPoint($this->model()) ;
		$nOriRgt = self::rightPoint($this->model()) ;
		$sLftClm = self::leftColumn($aOrmPrototype) ;
		$sRgtClm = self::rightColumn($aOrmPrototype) ;
		
		DB::singleton()->execute("delete from ".$aOrmPrototype->tableName()." where {$sLftClm}>={$nOriLft} and {$sRgtClm}<={$nOriRgt}") ;
		
		$nMove = $nOriRgt-$nOriLft+1 ;
		$aUpdate = new Update($aOrmPrototype->tableName()) ;
		
		$this->moveFeet($aUpdate,$sLftClm,-$nMove,$nOriLft) ;
		$this->moveFeet($aUpdate,$sRgtClm,-$nMove,$nOriLft) ;
	}
	
	/**
	 * 建立分类所属关系的树形结构
	 * $aModelList 中的子元素必须拥有 lft 字段值
	 * 
	 * 改造参数$aModelList中的元素,将元素间的关系保存在元素的属性中,$aModelList的迭代顺序依然不变
	 * 如果 $bReturnTopModels=true 则返回一个包含所有 第一层分类的数组
	 */
	static public function buildTree(ModelList $aModelList,$bReturnTopModels=false)
	{
		$aParentStack = new Stack() ;
		if($bReturnTopModels)
		{
			$arrTopCategories = array() ;
		}
		
		// 按照 lft 排序
		$aModelList->sortChildren( function (Model $aModelA,Model $aModelB) {
			
			if( $aModelA->lft > $aModelB->lft )
			{
				return 1 ;
			}
			else if( $aModelA->lft==$aModelB->lft )
			{
				return 0 ;
			}
			else
			{
				return -1 ;
			}
			
		} ) ;
		
		foreach($aModelList as $nIdx=>$aModel)
		{
			for(; ($nParentIdx=$aParentStack->get())!==false; $aParentStack->out() )
			{
				if( $aModelList->data('lft',$nParentIdx) < $aModel->lft and $aModelList->data('rgt',$nParentIdx) > $aModel->rgt )
				{
					break ;
				}
			}
			
			if($nParentIdx!==false)
			{
				self::addChildCategory($aModelList,$nParentIdx,$nIdx) ;
			}
			else
			{
				self::setDepth($aModel,0) ;
				
				if($bReturnTopModels)
				
				{
					$arrTopCategories[] = $nIdx ;
				}
			}
			$aParentStack->put($nIdx) ;
		}
		
		return $bReturnTopModels? new ModelListIterator($aModelList,$arrTopCategories): null ;
	}
	
	/**
	 * 加载原型中的所有分类,
	 * 返回的迭代器的元素中不包含他们之间的关系,如果需要分类间的关系,使用本类的buildTree方法
	 * @return Model
	 */
	static public function load(Model $aModelList)
	{		
		$aCriteria = clone $aModelList->prototype()->criteria() ;
		$aCriteria->addOrderBy('lft',false) ;
		
		if( !$aModelList->load($aCriteria) )
		{
			return new \EmptyIterator();
		}
		
		return $aModelList ;
	}
	
	/**
	 * 加载所有属于自己的下级分类，并建立树形结构
	 */
	public function loadTree()
	{
		if( !$aOrmPrototype = $this->aModel->prototype() )
		{
			throw new CategoryPointException("尚未为临接表模型设置原型，无法完成操作") ;
		}
		
		$aCategoryList = new ModelList($aOrmPrototype) ;
		
		$aCriteria = clone $aOrmPrototype->criteria() ;
		$aCriteria->addOrderBy('lft',false)
				->where()
					->gt('lft',$aRootCategory->lft)
					->lt('rgt',$aRootCategory->rgt) ;
			
		if( !$aCategoryList->load($aCriteria) )
		{
			return ;
		}
		
		self::buildTree($aCategoryList) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function childCategoryIterator()
	{
		return new ModelListIterator(
				ModelList::belongsModelList($this->model())
				, $this->model()->data('__category_children')?: array()
		) ;
	}
	
	static public function depth(Model $aModel)
	{
		return (int) $aModel->data('__category_depth') ;
	}
	static public function setDepth(Model $aModel,$nDepth)
	{
		$aModel->setData('__category_depth',(int)$nDepth) ;
	}
	
	static private function addChildCategory(ModelList $aModelList,$nParentModelIndex,$nChildModelIndex)
	{		
		$arrChildren = $aModelList->data('__category_children',$nParentModelIndex)?: array() ;
		if(!in_array($nChildModelIndex,$arrChildren))
		{
			$arrChildren[] = $nChildModelIndex ;
		}
		$aModelList->setData('__category_children',$arrChildren,$nParentModelIndex) ;
		
		$aModelList->setData(
				'__category_depth'
				, $aModelList->data('__category_depth',$nParentModelIndex)+1
				, $nChildModelIndex
		) ;
	}
	
	private function endRightFoot(Prototype $aPrototype,$sRightColumn=null)
	{
		$sTableName = $aPrototype->tableName() ;
		if(!$sRightColumn)
		{
			$sRightColumn = self::rightColumn($aPrototype) ;
		}
		
		$aRecords = DB::singleton()->query("select {$sRightColumn} as rgt from {$sTableName} order by {$sRightColumn} desc limit 1 ;") ;
		if( !$aRecords->rowCount() )
		{
			return 0 ;
		}
		$arrRow = $aRecords->fetch() ;
		return (int)$arrRow['rgt'] ;
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
		$aUpdate->setData($sColumn,"{$sColumn}+${nMove}",true) ;
		
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
		$aUpdate->setData('lft',"{$sLftClm}+${nMove}",true) ;
		$aUpdate->setData('rgt',"{$sRgtClm}+${nMove}",true) ;
		
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
	
	static public function leftPoint(Model $aModel)
	{
		return (int)$aModel->data('lft') ;
	}
	static public function rightPoint(Model $aModel)
	{
		return (int)$aModel->data('rgt') ;
	}
	
	static public function leftColumn(Prototype $aPrototype)
	{
		return ($aPrototype->getColumnByAlias('lft')?:'lft') ;
	}
	static public function rightColumn(Prototype $aPrototype)
	{
		return ($aPrototype->getColumnByAlias('rgt')?:'rgt') ;
	}
	
	/**
	 * 
	 * @param Model $aModel 子分类的model
	 * @return ModelList 所有父分类的model，排序为顶级分类在前，子分类在后
	 */
	static public function getParents(Model $aModel )
	{
		if(!$aModel){
			return;
		}
		$aPrototype = clone $aModel->prototype();
		$aPrototype->addOrderBy('lft');
		$aParentsModelList = $aPrototype->createModel(true);
		$aParentsModelList->loadSql("lft < @1 and rgt > @2" , $aModel->lft , $aModel->rgt);
		return $aParentsModelList;
	}
	
	/**
	 *
	 * @param Model $aModel 子分类的model
	 * @return ModelList 所有子分类的model,排序高级分类在前，子分类在后
	 */
	static public function getChildren(Model $aModel )
	{
		if(!$aModel){
			return;
		}
		$aPrototype = clone $aModel->prototype();
		$aPrototype->addOrderBy('lft');
		$aParentsModelList = $aPrototype->createModel(true);
		$aParentsModelList->loadSql("lft < @1 and rgt > @2" , $aModel->lft , $aModel->rgt);
		return $aParentsModelList;
	}
	
	/**
	 * @return Model
	 */
	public function model()
	{
		return $this->aModel ; 
	}
	
	/**
	 * @var Model
	 */
	private $aModel ;
	// private $arrChildCategories = array() ;
}


