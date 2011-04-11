<?php

namespace jc\locale ;

interface ITranslator
{
	public function findSentence($sKey) ;
	
	public function trans($sOri,array $arrArgvs=array(),$sSavePackageName=null) ;
}

?>