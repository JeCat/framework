<?php
namespace org\jecat\framework\fs\vfs ;


class VirtualFileSystem
{
	static public function flyweight($sProtocol='vfs')
	{
		if( !isset(self::$arrProtocols[$sProtocol]) )
		{
			self::$arrProtocols[$sProtocol] = new self($sProtocol) ;
		}
		
		return self::$arrProtocols[$sProtocol] ;
	}
	static public function setFlyweight(self $aInstance,$sProtocol='vfs')
	{
		$arrProtocols[$sProtocol] = $aInstance ;
	}
	
	public function __construct($sProtocol)
	{
		$this->sProtocol = $sProtocol ;
		stream_register_wrapper($sProtocol,'org\\jecat\\framework\\fs\\vfs\\VFSWrapper') ;
	}
	
	static public function findFileSystemByUrl($sUrl)
	{
		$arrUrlInfo = parse_url($sUrl) ;
		if( !$aVfs=VirtualFileSystem::flyweight($arrUrlInfo['scheme']) )
		{
			return null ;
		}
		
		return $aVfs->fileSystem($arrUrlInfo['host'].$arrUrlInfo['path']) ;
	}
	static public function localeFileSystemByUrl($sUrl)
	{
		$arrUrlInfo = parse_url($sUrl) ;
		if( !$aVfs=VirtualFileSystem::flyweight($arrUrlInfo['scheme']) )
		{
			return null ;
		}
		
		return $aVfs->localeFileSystemPath($arrUrlInfo['host'].$arrUrlInfo['path']) ;
	}

	/**
	 * 挂载一个物理文件系统
	 */
	public function mount($sPath,IPhysicalFileSystem $aConcreteFileSystem)
	{
		$sMountPoint = trim($sPath,'/') ;
		$this->arrConcreteFileSystem[$sMountPoint] = $aConcreteFileSystem ;
	
		krsort($this->arrConcreteFileSystem) ;
	}
	
	/**
	 * 卸载一个物理文件系统
	 */
	public function unmount($sPath)
	{
		$sMountPoint = trim($sPath,'/') ;
		unset($this->arrConcreteFileSystem[$sMountPoint]) ;
	}

	/**
	 * 通过确切的挂载点返回一个物理文件系统对像
	 *
	 * @param string $sMountPoint 是一个路径，左右两端不需要斜线
	 * @return IPhysicalFileSystem
	 */
	public function fileSystem($sMountPoint)
	{
		return isset($this->arrConcreteFileSystem[$sMountPoint])?
			$this->arrConcreteFileSystem[$sMountPoint]: null ;
	}
	
	/**
	 * 找到给入的路径所属的物理文件系统
	 * 
	 * @param string $sMountPoint 是一个路径，左右两端不需要斜线
	 * @return IPhysicalFileSystem
	 */
	public function findFileSystem($sPath)
	{
		$sPath = trim($sPath,'/').'/' ;
		
		foreach($this->arrConcreteFileSystem as $sMountPoint=>$aConcreteFileSystem)
		{
			if( strpos( $sPath, $sMountPoint.'/' ) === 0 )
			{
				return $aConcreteFileSystem ;
			}
		}
		
		return null ;
	}

	/**
	 * 返回给入的路径所属的物理文件系统 以及 在所属文件系统中的相对路径
	 *
	 * @param string $sMountPoint 是一个路径，左右两端不需要斜线
	 * @return array(IPhysicalFileSystem,string)
	 */
	public function localeFileSystemPath($sPath)
	{
		$sPath = trim($sPath,'/').'/' ;
	
		foreach($this->arrConcreteFileSystem as $sMountPoint=>$aConcreteFileSystem)
		{
			if( strpos( $sPath, $sMountPoint.'/' ) === 0 )
			{
				return array(
					$aConcreteFileSystem
					, substr( $sPath, strlen($sMountPoint)+1, -1 )?: ''
				) ;
			}
		}
	
		return null ;
	}
	
	public function mountPoints()
	{
		return array_keys($this->arrConcreteFileSystem) ;
	}
	
	private $sProtocol ;
	private $arrConcreteFileSystem ;
	
	static private $arrProtocols = array() ;
}

