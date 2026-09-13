/**
 * Nested Builder — "Pane" titled container. Each top-level Pane becomes one
 * tab / accordion row / slide in the consuming element.
 */
fwEvents.on('fw-builder:' + 'nested-builder' + ':register-items', function (builder) {
	var currentItemType = 'pane';
	var reg = (window.fwNestedBuilderItems || {})[currentItemType] ||
		{ l10n: {}, options: [], defaults: { type: currentItemType, options: {} } };

	var ItemView = builder.classes.ItemView.extend({
		template: fw.template(
			'<div class="nb-item nb-item-pane">' +
				'<div class="nb-item-bar nb-item-bar--pane">' +
					'<span class="nb-item-name"><span class="dashicons dashicons-feedback"></span> <%- title %></span>' +
					'<span class="nb-item-actions">' +
						'<a class="nb-edit dashicons dashicons-admin-generic" href="#" onclick="return false;" data-hover-tip="<%- edit %>"></a>' +
						'<a class="nb-remove dashicons dashicons-no" href="#" onclick="return false;" data-hover-tip="<%- remove %>"></a>' +
					'</span>' +
				'</div>' +
				'<div class="nb-box-zone nb-pane-zone"><div class="builder-items"></div></div>' +
			'</div>'
		),
		events: {
			'click .nb-edit': 'openEdit',
			'click .nb-remove': 'removeItem'
		},
		initialize: function () {
			this.defaultInitialize();
			this.modal = new fw.OptionsModal({
				title: reg.l10n.item_title || 'Pane',
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
				title:  fw.opg('title', o) || reg.l10n.item_title || 'Pane',
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
		// A Pane accepts any content (Boxes / Text), but not another Pane.
		allowIncomingType: function (type) { return type !== 'pane'; },
		// A Pane may only live at the ROOT (it represents a tab / row) — never nested.
		allowDestinationType: function (type) { return type === null || typeof type === 'undefined'; }
	});

	builder.registerItemClass(Item);
});
