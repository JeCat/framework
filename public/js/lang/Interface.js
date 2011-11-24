jc.lang.Interface = {
	
	define: function (sName,mapMethods,arrExtends){

		var infNew = new jc.lang.Interface() ; 
		infNew.__sName = sName ;
		infNew.__arrExtends = arrExtends ;
		infNew.__mapMethods = mapMethods ;
		
		return infNew ;
	}
	
	, __sName : null
	, __arrExtends : [] 
	, __mapMethods : []

	, isInstance: function (objce)
	{
		
	}

	, beImplemented: function (clsClass)
	{
		if( typeof(clsClass)=="undefined" || typeof(clsClass)!="function" )
		{
			throw new TypeError("clsClass must be function type") ;
		}
		
		
	}
}