var TimerObject=function(t) {
	this.bRun=false;
	this.bStop=true;
	this.bFirstRun=false;
	this.timeout=100;
	this.events=[];
	if(t>0) {
		this.timeout=t;
	}
};

TimerObject.prototype.run=function() {
	this.bRun=true;
	if(!this.bFirstRun || this.bStop) {
		this.bFirstRun=true;
		this.tick();
	}
	return this;
};
TimerObject.prototype.stop=function() {
	this.bRun=false;
	this.bStop=false;
	return this;
};
TimerObject.prototype.append=function(handler) {
	this.events.push(handler);
	return this;
};
TimerObject.prototype.empty=function() {
	this.events=[];
	return this;
};
TimerObject.prototype.tick=function() {
	var self=this;
	for(var i=0;i<this.events.length;i++) {
		this.events[i]();
	}
	if(this.bRun) {
		setTimeout(function(){self.tick();},this.timeout);
	} else {
		this.bStop=true;
	}
};