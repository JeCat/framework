<?php
namespace jc\fs ;


class Dir extends FSO
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
} 



?>