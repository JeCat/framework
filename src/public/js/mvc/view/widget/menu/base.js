jc={};
jc.mvc={};
jc.mvc.view={};
jc.mvc.view.widget={};
jc.mvc.view.widget.menu={};
jc.mvc.view.widget.menu.base={};
jc.mvc.view.widget.menu.base.getStyle=function(o,styleName){
	var el = o;
	if (el.currentStyle){
		var y = el.currentStyle[styleName];
	}else if (window.getComputedStyle){
		var y = document.defaultView.getComputedStyle(el,null).getPropertyValue(styleName);  
	}
	return y;
}
jc.mvc.view.widget.menu.base.arrActive=new Array();
jc.mvc.view.widget.menu.base.show=function(o){
	o.parentNode.style.position="relative";
	o.style.position="absolute";
	o.style.left = this.getStyle(o.parentNode,'width');
	o.style.top = 0;
	o.style.display="block";
}
jc.mvc.view.widget.menu.base.hide=function(o){
	o.style.display="none";
}
jc.mvc.view.widget.menu.base.isShow=function(o){
	return this.getStyle(o,'display')== "block";
}
jc.mvc.view.widget.menu.base.isAlone=function(o){
	return ( o.className && ( o.className.match('jc-mvc-view-widget-menu-menu-alone') == 'jc-mvc-view-widget-menu-menu-alone'));
}
jc.mvc.view.widget.menu.base.expand=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isAlone(childlist[i])){
			o = childlist[i];
			this.show(o);
			this.arrActive.push(o.id);
		}
	}
}
jc.mvc.view.widget.menu.base.contract=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isAlone(childlist[i])){
			var o = childlist[i];
			this.hide(o);
		}
	}
}
jc.mvc.view.widget.menu.base.isOrParentOf=function(a , b){// a is b or a is parent of b
	while(b && typeof(b)!='undefined'){
		if(a == b) return true;
		b = b.parentNode;
	}
	return false;
}
jc.mvc.view.widget.menu.base.hideActive=function(item){//隐藏所有和item无关的menu
	for(var i=0;i<this.arrActive.length;++i){
		a=this.arrActive[i];
		if(! this.isOrParentOf(document.getElementById(a) ,item) ){
			this.hide(document.getElementById(a));
			this.arrActive.splice(i,1);
		}
	}
}
jc.mvc.view.widget.menu.base.item_onActive=function(item){
	clearTimeout(this.t);
	this.t=-1;
	this.hideActive(item);
	this.expand(item);
	this.stopBubble(event);
}
jc.mvc.view.widget.menu.base.t=-1;
jc.mvc.view.widget.menu.base.timeout=function(){
	this.hideActive(null);
}
jc.mvc.view.widget.menu.base.item_onDisactive=function(item){
	if(this.t == -1){
		this.t=setTimeout("jc.mvc.view.widget.menu.base.timeout()",500);
	}
}
jc.mvc.view.widget.menu.base.stopBubble=function(e) {  
    var e = e ? e : window.event;
    if (window.event) { // IE
        e.cancelBubble = true;
    } else { // FF
        e.stopPropagation();
    }
}
