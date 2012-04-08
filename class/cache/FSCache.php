<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\util\String;
use org\jecat\framework\fs\Folder;

class FSCache extends Cache
{
	public function __construct($sFolder)
	{
		$this->aFolder = Folder::singleton()->findFolder(
			$sFolder,Folder::FIND_AUTO_CREATE
		) ;
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
		
		// 尝试 .php
		if( $sFilePath = $this->aFolder->findFile($sDataPath.'.php',Folder::FIND_RETURN_PATH) )
		{
			return include $sFilePath ;
		}
		// 尝试 .data
		if( $sFilePath = $this->aFolder->findFile($sDataPath.'.data',Folder::FIND_RETURN_PATH) )
		{
			return unserialize(file_get_contents($sFilePath)) ;
		}

		return null ;
	}
	
	public function setItem($sDataPath,$data,$nExpire=self::expire_default,$fCreateTimeMicroSec=-1)
	{
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
		if( !$sFilePath=$this->aFolder->findFile($sFilePath,Folder::FIND_AUTO_CREATE|Folder::FIND_RETURN_PATH) )
		{
			return false ;
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
		
		if( !$sFilePath=$this->aFolder->findFile($sDataPath.'.time',Folder::FIND_AUTO_CREATE|Folder::FIND_RETURN_PATH) )
		{
			return false ;
		}

		file_put_contents( $sFilePath.'.time', "<?php return array({$fCreateTimeMicroSec},{$nExpireSec}) ;" ) ;
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
			$this->aFolder->delete(true,true) ;
		}
		// 删除目录
		else if( $aFolder=$this->aFolder->findFolder($sDataPath) )
		{
			$aFolder->delete(true,true) ;
		}
		// 删除指定内容
		else
		{
			$this->aFolder->deleteChild($sDataPath.'.data') ;
			$this->aFolder->deleteChild($sDataPath.'.php') ;
			$this->aFolder->deleteChild($sDataPath.'.time') ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isExpire($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return true ;
		}

		list(,$nExpireTime) = include $sFilePath ;
		return $nExpireTime>0 and $nExpireTime<time() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	public function createTime($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return 0 ;
		}

		list($fCreateTime,) = include $sFilePath ;
		return (float)$fCreateTime ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	public function expireTime($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return -1 ;
		}
	
		list(,$nExpireTime) = include $sFilePath ;
		return (int)$nExpireTime ;
	}
	
	/**
	 * @var org\jecat\framework\fs\FsFolder
	 */
	private $aFolder ;
}
?>