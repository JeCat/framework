<?php
namespace jc\fs\imp ;

use jc\fs\FileSystem;

class MockFileSystem extends FileSystem{
	public function iterator($sPath){
		//echo "Mock:iterator $sPath\n";
	}
	protected function deleteFileOperation(&$sPath){
		//echo "Mock:deleteFileOperation $sPath\n";
	}
	protected function deleteDirOperation(&$sPath){
		//echo "Mock:deleteDireOperation $sPath\n";
	}
	protected function createFileObject(&$sPath){
		//echo "Mock:createFileObject $sPath\n";
		return new MockFSO($this,$sPath);
	}
	protected function createFolderObject(&$sPath){
		//echo "Mock:createFolderObject $sPath\n";
		return new MockFSO($this,$sPath);
	}
	protected function existsOperation(&$sPath){
		//echo "Mock:existsOperation $sPath\n";
		return true;
	}
	protected function isFileOperation(&$sPath){
		//echo "Mock:isFileOperation $sPath\n";
		return true;
	}
	protected function isFolderOperation(&$sPath){
		//echo "Mock:isFolderOperation $sPath\n";
		return false;
	}
}
