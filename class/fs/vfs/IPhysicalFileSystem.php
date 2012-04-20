<?php
namespace org\jecat\framework\fs\vfs ;

interface IPhysicalFileSystem
{
	/**
	 * @return resource
	 */
	public function opendir($sPath,$options) ;

	/**
	 * @return string
	 */
	public function readdir($hResource) ;
}

