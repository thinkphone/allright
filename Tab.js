/**
 * Tab.js new Tab({"hid":"cid",...}).on(); new
 * Tab("hid","cid",[onmouseover]).on(page);
 * 
 * @author GF<email@liuguangfeng.cn> http://gf-zj.com/Tab
 *
 */
(function(window) {
	function $(id) {
		return document.getElementById(id);
	}
	function addEvent(elm, evType, fn, useCapture) {
		if (elm.addEventListener) {
			elm.addEventListener(evType, fn, useCapture);
		} else if (elm.attachEvent) {
			elm.attachEvent('on' + evType, fn);
		} else {
			elm['on' + evType] = fn;
		}
	}
	;
	function log(msg) {
		if (typeof (console) !== "undefined") {
			console.log(msg);
		} else {
			//alert(msg);
		}
	}
	Tab.prototype = {
		on : function(page) {
			addEvent(window, 'load', (function(tab) {
				return function() {
					tab.init(page);
				};
			})(this), false);
			return this;
		},
		fire : function(to) {
			var current = this.getActiveTab();
			log("current" + current + "  to:" + to);
			if (current == to)
				return;
			this.cc[current].style.display = "none";
			this.hc[current].className = this.hc[current].className.replace(
					/\s*active/, "");

			this.cc[to].style.display = "block";
			this.hc[to].className += " active";
		},
		getActiveTab : function() {
			for ( var i = 0; i < this.cc.length; i++)
				if (this.cc[i].style.display != "none")
					return i;
		},
		init : function(page) {
			if (typeof (this.args[0]) == "object") {
				this.triggle = (this.args[1] || "onclick");
				this.hc = [];
				this.cc = [];
				for ( var i in this.args[0]) {
					this.hc.push($(i));
					this.cc.push($(this.args[0][i]));
				}

			} else if (this.args.length >= 2) {
				this.hc = $(this.args[0]).children;
				this.cc = $(this.args[1]).children;
				this.triggle = (this.args[2] || "onclick");
			}

			if (this.hc.length > this.cc.length) {
				log("this.hc.length>this.cc.length");
				return;
			}
			if(typeof(page)!=="undefined"&&page<this.hc.length){
				//set current page
				this.fire(page);
			}
			for ( var i = 0, len = this.hc.length; i < len; i++) {
				this.hc[i][this.triggle] = (function(tab, i) {
					return function() {
            tab.hc[i].blur();
						tab.fire(i);
					};
				})(this, i);
			}
		}
	};
	function Tab() {
		this.args = arguments;
	}
	window.Tab = Tab;
})(window);