<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\lang\Object;

abstract class PurviewManager extends Object
{
	const user = 'user' ;
	const group = 'group' ;
	
	const ignore = '*ignore*' ;
	
	abstract public function addUserPurview($uid,$sNamespace,$sPurviewName,$target=null,$nBit=1) ;
	
	abstract public function removeUserPurview($uid,$sNamespace,$sPurviewName,$target=null,$nBit=1) ;
	
	abstract public function hasPurview($id,$sNamespace,$sPurviewName,$target=null,$nBit=1,$bGroup=false) ;
	
	abstract public function userPurviews($uid,$sNamespace=self::ignore,$sPurviewName=self::ignore,$target=self::ignore) ;
}

?>