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
namespace org\jecat\framework\util;

/**
 * 版本兼容类
 *
 * @author		alee
 * @access		public
 */
class VersionCompat
{
	/**
	 * 增加兼容版本
	 *
	 * @access	public
	 * @param	$aVersion	Version	版本
	 * @return	void
	 */
	public function addCompatibleVersion(Version $aVersion)
	{
		$this->arrScopes[] = new VersionScope($aVersion,$aVersion,'>=','<=') ;
	}
	
	/**
	 * 增加兼容版本
	 *
	 * @access	public
	 * @param	$aVersionScope	VersionScope	版本范围
	 * @return	void
	 */
	public function addCompatibleVersionScope(VersionScope $aVersionScope)
	{
		$this->arrScopes[] = $aVersionScope ;
	}
	
	/**
	 * 增加兼容版本
	 * 
	 * @access	public
	 * @param	$sCompatibleVersions		string
	 * @return	void
	 */
	public function addFromString($sCompatibleVersions)
	{
		$arrScopes = preg_split('/[;\r\n]/',$sCompatibleVersions,-1,PREG_SPLIT_NO_EMPTY) ;
		foreach($arrScopes as $sScope)
		{
			$this->arrScopes[] = VersionScope::fromString($sScope) ;
		}
	}
	
	/**
	 * Description
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		$arrScopes = array() ;
		foreach ($this->arrScopes as $aScope)
		{
			$arrScopes[] = $aScope->__toString() ;
		}
		
		return implode("\r\n",$arrScopes) ;
	}
	
	/**
	 * 清空兼容版本
	 * 
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->arrScopes[] = array() ;
	}
	
	
	/**
	 * 检查一个版本是否兼容
	 * 
	 * @access	public
	 * @param	$aVersion		JCAT_Version
	 * @return	bool
	 */
	public function check($aVersion)
	{
		if($aVersion instanceof Version){
			foreach ($this->arrScopes as $aScope)
			{
				if( $aScope->isInScope($aVersion) )
				{
					return true ;
				}
			}
		}else if($aVersion instanceof VersionScope){
			foreach ($this->arrScopes as $aScope)
			{
				if( VersionScope::SEPARATE !== VersionScope::compareScope($aScope,$aVersion) )
				{
					return true ;
				}
			}
			return false;
		}
		return false ;
	}
	
	
	/**
	 * Description
	 * 
	 * @access	private
	 * @var		array
	 */
	private $arrScopes ;
}

