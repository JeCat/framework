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
	public function & openFile($sPath,$sMode,$options,&$opened_path)
	{
		$opened_path = $this->sRootPath.'/'.$sPath ;
		return fopen($opened_path,$sMode) ;
	}
	/**
	 * @return void
	 */
	public function closeFile(&$resource)
	{
		return fclose($hResource) ;
	}
	/**
	 * @return bool
	 */
	public function endOfFile(&$resource)
	{
		return feof($hResource) ;
	}
	/**
	 * @return bool
	 */
	public function lockFile(&$resource)
	{
		return lock($hResource) ;
	}
	/**
	 * @return bool
	 */
	public function flushFile(&$resource)
	{
		return flush($hResource) ;
	}
	/**
	 * @return string
	 */
	public function readFile(&$resource,$nLength)
	{
		return fread($hResource,$nLength) ;
	}
	/**
	 * @return bool
	 */
	public function seekFile(&$resource,$offset,$whence=SEEK_SET)
	{
		return fread($hResource,$offset,$whence) ;
	}
	/**
	 * @return bool
	 */
	public function tellFile(&$resource)
	{
		return ftell($hResource) ;
	}
	/**
	 * @return int
	 */
	public function writeFile(&$resource,$data)
	{
		return fwrite($hResource,$data) ;
	}

	/**
	 * @return bool
	 */
	public function unlinkFile($sPath)
	{
		return unlink($this->sRootPath.'/'.$sPath) ;
	}
	/**
	 * @return array
	 */
	public function stat($sPath,$flags)
	{
		return stat($this->sRootPath.'/'.$sPath) ;
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
	/**
	 * @return bool
	 */
	public function closedir($hResource)
	{
		return closedir($hResource) ;
	}
	/**
	 * @return bool
	 */
	public function rewinddir($hResource)
	{
		return rewinddir($hResource) ;
	}
	/**
	 * @return bool
	 */
	public function mkdir($sPath,$nMode,$options)
	{
		return mkdir( $this->sRootPath.'/'.$sPath, $nMode, $options ) ;
	}
	/**
	 * @return bool
	 */
	public function rename($sFrom,$sTo)
	{
		return rename( $this->sRootPath.'/'.$sFrom, $this->sRootPath.'/'.$sTo ) ;
	}
	/**
	 * @return bool
	 */
	public function rmdir($sPath,$options)
	{
		return rmdir( $this->sRootPath.'/'.$sPath ) ;
	}
	
	
	public function url($sPath)
	{
		return $this->sRootPath.'/'.$sPath ;
	}
	
	
	private $sRootPath ;
}

