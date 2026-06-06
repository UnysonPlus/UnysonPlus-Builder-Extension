(function($, localized){
		var eventsNamespace = '.templates-full',
			loadingId = 'fw-builder-templates-type-full',
			modal,
			lazyInitModal = function () {
				lazyInitModal = function () {};

				modal = new fw.OptionsModal({
					title: localized.l10n.save_template,
					options: [
						{
							'template_name': {
								'type': 'text',
								'label': localized.l10n.template_name
							}
						}
					],
					values: ''
				});
			};

	fwEvents.on('fw:option-type:builder:templates:init', function(data){
		var loading = data.tooltipLoading,
			builder = data.builder,
			tooltipHideCallback = data.tooltipHideCallback,
			tooltipRefreshCallback = data.tooltipRefreshCallback;

		data.$elements.find('.fw-builder-templates-type-full')
			.off(eventsNamespace)
			.on('click'+ eventsNamespace, 'a[data-load-template]', function(){
				var templateId = $(this).attr('data-load-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_full_load',
						'_nonce': (typeof _fw_option_type_builder_templates_full !== 'undefined' ? _fw_option_type_builder_templates_full.nonce : ''),
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to load builder template', json);
							return;
						}

						if (JSON.stringify(builder.rootItems) === json.data.json) {
							console.log('Loaded value is the same as current');
						} else {
							builder.rootItems.reset(JSON.parse(json.data.json));
						}

						tooltipHideCallback();
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a[data-delete-template]', function(){
				var templateId = $(this).attr('data-delete-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_full_delete',
						'_nonce': (typeof _fw_option_type_builder_templates_full !== 'undefined' ? _fw_option_type_builder_templates_full.nonce : ''),
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to delete builder template', json);
							return;
						}

						tooltipRefreshCallback();
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a[data-export-template]', function(){
				// Round-trip a saved template through the export envelope and
				// trigger a browser download via Blob — keeps the AJAX
				// response format symmetric with load/save/delete (we don't
				// need Content-Disposition gymnastics on admin-ajax).
				var templateId = $(this).attr('data-export-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_full_export',
						'_nonce': (typeof _fw_option_type_builder_templates_full !== 'undefined' ? _fw_option_type_builder_templates_full.nonce : ''),
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to export builder template', json);
							return;
						}

						var payload = JSON.stringify(json.data.content, null, 2);
						var blob = new Blob([payload], { type: 'application/json' });
						var url = URL.createObjectURL(blob);
						var a = document.createElement('a');
						a.href = url;
						a.download = json.data.filename;
						document.body.appendChild(a);
						a.click();
						document.body.removeChild(a);
						setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax export error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a.import-template', function () {
				// Delegate to the hidden file input so we get the OS file picker
				// without having to host a separate modal. Reset .value first so
				// re-selecting the same file still fires `change`.
				var $input = $(this).closest('.save-template-wrapper').find('input.template-import-file');
				$input.val('');
				$input.trigger('click');
			})
			.on('change'+ eventsNamespace, 'input.template-import-file', function () {
				var file = this.files && this.files[0];
				if (!file) {
					return;
				}

				var formData = new FormData();
				formData.append('action', 'fw_builder_templates_full_import');
				formData.append('_nonce', (typeof _fw_option_type_builder_templates_full !== 'undefined' ? _fw_option_type_builder_templates_full.nonce : ''));
				formData.append('builder_type', builder.get('type'));
				formData.append('template_file', file);

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: formData,
					processData: false,
					contentType: false
				})
					.done(function (json) {
						loading.hide();

						if (!json.success) {
							var msg = (json.data && json.data.message)
								? json.data.message
								: (localized.l10n.import_failed || 'Failed to import template');
							window.alert(msg);
							return;
						}

						tooltipRefreshCallback();
					})
					.fail(function (xhr, status, error) {
						loading.hide();
						console.error('Ajax import error', error);
						window.alert(localized.l10n.import_failed || 'Failed to import template');
					});
			})
			.on('click'+ eventsNamespace, 'a.save-template', function () {
				tooltipHideCallback();

				lazyInitModal();

				// reset previous values
				modal.set('values', {}, {silent: true});

				// remove previous listener
				modal.off('change:values');

				modal.on('change:values', function (modal, values) {
					fw.loading.show(loadingId);

					$.ajax({
						type: 'post',
						dataType: 'json',
						url: ajaxurl,
						data: {
							'action': 'fw_builder_templates_full_save',
							'_nonce': (typeof _fw_option_type_builder_templates_full !== 'undefined' ? _fw_option_type_builder_templates_full.nonce : ''),
							'template_name': values.template_name,
							'builder_json': JSON.stringify(builder.rootItems),
							'builder_type': builder.get('type')
						}
					})
						.done(function (json) {
							fw.loading.hide(loadingId);

							if (!json.success) {
								console.error('Failed to save builder template', json);
								return;
							}
						})
						.fail(function (xhr, status, error) {
							fw.loading.hide(loadingId);

							console.error('Ajax save error', error);
						});
				});

				modal.open();
			});
	});
})(jQuery, _fw_option_type_builder_templates_full);