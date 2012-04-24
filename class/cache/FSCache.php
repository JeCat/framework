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
namespace org\jecat\framework\cache ;

use org\jecat\framework\fs\Folder;

class FSCache extends Cache
{
	public function __construct($sFolder)
	{
		$this->sFolderPrefix = trim($sFolder,'/').'/' ;
	}
	
	public function item($sDataPath)
	{
		$nExpireTime = $this->expireTime($sDataPath) ;
		if( $nExpireTime<0 )
		{
			return null ;
		}
		else if( $nExpireTime>0 and $nExpireTime<time() )
		{
			$this->delete($sDataPath) ;
			return null ;
		}
		
		$sDataPath = $this->sFolderPrefix.trim($sDataPath,'/') ;
		
		// 尝试 .php
		if( is_file($sDataPath.'.php') )
		{
			return include $sDataPath.'.php' ;
		}
		// 尝试 .data
		else if( is_file($sDataPath.'.data') )
		{
			return unserialize(file_get_contents($sDataPath.'.data')) ;
		}

		return null ;
	}
	
	public function setItem($sDataPath,$data,$nExpire=self::expire_default,$fCreateTimeMicroSec=-1)
	{
		$sDataPath = $this->sFolderPrefix.trim($sDataPath,'/') ;
			
		if(is_object($data))
		{
			$sSerialize = serialize($data) ;
			$sFilePath = $sDataPath.'.data' ;
		}
		else
		{
			$sSerialize = "<?php\r\nreturn ".var_export($data,true).' ;' ;
			$sFilePath = $sDataPath.'.php' ;
		}
		
		$sDataFolder = dirname($sFilePath) ;
		if( !is_dir($sDataFolder) and !Folder::createInstance($sDataFolder)->create() )
		{
			return ;
		}
		
		file_put_contents( $sFilePath,$sSerialize) ;
		
		// create time
		if($fCreateTimeMicroSec<0)
		{
			$fCreateTimeMicroSec = microtime(true) ;
		}
		
		// expire time
		if( $nExpire===Cache::expire_allways )
		{
			$nExpireSec = 0 ;
		}
		else if( $nExpire===Cache::$expire_default )
		{
			$nExpireSec = ceil($fCreateTimeMicroSec) + Cache::$expire_default ;
		}
		else
		{
			$nExpireSec = ceil($fCreateTimeMicroSec) + $nExpire ;
		}

		file_put_contents( $sDataPath.'.time', "<?php return array({$fCreateTimeMicroSec},{$nExpireSec}) ;" ) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 */
	public function delete($sDataPath)
	{
		// 所有
		if( empty($sDataPath) )
		{
			$this->clear() ;
		}
		else
		{
			$sDataPath = $this->sFolderPrefix.trim($sDataPath,'/') ;
			
			// 删除目录
			if( is_dir($sDataPath) )
			{
				Folder::createInstance($sDataPath)->delete(true,true) ;
			}
			// 删除指定内容
			else
			{
				@unlink($sDataPath.'.data') ;
				@unlink($sDataPath.'.php') ;
				@unlink($sDataPath.'.time') ;
			}
		}
	}

	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function clear()
	{
		return Folder::createInstance($this->sFolderPrefix)->delete(true,true) ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function isExpire($sDataPath)
	{
		$nExpireTime = $this->expireTime($sDataPath) ;
		return $nExpireTime>0 and $nExpireTime<time() ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return float
	 */
	public function createTime($sDataPath)
	{
		$sPath = $this->sFolderPrefix.trim($sDataPath,'/').'.time' ;
		if(!is_file($sPath))
		{
			return 0 ;
		}
		list($fCreateTime,) = explode(',',file_get_contents($sPath)) ;
		return (int)$fCreateTime ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	public function expireTime($sDataPath)
	{
		$sPath = $this->sFolderPrefix.trim($sDataPath,'/').'.time' ;
		if(!is_file($sPath))
		{
			return 0 ;
		}
		list(,$nExpireTime) = explode(',',file_get_contents($sPath)) ;
		return (int)$nExpireTime ;
	}
	
	/**
	 * @var org\jecat\framework\fs\FsFolder
	 */
	private $aFolder ;
}

