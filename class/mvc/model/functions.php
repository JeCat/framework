<?php 
namespace org\jecat\framework\mvc\model ;

/**
 * @return org\jecat\framework\mvc\model\Model
 */
function M($sTable,$sPrototypeName=null,$primaryKeys=null,$columns=null)
{
	return new Model($sTable,$sPrototypeName,$primaryKeys,$columns) ;
}

