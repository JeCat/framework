<?php
namespace org\jecat\framework\util;

/**
 * 版本兼容类
 *
 * @author		alee
 * @access		public
 */
class VersionCompat
{
	/**
	 * 增加兼容版本
	 *
	 * @access	public
	 * @param	$aVersion	Version	版本
	 * @return	void
	 */
	public function addCompatibleVersion(Version $aVersion)
	{
		$this->arrScopes[] = new VersionScope($aVersion) ;
	}
	
	/**
	 * 增加兼容版本
	 *
	 * @access	public
	 * @param	$aVersionScope	VersionScope	版本范围
	 * @return	void
	 */
	public function addCompatibleVersionScope(VersionScope $aVersionScope)
	{
		$this->arrScopes[] = $aVersionScope ;
	}
	
	/**
	 * 增加兼容版本
	 * 
	 * @access	public
	 * @param	$sCompatibleVersions		string
	 * @return	void
	 */
	public function addFromString($sCompatibleVersions)
	{
		$arrScopes = preg_split('/[;\r\n]/',$sCompatibleVersions,-1,PREG_SPLIT_NO_EMPTY) ;
		foreach($arrScopes as $sScope)
		{
			$this->arrScopes[] = VersionScope::fromString($sScope) ;
		}
	}
	
	/**
	 * Description
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		$arrScopes = array() ;
		foreach ($this->arrScopes as $aScope)
		{
			$arrScopes[] = $aScope->__toString() ;
		}
		
		return implode("\r\n",$arrScopes) ;
	}
	
	/**
	 * 清空兼容版本
	 * 
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->arrScopes[] = array() ;
	}
	
	
	/**
	 * 检查一个版本是否兼容
	 * 
	 * @access	public
	 * @param	$aVersion		JCAT_Version
	 * @return	bool
	 */
	public function check($aVersion)
	{
		if($aVersion instanceof Version){
			foreach ($this->arrScopes as $aScope)
			{
				if( $aScope->isInScope($aVersion) )
				{
					return true ;
				}
			}
		}else if($aVersion instanceof VersionScope){
			foreach ($this->arrScopes as $aScope)
			{
				if( VersionScope::SEPARATE !== VersionScope::compareScope($aScope,$aVersion) )
				{
					return true ;
				}
			}
			return false;
		}
		return false ;
	}
	
	
	/**
	 * Description
	 * 
	 * @access	private
	 * @var		array
	 */
	private $arrScopes ;
}
