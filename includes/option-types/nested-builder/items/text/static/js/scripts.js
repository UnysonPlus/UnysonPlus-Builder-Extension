/**
 * Nested Builder — "Text" content leaf (no child drop-zone).
 */
fwEvents.on('fw-builder:' + 'nested-builder' + ':register-items', function (builder) {
	var currentItemType = 'text';
	var reg = (window.fwNestedBuilderItems || {})[currentItemType] ||
		{ l10n: {}, options: [], defaults: { type: currentItemType, options: {} } };

	var ItemView = builder.classes.ItemView.extend({
		template: fw.template(
			'<div class="nb-item nb-item-text">' +
				'<div class="nb-item-bar">' +
					'<span class="nb-item-name"><span class="dashicons dashicons-editor-paragraph"></span> <%- title %></span>' +
					'<span class="nb-item-actions">' +
						'<a class="nb-edit dashicons dashicons-admin-generic" href="#" onclick="return false;" data-hover-tip="<%- edit %>"></a>' +
						'<a class="nb-remove dashicons dashicons-no" href="#" onclick="return false;" data-hover-tip="<%- remove %>"></a>' +
					'</span>' +
				'</div>' +
				'<div class="nb-item-preview" style="text-align:<%- align %>"><%- preview %></div>' +
			'</div>'
		),
		events: {
			'click .nb-edit': 'openEdit',
			'click .nb-remove': 'removeItem'
		},
		initialize: function () {
			this.defaultInitialize();
			this.modal = new fw.OptionsModal({
				title: reg.l10n.item_title || 'Text',
				options: this.model.modalOptions,
				values: this.model.get('options'),
				size: 'medium'
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
			var content = fw.opg('content', o) || reg.l10n.item_title || 'Text';
			var plain = jQuery('<div>').html(String(content)).text();
			this.defaultRender({
				title:   reg.l10n.item_title || 'Text',
				align:   fw.opg('align', o) || 'left',
				preview: plain.length > 140 ? plain.substring(0, 140) + '…' : (plain || 'Text'),
				edit:    reg.l10n.edit || 'Edit',
				remove:  reg.l10n.remove || 'Remove'
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
		}
	});

	builder.registerItemClass(Item);
});
