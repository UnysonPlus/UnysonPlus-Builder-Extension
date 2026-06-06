window.fwExtBuilderInitialize = (function ($) {
	var fixedHeaderHelpers = {
		increment: 0,
		$adminBar: $('#wpadminbar'),
		getAdminBarHeight: function() {
			var height = 0;

			if (this.$adminBar.length && this.$adminBar.css('position') === 'fixed') {
				height = this.$adminBar.height();
			}

			var gutenbergContainer = $( '#editor.block-editor__container' );

			if ( gutenbergContainer.length > 0 ) {
				height += gutenbergContainer.find( '.edit-post-header' ).outerHeight() + gutenbergContainer.find( '.components-notice-list' ).height();
			}

			return height;
		},
		fix: function($header, $builder, $scrollParent){
			var topSpace = this.getAdminBarHeight(),
				scrollParentHeight = $scrollParent.height(),
				scrollParentScrollTop = $( document ).scrollTop(),
				scrollParentOffset = $( document ).offset(),
				builderHeight = $builder.get(0).clientHeight,
				builderOffsetTop = $builder.offset().top,
				headerHeight = $header.get(0).clientHeight;

			/**
			 * Fixes inside options modal
			 */
			if (scrollParentOffset) {
				builderOffsetTop -= scrollParentOffset.top;

				if (builderOffsetTop <= 0) {
					builderOffsetTop = topSpace;
				}
			}

			do {
				if (builderOffsetTop >= scrollParentScrollTop + topSpace) {
					// scroll top didn't reached the builder
					break;
				}

				if (builderHeight < scrollParentHeight - topSpace) {
					// the builder fits inside the scroll element
					break;
				}

				var bottomLimit = Math.floor(scrollParentHeight / 2);
				if (bottomLimit < headerHeight) {
					bottomLimit = headerHeight;
				}
				if (bottomLimit < 256) {
					bottomLimit = 256;
				}

				if (builderHeight < headerHeight + bottomLimit) {
					break;
				}

				if (scrollParentHeight < headerHeight + bottomLimit) {
					// the scroll element must have space to display header and bottomLimit
					break;
				}

				var headerTopShift = (scrollParentScrollTop + topSpace) - builderOffsetTop;

				if (headerTopShift + headerHeight + bottomLimit > builderHeight) {
					// do not allow header to cover last items
					headerTopShift -= headerTopShift + headerHeight + bottomLimit - builderHeight;
				}

				// set fixed header
				{
					if (!$builder.hasClass('fixed-header')) {
						$builder.addClass('fixed-header');
						if ( window._fwDragDebug ) { console.log( '[fwDragDebug] .fixed-header ADDED  pad-top=' + headerHeight + 'px  headerTop=' + headerTopShift + 'px' ); }
					}

					$builder.css({
						'padding-top': headerHeight +'px' // set ghost space in builder, like the header is still there
					});
					$header.css({
						'top': headerTopShift +'px'
					});

					return true;
				}
			} while(false);

			// remove fixed header
			{
				if ($builder.hasClass('fixed-header')) {
					$builder.removeClass('fixed-header');
					if ( window._fwDragDebug ) { console.log( '[fwDragDebug] .fixed-header REMOVED' ); }

					$builder.css({
						'padding-top': ''
					});
					$header.css({
						'top': ''
					});
				}

				return false;
			}
		}
	};

	return {
		init: init
	};

	/**
	 * Loop recursive through all items in given collection
	 */
	function forEachItemRecursive(collection, callback) {
		collection.each(function(item){
			callback(item);

			forEachItemRecursive(item.get('_items'), callback);
		});
	}

	function initDraggable ($this, builder, id) {
		fwEvents.trigger('fw:options:init:tabs', {$elements: $this.find('> .builder-items-types')});

		var additionalSortableOptions = {}

		fwEvents.trigger(
			'fw-builder:'+ builder.get('type') +':toolbar-sortable-additional-options',
			additionalSortableOptions
		)

		$this.find('> .builder-items-types .builder-item-type').draggable(_.extend({
			connectToSortable: '#'+ id +' .builder-root-items .builder-items',
			helper: 'clone',
			distance: 10,
			placeholder: 'fw-builder-placeholder',
			zIndex: 99999,
			// Parent the cloned helper to the common ancestor of both the
			// thumbnails area and the canvas. With the default `appendTo`
			// the helper sits inside the source thumbnail container, and
			// crossing into the canvas (or back out over the tabs/thumbnails
			// area) re-parents it into / out of the connected sortable, which
			// changes the offset parent and produces a visible "jump". A
			// common ancestor keeps the offset parent stable for the whole
			// drag. `.fw-option-type-builder` is the option-type wrapper that
			// contains both regions and carries the CSS class chain the
			// helper needs (so styles still match).
			appendTo: '.fw-option-type-builder',
			drag: function ( event, ui ) {
				if ( window.fwDragDebug ) { window.fwDragDebug.tick( 'd.drag', event, ui ); }
			},
			start: function(event, ui) {
				// Pin the helper's offsetParent for the entire drag by
				// tagging the body with `fw-builder-dragging`. The matching
				// rule in `builder.css` forces `.fw-option-type-builder` to
				// `position: relative` AND every `.builder-item` /
				// `.builder-items` / `.builder-root-items` descendant to
				// `position: static !important`. The diagnostic overlay
				// confirmed the drift root cause was the helper's offsetParent
				// flipping from `.fw-option-type-builder` to a column
				// `.builder-item.fw-col-xs-12` when jQuery UI's
				// `connectToSortable` re-parented the cloned helper into a
				// column's `.builder-items`. Forcing every potential
				// positioned ancestor inside the wrapper to static means the
				// offsetParent walk always lands on the wrapper, regardless
				// of which sortable jQuery UI re-parents into — so the inline
				// `top` / `left` jQuery UI sets each tick are evaluated
				// against a consistent coordinate system and the helper stays
				// glued to the cursor.
				$( 'body' ).addClass( 'fw-builder-dragging' );

				if ( window.fwDragDebug ) { window.fwDragDebug.bump( 'd.start' ); }

				var movedType = ui.helper.attr('data-builder-item-type');

				if (!movedType) {
					return;
				}

				var MovedTypeClass = builder.getRegisteredItemClassByType(movedType);

				if (!MovedTypeClass) {
					return;
				}

				/**
				 * add "allowed" classes to items vies where allowIncomingType(movedType) returned true
				 * else add "denied" class
				 */
				{
					{
						if (MovedTypeClass.prototype.allowDestinationType(null)) {
							builder.rootItems.view.$el.addClass('fw-builder-item-allow-incoming-type');
						} else {
							builder.rootItems.view.$el.addClass('fw-builder-item-deny-incoming-type');
						}
					}

					forEachItemRecursive(builder.rootItems, function(item){
						if (
							item.allowIncomingType(movedType)
							&&
							MovedTypeClass.prototype.allowDestinationType(item.get('type'))
						) {
							item.view.$el.addClass('fw-builder-item-allow-incoming-type');
						} else {
							item.view.$el.addClass('fw-builder-item-deny-incoming-type');
						}
					});
				}
			},
			stop: function() {
				// Release the offsetParent pin (see `start`).
				$( 'body' ).removeClass( 'fw-builder-dragging' );

				// remove "allowed" and "denied" classes from all items
				{
					builder.rootItems.view.$el.removeClass(
						'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
					);

					forEachItemRecursive(builder.rootItems, function(item){
						item.view.$el.removeClass(
							'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
						);
					});
				}

				if ( window.fwDragDebug ) { window.fwDragDebug.bump( 'd.stop' ); }
			}
		}, additionalSortableOptions));

		/**
		 * Add item on thumbnail click
		 */
		$this.find('.builder-items-types').on('click', '.builder-item-type', function(){
			var $itemType = $(this);

			var itemType = $itemType.attr('data-builder-item-type');

			if (itemType) {
				var ItemTypeClass = builder.getRegisteredItemClassByType(itemType);

				if (ItemTypeClass) {
					if (ItemTypeClass.prototype.allowDestinationType(null)) {
						builder.rootItems.add(
							new ItemTypeClass({}, {
								$thumb: $itemType
							})
						);

						// animation
						{
							// stop previous animation
							{
								clearTimeout($itemType.attr('data-animation-timeout-id'));
								$itemType.removeClass('fw-builder-animation-item-type-add');
							}

							$itemType.addClass('fw-builder-animation-item-type-add');

							$itemType.attr('data-animation-timeout-id',
								setTimeout(function(){
									$itemType.removeClass('fw-builder-animation-item-type-add');
								}, 500)
							);
						}
					} else {
						console.warn('Item type "'+ itemType +'" is not allowed as first level item');
					}
				} else {
					console.error('Unregistered item type: '+ itemType);
				}
			} else {
				console.error('Cannot extract item type from element', $itemType);
			}
		});

		// scroll to the added element
		builder.rootItems.on('add', function(el){
			var $el = el.view.$el;

			clearTimeout($this.attr('data-scroll-bottom-timeout'));

			var timeout = setTimeout(function(){
				if (!$el.length) {
					return; // not in DOM already
				}

				var $builderOption = $this,
					$scrollParent = $builderOption.scrollParent();

				if ($scrollParent.get(0) === document || $scrollParent.get(0) === document.body) {
					$scrollParent = $(window);
				}

				if ($builderOption.height() <= $scrollParent.height() + 300) {
					/**
					 * Do not scroll if the builder can fit or is almost entirely visible
					 * To prevent "jumping" https://github.com/ThemeFuse/Unyson/issues/815
					 */
					return;
				}

				var scrollParentHeight = $scrollParent.height()

				$scrollParent.scrollTop(Math.min( // use min() to not allow scroll to far down hiding the fixed header
					$el.offset().top - scrollParentHeight / 2,
					$builderOption.offset().top + $builderOption.outerHeight() - scrollParentHeight
				));
			}, 100);

			$this.attr('data-scroll-bottom-timeout', timeout);
		});
	}

	function init (Builder) {
		fwEvents.on('fw:options:init', function (data) {
			var $options = data.$elements.find('.fw-option-type-builder:not(.initialized)');

			if (! $options.length) {
				return;
			}

			initBuilderDelayed(Builder, data);
		});
	}

	function initBuilderDelayed (Builder, data) {
		var $options = data.$elements.find('.fw-option-type-builder:not(.initialized)');

		$options.closest('.fw-backend-option').addClass('fw-backend-option-type-builder');

		var triggerInit = _.once(function () {
			fwEvents.trigger('fw:option-type:builder:init', {
				$elements: $options
			});
		});

		$options.each(function () {
			var $el = $(this);
			var type = $el.attr('data-builder-option-type');

			var promises = [];

			fwEvents.trigger(
				'fw-builder:'+ type + ':collect-async-init-promises',
				{
					promises: promises
				}
			);

			jQuery.when.apply(jQuery, promises).then(function () {
				initSingleBuilder($el);
				triggerInit();
			});

		});

		function initSingleBuilder ($el) {
			var $this          = $el,
				hasDragAndDrop = $this.attr('data-drag-and-drop'),
				id             = $this.attr('id'),
				type           = $this.attr('data-builder-option-type');

			/**
			 * Create instance of Builder
			 */
			{
				var data = {
					type:         type,
					$option:      $this,
					$input:       $this.find('> [data-fw-option-type="hidden"]:first > input'),
					$types:       $this.find('.builder-items-types:first'),
					$rootItems:   $this.find('.builder-root-items:first'),
					$headerTools: $('<div class="fw-builder-header-tools fw-clearfix fw-hidden"></div>')
				};

				var eventData = $.extend({}, data, {
					/**
					 * In event you can extend (customize/change) and replace this (property) class
					 */
					Builder: Builder
				});

				fwEvents.trigger('fw-builder:'+ type +':before-create', eventData);

				$this.find('> .builder-items-types').append(data.$headerTools);

				var builder = new eventData.Builder(
					{
						type: data.type
					},
					{
						$input: data.$input
					}
				);

				builder.rootItems.view.$el.appendTo(data.$rootItems);

				new fwExtBuilderRootItemsTips(builder.rootItems);
			}

			/**
			 * Init draggable thumbnails just if user wants it to be around
			 */
			if (hasDragAndDrop) {
				initDraggable($this, builder, id);
			}

			/**
			 * Add tips to thumbnails
			 */
			$this.find('.builder-items-types .builder-item-type [data-hover-tip]').each(function(){
				$(this).qtip({
					position: {
						at: 'top center',
						my: 'bottom center',
						viewport: $('body')
					},
					style: {
						classes: 'qtip-fw qtip-fw-builder',
						tip: {
							width: 12,
							height: 5
						}
					},
					content: {
						text: $(this).attr('data-hover-tip')
					}
				});
			});

			/**
			 * Make header follow you when you scroll down
			 */
			if ($this.attr('data-fixed-header')) {
				var fixedHeaderEventsNamespace = '.fw-builder-fixed-header-'+ (++fixedHeaderHelpers.increment),
					$fixedHeader = $this.find('> .builder-items-types:first'),
					/**
					 * In OptionsModal we must track the modal scroll not the window scroll
					 */
					$scrollParent;

				$scrollParent = $this.scrollParent();
				if ($scrollParent.get(0) === document || $scrollParent.get(0) === document.body) {
					$scrollParent = $(window);
				}

				/**
				 * Options modal fixed tabs are initialized after options init
				 */
				setTimeout(function(){
					$scrollParent = $this.scrollParent();
					if ($scrollParent.get(0) === document || $scrollParent.get(0) === document.body) {
						$scrollParent = $(window);
					}

					$scrollParent
						.on('scroll'+ fixedHeaderEventsNamespace, function(){
							fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);
						})
						.on('resize'+ fixedHeaderEventsNamespace, function(){
							fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);
						});

					fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);
				}, 0);

				/**
				 * On thumbnails tab change, the new tab may contain more thumbnails that previous
				 * thus having different height
				 */
				$fixedHeader.on('click'+ fixedHeaderEventsNamespace, '.fw-options-tabs-list a, .fullscreen-btn', function(){
					fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);

					/**
					 * When you scroll down to the last items (to the limit when the fixed header stops and begins to go under page)
					 * and you switch to a tab with a bigger height, there are some issues with positioning.
					 * Calling this send time fixes it
					 */
					fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);
				});

				/**
				 * Listen builder value/items change
				 * For e.g. when you delete an element from the builder (or press undo/redo buttons)
				 * its height is changed and the fixed header needs repositioning
				 */
				builder.$input.on('fw-builder:input:change'+ fixedHeaderEventsNamespace, function(){
					fixedHeaderHelpers.fix($fixedHeader, $this, $scrollParent);
				});

				/**
				 * Remove events from external elements
				 * In case the builder is created and remove dynamically multiple times, for e.g. inside fw.OptionsModal
				 */
				$this.on('remove', function(){
					$scrollParent.off(fixedHeaderEventsNamespace);
				});
			}

			if ($this.attr('data-compression') && window.JSZip) {
				var compress = {
					eventsNamespace: '.fw-builder-compress',
					isBusy: false,
					listenFormSubmit: function () {
						$this.closest('form').on('submit'+  compress.eventsNamespace, function(e){
							var $form = $(this),
								$submitButton = $form.find('input[type="submit"]:focus');

							if (!$submitButton.length && $form.find(':focus').is('#post-preview')) {
								// Do nothing on "Preview Changes" button press
								return;
							}

							var builderValue = builder.$input.val();
							if (builderValue.length < 200000) {
								// compress only when builder has a lot of elements
								return;
							}

							e.preventDefault();

							if (compress.isBusy) {
								return console.log('Zipping...');
							} else {
								fw.loading.show();
								compress.isBusy = true;
							}

							var zip = new JSZip();

							zip.file('builder.json', builderValue);
							zip.generateAsync({type: 'base64', compression: 'DEFLATE'}).then(function(content) {
								builder.$input.val(content);

								fw.loading.hide();
								$form.off('submit'+ compress.eventsNamespace);

								$submitButton.length
									? $submitButton.focus().trigger('click')
									: $form.submit();
							});
						});
					}
				};

				$this.one('fw:option-type:builder:init', function () {
					compress.listenFormSubmit();
				});
			}

			$this.on('fw:option-type:builder:dump-json', function(e, data){
				if (typeof data != 'undefined' && typeof data.cid != 'undefined') {
					console.log('[Builder JSON Dump] Item '+ data.cid +'\n\n'+
						JSON.stringify(builder.findItemRecursive({cid: data.cid}))
					);
				} else {
					console.log('[Builder JSON Dump] Full\n\n'+
						JSON.stringify(builder.rootItems)
					);
				}
			});

			$this.trigger('fw:option-type:builder:init', $.extend({}, eventData, {
				builder: builder
			}));
		}

		// add special class for builders that has header tools div
		$options.find('> .builder-items-types .fw-builder-header-tools:not(.fw-hidden)')
			.closest('.fw-option-type-builder').addClass('has-header-tools')
			// Add "Save" post button if the builder is within edit post form
			.each(function(){
				var $postForm = $(this).closest('form#post');

				if (!$postForm.length) {
					return;
				}

				var isLocalStorageAvailable = function(){
						var test = 'test';
						try {
							localStorage.setItem(test, test);
							localStorage.removeItem(test);
							return true;
						} catch(e) {
							return false;
						}
					},
					localStorageKey = 'fw-ext-builder-save-scroll-position',
					$savePostButton = $postForm.find('#publishing-action #publish'),
					$savePostBuilderButton = $(
						'<button'+
							' type="button"'+
							' class="button button-primary fw-pull-right fw-builder-header-post-save-button"'+
							' onclick="return false;">' +
						'</button>')
						.text($savePostButton.attr('value'))
						.on('click', function(){
							$(this).attr('disabled', 'disabled').off('click');

							if (isLocalStorageAvailable()) {
								localStorage.setItem(localStorageKey, $(window).scrollTop());
							}

							$savePostButton.trigger('click');
						});

				$(this).find('.fw-builder-header-tools:first')
					.prepend('<span class="pull-right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>')
					.prepend($savePostBuilderButton);

				if (isLocalStorageAvailable()) {
					var scrollTopOnLastSave = localStorage.getItem(localStorageKey);

					if (scrollTopOnLastSave !== null) {
						localStorage.removeItem(localStorageKey);

						// http://stackoverflow.com/a/16475234/1794248
						$('html, body').animate({scrollTop: scrollTopOnLastSave}, '100', 'swing');
					}
				}
			});

		$options.addClass('initialized');
	}
})(jQuery);