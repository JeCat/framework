<?php
namespace org\jecat\framework\fs\vfs ;

interface IPhysicalFileSystem
{
	/**
	 * @return resource
	 */
	public function & openFile($sPath,$sMode,$options,&$opened_path) ;
	/**
	 * @return void
	 */
	public function closeFile(&$resource) ;
	/**
	 * @return bool
	 */
	public function endOfFile(&$resource) ;
	/**
	 * @return bool
	 */
	public function lockFile(&$resource) ;
	/**
	 * @return bool
	 */
	public function flushFile(&$resource) ;
	/**
	 * @return string
	 */
	public function readFile(&$resource,$nLength) ;
	/**
	 * @return bool
	 */
	public function seekFile(&$resource,$offset,$whence=SEEK_SET) ;
	/**
	 * @return bool
	 */
	public function tellFile(&$resource) ;
	/**
	 * @return int
	 */
	public function writeFile(&$resource,$data) ;
	/**
	 * @return bool
	 */
	public function unlinkFile($sPath) ;
	/**
	 * @return array
	 */
	public function stat($sPath,$flags) ;
	
	
	/**
	 * @return resource
	 */
	public function opendir($sPath,$options) ;

	/**
	 * @return string
	 */
	public function readdir(&$resource) ;
	/**
	 * @return bool
	 */
	public function closedir(&$resource) ;
	/**
	 * @return bool
	 */
	public function rewinddir(&$resource) ;
	/**
	 * @return bool
	 */
	public function mkdir($sPath,$nMode,$options) ;
	/**
	 * @return bool
	 */
	public function rename($sFrom,$sTo) ;
	/**
	 * @return bool
	 */
	public function rmdir($sPath,$options) ;

	public function url($sPath) ;
	
}


