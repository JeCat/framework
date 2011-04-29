<?php
namespace jc\util\match ;

use jc\util\HashTable;

class ResultSet extends HashTable
{
	public function add(Result $aResult)
	{
		parent::add($aResult) ;
	}

	public function result($nGrp=0) 
	{
		if( $aRes = $this->current() )
		{
			return $aRes->result($nGrp) ;
		}
		else
		{
			return null ;
		}
	}
	
	public function position($nGrp=0) 
	{
		if( $aRes = $this->current() )
		{
			return $aRes->position($nGrp) ;
		}
		else
		{
			return null ;
		}
	}
}

?>