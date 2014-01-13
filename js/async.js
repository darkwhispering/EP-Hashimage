jQuery(document).ready(function($) {
	$(".hashimage-container").each(function(i,e){
		// Hashimage continer
		var container = this;

		// Get options
		var options = $(this).data('async');

		// Get async status
		var async = options.async;

		// Get refresh status
		var refresh = ((parseInt(options.refresh, 10)+parseInt(10, 10))*parseInt(1000, 10));

		// Data url to plugin
		var dataurl = options.pluginpath+'?limit='+options.limit+'&hashtag='+options.hashtag+'&type='+options.type+'&async=true';

		// Path to links, needed for slimbox
		var link_path = '.hashimage-container li a';

		// Clean url path to the plugin for use with the loader
		var clean_pluginpath = options.pluginpath.replace('async.php', '');

		// Get image on initial load
		getImages(container, link_path, dataurl);

		// Refresh the feed automatically
		setInterval(function(){
			$(container).html('<div class="hashimage-loader"><img src="'+clean_pluginpath+'loading.gif" alt="loading..."/></div>');
			getImages(container, link_path, dataurl);
		}, refresh);

		// The ajax call
		function getImages(element, links, dataurl) {
			$(element).load(dataurl, function(){
				$(links).each(function() {
					if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
						jQuery(function($) {
							$("a[rel^='lightbox']").slimbox({/* Put custom options here */}, null, function(el) {
								return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
							});
						});
					}
				});
			});
		}
	});
});