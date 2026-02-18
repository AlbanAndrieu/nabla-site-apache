/**
 * Intercom and Axeptio widget loaders. Config is set here so pages need only one script tag.
 */
(() => {
	// Axeptio cookies – config and loader
	window.axeptioSettings = {
		clientId: "63ff48361876ce66c29dddcd",
		cookiesVersion: "nabla-en",
	};
	((d, s) => {
		var t = d.getElementsByTagName(s)[0],
			e = d.createElement(s);
		e.async = true;
		e.src = "//static.axept.io/sdk.js";
		t.parentNode.insertBefore(e, t);
	})(document, "script");

	// Intercom – config and loader
	window.intercomSettings = {
		api_base: "https://api-iam.intercom.io",
		app_id: "todo",
	};
	(() => {
		var w = window;
		var ic = w.Intercom;
		if (typeof ic === "function") {
			ic("reattach_activator");
			ic("update", w.intercomSettings);
		} else {
			var d = document;
			var i = () => {
				i.c(arguments);
			};
			i.q = [];
			i.c = (args) => {
				i.q.push(args);
			};
			w.Intercom = i;
			var l = () => {
				var s = d.createElement("script");
				s.type = "text/javascript";
				s.async = true;
				s.src = "https://widget.intercom.io/widget/todo";
				var x = d.getElementsByTagName("script")[0];
				x.parentNode.insertBefore(s, x);
			};
			if (document.readyState === "complete") {
				l();
			} else if (w.attachEvent) {
				w.attachEvent("onload", l);
			} else {
				w.addEventListener("load", l, false);
			}
		}
	})();
})();
