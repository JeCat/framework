<?php
namespace org\jecat\framework\pattern ;

interface ISingletonable
{
	static public function singleton ($bCreateNew=true,$createArgvs=null,$sClass=null) ;
}

?>