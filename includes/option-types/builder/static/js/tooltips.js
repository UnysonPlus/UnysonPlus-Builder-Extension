/**
 * Create tooltips for elements with data-hover-tip="Tip Text" attribute
 */
window.fwExtBuilderRootItemsTips = (function(rootItems){
	var $ = jQuery,
		/**
		 * Store all created tooltip instance APIs
		 */
		tipsAPIs = [],
		destroyTips = function(){
			_.each(tipsAPIs, function(api) { api.destroy(true); });

			tipsAPIs = [];
		},
		makeTip = function($el){
			if ($el.attr('data-fw-tooltip')) {
				return;
			}

			$el.fwTooltip({
				position: {
					at: 'top center',
					my: 'bottom center',
					viewport: rootItems.view.$el.parent()
				},
				style: {
					classes: 'fw-tooltip-default fw-tooltip-builder',
					tip: {
						width: 12,
						height: 5
					}
				},
				content: {
					text: $el.attr('data-hover-tip')
				}
			});

			tipsAPIs.push($el.fwTooltip('api'));

			$el.fwTooltip('api').show();
		};

	rootItems.view.$el.on('mouseenter', '[data-hover-tip]', function(){
		makeTip($(this));
	});

	rootItems.on('builder:change', function(){
		destroyTips();
	});
});
