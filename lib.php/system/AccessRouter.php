<?php
namespace jc\system ;

class AccessRouter
{
    public function setControllerParam($sParamName)
    {
    	$this->sControllerParam = (string)$sParamName ;
    }
    public function controllerParam()
    {
    	return $this->sControllerParam ;
    }
    
    public function controller(Request $aRequest)
    {
    	$sControllerName = $aRequest->string($this->sControllerParam) ;
    }
    
	private $sControllerParam = 'c' ;
}

?>