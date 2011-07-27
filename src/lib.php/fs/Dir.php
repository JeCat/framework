<?php
namespace jc\fs ;


class Dir extends FSObject
{
	static public function formatPath($sPath,$sPathSeparator=DIRECTORY_SEPARATOR)
	{
		$sNewPath = parent::formatPath($sPath,$sPathSeparator) ;
		
		// 文件分隔符结尾
		if( substr($sNewPath, -1,1)!=$sPathSeparator )
		{
			$sNewPath.= $sPathSeparator ;
		}
		
		return $sNewPath ;
	}
	
	static public function mkdir($sPath,$nMode=0644,$bRecursion=true)
	{
		if( mkdir($sPath,$nMode,$bRecursion)===false )
		{
			return false ;
		}
		
		else 
		{
			chmod($sPath,$nMode) ;
			return true ;
		}
	}
} 



?>