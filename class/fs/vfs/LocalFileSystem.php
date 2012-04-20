<?php
namespace org\jecat\framework\fs\vfs ;

class LocalFileSystem implements IPhysicalFileSystem
{
	public function __construct($sRootPath)
	{
		$this->sRootPath = $sRootPath ;
	}

	/**
	 * @return resource
	 */
	public function opendir($sPath,$options)
	{
		return opendir($this->sRootPath.'/'.$sPath) ;
	}
	/**
	 * @return string
	 */
	public function readdir($hResource)
	{
		return readdir($hResource) ;
	}
	
	private $sRootPath ;
}

