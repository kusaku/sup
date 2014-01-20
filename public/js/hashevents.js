var HashEvents=function() {
	this.arHandlers={};
	this.arHash=[];
	this.parseHash();
	var self=this;
	if ("addEventListener" in window) {
		window.addEventListener("hashchange", function(){HashEvents.prototype.parseHash.call(self);}, false);
	}
	else {
		window.attachEvent("onhashchange", function(){HashEvents.prototype.parseHash.call(self);});
	}
};

HashEvents.prototype.arHandlers={};
HashEvents.prototype.arHash=[];
HashEvents.prototype.parseHash=function() {
	var hash = document.location.hash;
	this.arHash = hash.split('_');
	for(var ii in this.arHandlers) {
		if(this.arHandlers.hasOwnProperty(ii)) {
			this.arHandlers[ii](this.arHash);
		}
	}
};
HashEvents.prototype.addHandler=function(name,func) {
	if(!this.arHandlers.hasOwnProperty(name)) {
		func(this.arHash);
		this.arHandlers[name]=func;
	}
};