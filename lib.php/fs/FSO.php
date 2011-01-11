<?php
namespace jc\fs ;

use jc\lang\Object;

class FSO extends Object
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($sPath='')
	{
		$this->sPath = $sPath ;
	}
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function initialize($sPath)
	{
		$this->sPath = $sPath ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function path()
	{
		return $this->sPath ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function name()
	{
		return basename($this->path()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function parentPath()
	{
		return dirname($this->path()).'/' ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function exists()
	{
		file_exists($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function lastModified()
	{
		filemtime($this->path()) ;
	}

	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canRead()
	{
		return is_readable($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canWrite()
	{
		return is_writeable($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canExecute()
	{
		return is_executable($this->path()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function perms()
	{
		return fileperms($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function setPerms($nMode)
	{
		return chmod($this->path(),$nMode) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function delete()
	{
		unlink($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sPath = "" ;
}
?>