/**
 * Nested Builder — "Box" container item (nestable flex/grid). The canvas view
 * template holds a `.builder-items` element which the base builder replaces with
 * this item's child ItemsView, so dropped items nest into its `_items` collection.
 */
fwEvents.on('fw-builder:' + 'nested-builder' + ':register-items', function (builder) {
	var currentItemType = 'box';
	var reg = (window.fwNestedBuilderItems || {})[currentItemType] ||
		{ l10n: {}, options: [], defaults: { type: currentItemType, options: {} } };

	var ItemView = builder.classes.ItemView.extend({
		template: fw.template(
			'<div class="nb-item nb-item-box nb-layout-<%- layout %>">' +
				'<div class="nb-item-bar">' +
					'<span class="nb-item-name"><span class="dashicons dashicons-screenoptions"></span> ' + '<%- title %> · <%- layout %></span>' +
					'<span class="nb-item-actions">' +
						'<a class="nb-edit dashicons dashicons-admin-generic" href="#" onclick="return false;" data-hover-tip="<%- edit %>"></a>' +
						'<a class="nb-remove dashicons dashicons-no" href="#" onclick="return false;" data-hover-tip="<%- remove %>"></a>' +
					'</span>' +
				'</div>' +
				'<div class="nb-box-zone"><div class="builder-items"></div></div>' +
			'</div>'
		),
		events: {
			'click .nb-edit': 'openEdit',
			'click .nb-remove': 'removeItem'
		},
		initialize: function () {
			this.defaultInitialize();
			this.modal = new fw.OptionsModal({
				title: reg.l10n.item_title || 'Box',
				options: this.model.modalOptions,
				values: this.model.get('options'),
				size: 'small'
			});
			this.listenTo(this.modal, 'change:values', function (modal, values) {
				this.model.set('options', values);
			});
			this.model.on('change:options', function () {
				this.modal.set('values', this.model.get('options'));
			}, this);
		},
		render: function () {
			var o = this.model.get('options') || {};
			this.defaultRender({
				title:  reg.l10n.item_title || 'Box',
				layout: fw.opg('layout', o) || 'row',
				edit:   reg.l10n.edit || 'Edit',
				remove: reg.l10n.remove || 'Remove'
			});
		},
		openEdit: function () { this.modal.open(); },
		removeItem: function () { this.remove(); this.model.collection.remove(this.model); }
	});

	var Item = builder.classes.Item.extend({
		defaults: function () { return fw.clone(reg.defaults); },
		initialize: function () {
			this.defaultInitialize();
			this.modalOptions = reg.options;
			this.view = new ItemView({ id: 'fw-builder-item-' + this.cid, model: this });
		},
		// A Box is a container: it accepts nested Boxes and content leaves (not Panes).
		allowIncomingType: function (type) { return type === 'box' || type === 'text'; }
	});

	builder.registerItemClass(Item);
});
