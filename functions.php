<?php 

namespace org\jecat\framework\mvc ;

/**
 * @return org\jecat\framework\mvc\model\Model
 */
function M($sTable,$sPrototypeName=null,$primaryKeys=null,$columns=null)
{
	return new model\Model($sTable,$sPrototypeName,$primaryKeys,$columns) ;
}

