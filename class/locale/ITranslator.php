<?php

namespace org\jecat\framework\locale ;

interface ITranslator
{
	public function findSentence($sKey) ;
	
	public function trans($sOri,$argvs=null,$sSavePackageName=null) ;
}

?>