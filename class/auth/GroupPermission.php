<?php
namespace org\jecat\framework\auth ;

class GroupPermission extends PermissionBase
{
	public function check(IdManager $aIdManager) 
	{
		$bAllow = false ;
		foreach($this->iterator() as $aPermission)
		{
			// bingo !
			if( $aPermission->check($aIdManager) ) 
			{
				$bAllow = true ;
			}
			
			// deny
			else
			{
				if( $aPermission->isNecessary() )
				{
					return false ;
				}
			}
		}

		return $bAllow ;
	}
	
	public function add(IPermission $aPermission,$bRestrict=false)
	{
		if( !$this->arrPermissions or !in_array($aPermission,$this->arrPermissions,$bRestrict) )
		{
			$this->arrPermissions[] = $aPermission ;
		}
		return $this ;
	}
	
	public function remove(IPermission $aPermission,$bRestrict=false)
	{
		if($this->arrPermissions)
		{
			$pos = array_search($aPermission,$this->arrPermissions,$bRestrict) ;
			if($pos!==false)
			{
				unset($this->arrPermissions[$pos]) ;
			}
		}
		return $this ;
	}
	
	public function clear()
	{
		$this->arrPermissions = null ;
		return $this ;
	}
	
	public function has(IPermission $aPermission,$bRestrict=false)
	{
		return $this->arrPermissions and array_search($aPermission,$this->arrPermissions,$bRestrict)!==false ;
	}
	
	public function iterator()
	{
		return $this->arrPermissions ?
					new \ArrayIterator($this->arrPermissions):
					new \EmptyIterator() ;
	}

	private $arrPermissions ;
}

?>