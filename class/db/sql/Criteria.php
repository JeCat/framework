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
namespace org\jecat\framework\db\sql ;

class Criteria extends SQL
{	
	// -- limit --
	
	/**
	 *  设置limit条件
	 * @param int $nLimitLen limit 长度
	 * @param int $sLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen,$limitFrom = null)
	{
		if($nLimitLen<0)
		{
			$this->clearLimit(true) ;
		}
		else
		{
			$this->clearLimit(false) ;
		
			$arrRawLimit =& $this->rawClause(self::CLAUSE_LIMIT) ;
		
			if( $limitFrom!==null )
			{
				$arrRawLimit['subtree'][] = $limitFrom ;
				$arrRawLimit['subtree'][] = ',' ;
				$arrRawLimit['subtree'][] = $nLimitLen ;
			}
			else
			{
				$arrRawLimit['subtree'][] = (int)$nLimitLen ;
			}
		}
	
		return $this ;
	}
	
	public function clearLimit($bRemoveCluuse=true)
	{
		if($bRemoveCluuse)
		{
			unset($this->arrRawSql['subtree'][self::CLAUSE_LIMIT]) ;
		}
		else
		{
			if(isset($this->arrRawSql['subtree'][self::CLAUSE_LIMIT]))
			{
				$this->arrRawSql['subtree'][self::CLAUSE_LIMIT]['subtree'] = array() ;
			}
		}
	}
	
	// -- where --
	public function setWhere(Restriction $aWhere){
		$this->aWhere = $aWhere;
		$this->setRawWhere( $aWhere->rawSql() ) ;
		return $this ;
	}
	/**
	 * @return Restriction
	 */
	public function where($bAutoCreate=true)
	{
		$arrRawWhere =& $this->rawClause(self::CLAUSE_WHERE) ;
	
		if( !$this->aWhere and $bAutoCreate )
		{
			$this->aWhere = new Restriction() ;
			$this->aWhere->setRawSql($arrRawWhere) ;
		}
	
		return $this->aWhere ;
	}
	
	
	// -- order by --
	public function addOrderBy($sColumn,$bDesc=true,$sTable=null)
	{
		$arrRawOrder =& $this->rawClause(self::CLAUSE_ORDER) ;
	
		if(!empty($arrRawOrder['subtree']))
		{
			$arrRawOrder['subtree'][] = ',' ;
		}
		$arrRawOrder['subtree'][] = self::createRawColumn($sTable, $sColumn) ;
		$arrRawOrder['subtree'][] = $bDesc? 'DESC': 'ASC' ;
	
		return $this ;
	}
	
	public function clearOrders()
	{
		unset($this->arrRawSql['subtree'][self::CLAUSE_ORDER]) ;
		return $this ;
	}
	
	// -- group by --
	public function addGroupBy($sColumn,$sTable=null,$sDB=null)
	{
		$arrGroupBy =& $this->rawClause(self::CLAUSE_GROUP) ;
		if( !empty($arrGroupBy['subtree']) )
		{
			$arrGroupBy['subtree'][] = ',' ;
		}
		$arrGroupBy['subtree'][] = self::createRawColumn($sTable,$sColumn,null) ;
	
		return $this ;
	}
	
	public function clearGroupBy($bRemoveCluuse=true)
	{
		if($bRemoveCluuse)
		{
			unset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]) ;
		}
		else if(isset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]))
		{
			$this->arrRawSql['subtree'][self::CLAUSE_GROUP]['subtree'] = array() ;
		}
		return $this ;
	}
	
	public function attache(array & $arrRawSql)
	{
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_WHERE]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_WHERE] =& $this->arrRawSql['subtree'][self::CLAUSE_WHERE] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_GROUP] =& $this->arrRawSql['subtree'][self::CLAUSE_GROUP] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_ORDER]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_ORDER] =& $this->arrRawSql['subtree'][self::CLAUSE_ORDER] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_LIMIT]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_LIMIT] =& $this->arrRawSql['subtree'][self::CLAUSE_LIMIT] ;
		}
	}
	
	private $aWhere ;
}

