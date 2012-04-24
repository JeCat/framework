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
namespace org\jecat\framework\auth ;

class GroupPermission extends PermissionBase
{
	public function check(IdManager $aIdManager) 
	{
		$bAllow = false ;
		foreach($this->iterator() as $aPermission)
		{
			// bingo !
			if( $aPermission->check($aIdManager) ) 
			{
				$bAllow = true ;
			}
			
			// deny
			else
			{
				if( $aPermission->isNecessary() )
				{
					return false ;
				}
			}
		}

		return $bAllow ;
	}
	
	public function add(IPermission $aPermission,$bRestrict=false)
	{
		if( !$this->arrPermissions or !in_array($aPermission,$this->arrPermissions,$bRestrict) )
		{
			$this->arrPermissions[] = $aPermission ;
		}
		return $this ;
	}
	
	public function remove(IPermission $aPermission,$bRestrict=false)
	{
		if($this->arrPermissions)
		{
			$pos = array_search($aPermission,$this->arrPermissions,$bRestrict) ;
			if($pos!==false)
			{
				unset($this->arrPermissions[$pos]) ;
			}
		}
		return $this ;
	}
	
	public function clear()
	{
		$this->arrPermissions = null ;
		return $this ;
	}
	
	public function has(IPermission $aPermission,$bRestrict=false)
	{
		return $this->arrPermissions and array_search($aPermission,$this->arrPermissions,$bRestrict)!==false ;
	}
	
	public function iterator()
	{
		return $this->arrPermissions ?
					new \ArrayIterator($this->arrPermissions):
					new \EmptyIterator() ;
	}

	private $arrPermissions ;
}


