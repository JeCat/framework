<?php
namespace jc\fs\imp ;

use jc\fs\FSO;
use jc\lang\Object;

class MockFSO extends FSO{
	public function __construct(\jc\fs\FileSystem $aFileSystem, $sInnerPath=''){
		parent::__construct($aFileSystem,$sInnerPath);
	}
	public function canRead(){}
	public function canWrite(){}
	public function canExecute(){}
	function perms(){}
	public function setPerms($nMode){}
	public function copy($to){}
	public function move($to){}
	public function createTime(){}
	public function modifyTime(){}
	public function accessTime(){}
	public function isHidden(){}
	public function exists(){}
	public function create(){}
	public function url(){}
}
