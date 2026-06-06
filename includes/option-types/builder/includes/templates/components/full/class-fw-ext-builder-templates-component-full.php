<?php if (!defined('FW')) die('Forbidden');

class FW_Ext_Builder_Templates_Component_Full extends FW_Ext_Builder_Templates_Component
{
	public function get_type()
	{
		return 'full';
	}

	public function get_title()
	{
		return __('Full Templates', 'fw');
	}

	public function _render($data)
	{
		$html = '';

		$templates = $this->get_templates($data['builder_type']);

		{
			$this->fake_created_value = 0;

			$templates = array_map( // make this to keep elements order after applying uasort()
				array($this, 'array_map_add_fake_created_key'),
				$templates
			);
		}

		uasort($templates, array($this, 'sort_templates'));

		foreach ($templates as $template_id => $template) {
			if (isset($template['type']) && $template['type'] === 'predefined') {
				$delete_btn = '';
			} else {
				$delete_btn = '<a href="#" onclick="return false;" data-delete-template="'. fw_htmlspecialchars($template_id) .'"'
				              . ' class="template-delete dashicons fw-x" title="'. esc_attr__('Delete', 'fw') .'"></a>';
			}

			$export_btn = '<a href="#" onclick="return false;" data-export-template="'. fw_htmlspecialchars($template_id) .'"'
			              . ' class="template-export dashicons dashicons-download" title="'. esc_attr__('Export', 'fw') .'"></a>';

			$html .=
				'<li>'
					. $delete_btn
					. $export_btn
					. '<a href="#" onclick="return false;" data-load-template="'. fw_htmlspecialchars($template_id) .'"'
					. ' class="template-title">'
						. fw_htmlspecialchars($template['title'])
					. '</a>'
				. '</li>';
		}

		if (empty($html)) {
			$html = '<div class="fw-text-muted no-'. $this->get_type() .'-templates">'. __('No Templates Saved', 'fw') .'</div>';
		} else {
			$html =
				'<p class="fw-text-muted load-template-title">'. __('Load Template', 'fw') .':</p>'
				. '<ul class="std">'. $html .'</ul>';
		}

		$html =
			'<div class="save-template-wrapper">'
				. '<a href="#" onclick="return false;" class="save-template button button-primary">'
					. __('Save Full Template', 'fw')
				. '</a>'
				. ' '
				. '<a href="#" onclick="return false;" class="import-template button button-secondary">'
					. __('Import…', 'fw')
				. '</a>'
				. '<input type="file" class="template-import-file fw-hidden" accept="application/json,.json" />'
			. '</div>'
			. $html;

		return $html;
	}

	public function _enqueue($data)
	{
		$uri = fw_ext('builder')->get_uri('/includes/option-types/builder/includes/templates/components/'. $this->get_type());
		$version = fw_ext('builder')->manifest->get_version();

		wp_enqueue_style(
			'fw-option-builder-templates-'. $this->get_type(),
			$uri .'/styles.css',
			array('fw-option-builder-templates'),
			$version
		);

		wp_enqueue_script(
			'fw-option-builder-templates-'. $this->get_type(),
			$uri .'/scripts.js',
			array('fw-option-builder-templates'),
			$version,
			true
		);

		wp_localize_script(
			'fw-option-builder-templates-'. $this->get_type(),
			'_fw_option_type_builder_templates_'. $this->get_type(),
			array(
				'l10n' => array(
					'template_name'     => __('Template Name', 'fw'),
					'save_template'     => __('Save Builder Template', 'fw'),
					'import_failed'     => __('Failed to import template', 'fw'),
					'import_not_json'   => __('That file is not a valid JSON file', 'fw'),
					'import_no_file'    => __('Please choose a file to import', 'fw'),
				),
				'nonce' => wp_create_nonce( 'fw_builder_templates_'. $this->get_type() ),
			)
		);
	}

	public function _init()
	{
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_load',   array($this, '_action_ajax_load_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_save',   array($this, '_action_ajax_save_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_delete', array($this, '_action_ajax_delete_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_export', array($this, '_action_ajax_export_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_import', array($this, '_action_ajax_import_template'));
	}

	private $fake_created_value;

	private function array_map_add_fake_created_key($el)
	{
		if (!isset($el['created'])) {
			/**
			 * Before 1.1.14 templates were appended
			 * After 1.1.14 templates are prepended
			 * So reverse old templates to be in the same order as the new ones
			 */
			$el['created'] = (++$this->fake_created_value);
		}

		return $el;
	}

	private function get_templates($builder_type)
	{
		return $this->get_db_templates($builder_type) + $this->get_predefined_templates($builder_type);
	}

	private function get_wp_option_prefix($builder_type)
	{
		return 'fw:bt:f:'. $builder_type .':';
	}

	private function sort_templates($a, $b)
	{
		$at = isset($a['created']) ? $a['created'] : 0;
		$bt = isset($b['created']) ? $b['created'] : 0;

		if ($at == $bt) {
			return 0;
		}

		return ($at > $bt) ? -1 : 1;
	}

	/**
	 * @internal
	 */
	public function _action_ajax_load_template()
	{
		check_ajax_referer( 'fw_builder_templates_full', '_nonce' );

		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$templates = $this->get_templates($builder_type);

		$template_id = (string)FW_Request::POST('template_id');

		if (!isset($templates[$template_id])) {
			wp_send_json_error();
		}

		wp_send_json_success(array(
			'json' => $templates[$template_id]['json']
		));
	}

	/**
	 * @internal
	 */
	public function _action_ajax_save_template()
	{
		check_ajax_referer( 'fw_builder_templates_full', '_nonce' );

		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$template = array(
			'title' => trim((string)FW_Request::POST('template_name')),
			'json' => trim((string)FW_Request::POST('builder_json')),
			'created' => time(),
		);

		if (
			empty($template['json'])
			||
			($decoded_json = json_decode($template['json'], true)) === null
		) {
			wp_send_json_error();
		}

		unset($decoded_json);

		if (empty($template['title'])) {
			$template['title'] = __('No Title', 'fw');
		}

		$template_id = md5($template['json']);

		update_option(
			$this->get_wp_option_prefix($builder_type) . $template_id,
			$template,
			false
		);

		/**
		 * Remove from old storage (to prevent array key merge with old value on get)
		 */
		{
			$old_templates = fw_get_db_extension_data('builder', 'templates/'. $builder_type, array());

			unset($old_templates[$template_id]);

			fw_set_db_extension_data('builder', 'templates/'. $builder_type, $old_templates);

			unset($old_templates);
		}

		wp_send_json_success();
	}

	/**
	 * @internal
	 */
	public function _action_ajax_delete_template()
	{
		check_ajax_referer( 'fw_builder_templates_full', '_nonce' );

		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$template_id = (string)FW_Request::POST('template_id');

		delete_option($this->get_wp_option_prefix($builder_type) . $template_id);

		/**
		 * Remove from old storage
		 */
		{
			$old_templates = fw_get_db_extension_data('builder', 'templates/'. $builder_type, array());

			unset($old_templates[$template_id]);

			fw_set_db_extension_data('builder', 'templates/'. $builder_type, $old_templates);

			unset($old_templates);
		}

		wp_send_json_success();
	}

	/**
	 * Wrap a saved template in the portable export envelope and return it
	 * as a JSON payload. The client (scripts.js) then converts the payload
	 * to a Blob and triggers a file download — this avoids
	 * Content-Disposition gymnastics on the admin-ajax endpoint and keeps
	 * the response symmetric with the other AJAX actions (success / error
	 * wrappers, standard nonce + capability checks).
	 *
	 * @internal
	 */
	public function _action_ajax_export_template()
	{
		check_ajax_referer( 'fw_builder_templates_full', '_nonce' );

		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$templates = $this->get_templates($builder_type);

		$template_id = (string)FW_Request::POST('template_id');

		if (!isset($templates[$template_id])) {
			wp_send_json_error();
		}

		$template = $templates[$template_id];

		$envelope = array(
			'_fw_template_export' => array(
				'format_version' => 2, // v2: per-element Custom CSS travels inside `json`
				'kind'           => $this->get_type(),
				'builder_type'   => $builder_type,
				'plugin_version' => fw()->manifest->get_version(),
				'exported_at'    => time(),
			),
			'title'   => isset($template['title']) ? (string)$template['title'] : '',
			'json'    => (string)$template['json'],
			'created' => isset($template['created']) && is_numeric($template['created']) ? (int)$template['created'] : time(),
		);

		$slug = sanitize_title($envelope['title']);
		if (empty($slug)) {
			$slug = 'template';
		}

		wp_send_json_success(array(
			'filename' => $slug .'-'. $this->get_type() .'-'. substr($template_id, 0, 8) .'.json',
			'content'  => $envelope,
		));
	}

	/**
	 * Validate an uploaded template JSON file and save it as a regular
	 * user template in `wp_options`. The deterministic option key derived
	 * from `md5($json)` means re-importing the same content is idempotent
	 * — the second import overwrites the same row with identical data
	 * rather than creating a duplicate entry.
	 *
	 * @internal
	 */
	public function _action_ajax_import_template()
	{
		check_ajax_referer( 'fw_builder_templates_full', '_nonce' );

		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		if (
			empty($_FILES['template_file'])
			|| !isset($_FILES['template_file']['error'])
			|| $_FILES['template_file']['error'] !== UPLOAD_ERR_OK
		) {
			wp_send_json_error(array('message' => __('No file uploaded', 'fw')));
		}

		// Cap file size at 5 MB — saved templates can be large, but anything
		// over 5 MB is almost certainly not a template export.
		if ($_FILES['template_file']['size'] > 5 * 1024 * 1024) {
			wp_send_json_error(array('message' => __('File is too large', 'fw')));
		}

		// Use file_get_contents on the upload's tmp path. Avoid wp_handle_upload
		// here — we don't want to land the file in the media library, we just
		// need to parse the JSON and discard.
		$tmp = isset($_FILES['template_file']['tmp_name'])
			? sanitize_text_field((string)$_FILES['template_file']['tmp_name'])
			: '';

		if (empty($tmp) || !is_uploaded_file($tmp)) {
			wp_send_json_error(array('message' => __('Upload failed', 'fw')));
		}

		$contents = @file_get_contents($tmp);

		if ($contents === false || $contents === '') {
			wp_send_json_error(array('message' => __('Could not read uploaded file', 'fw')));
		}

		$data = json_decode($contents, true);

		if (!is_array($data)) {
			wp_send_json_error(array('message' => __('That file is not a valid JSON file', 'fw')));
		}

		// Validate envelope.
		if (
			!isset($data['_fw_template_export'])
			|| !is_array($data['_fw_template_export'])
		) {
			wp_send_json_error(array('message' => __('Not an Unyson+ template file', 'fw')));
		}

		$envelope = $data['_fw_template_export'];

		if (!isset($envelope['kind']) || $envelope['kind'] !== $this->get_type()) {
			wp_send_json_error(array('message' => sprintf(
				/* translators: 1: envelope kind found in the file, 2: kind expected by this importer */
				__('This is a %1$s template — open the %2$s list to import it.', 'fw'),
				isset($envelope['kind']) ? (string)$envelope['kind'] : __('unknown', 'fw'),
				$this->get_type()
			)));
		}

		if (
			!isset($envelope['builder_type'])
			|| $envelope['builder_type'] !== $builder_type
		) {
			wp_send_json_error(array('message' => __('Template was exported from a different builder type', 'fw')));
		}

		// Validate body.
		if (!isset($data['json']) || !is_string($data['json'])) {
			wp_send_json_error(array('message' => __('Template file is missing the body', 'fw')));
		}

		$template_json = trim((string)$data['json']);

		if (empty($template_json) || json_decode($template_json, true) === null) {
			wp_send_json_error(array('message' => __('Template content is not valid JSON', 'fw')));
		}

		$template = array(
			'title'   => isset($data['title']) ? trim((string)$data['title']) : '',
			'json'    => $template_json,
			'created' => isset($data['created']) && is_numeric($data['created']) ? (int)$data['created'] : time(),
		);

		if (empty($template['title'])) {
			$template['title'] = __('Imported Template', 'fw');
		}

		$template_id = md5($template['json']);

		update_option(
			$this->get_wp_option_prefix($builder_type) . $template_id,
			$template,
			false
		);

		wp_send_json_success(array(
			'id'    => $template_id,
			'title' => $template['title'],
		));
	}

	/**
	 * @param $builder_type
	 * @return mixed|null
	 *
	 * Note: Templates can be very big and saving them in a single wp option can throw mysql error on update query
	 */
	protected function get_db_templates($builder_type)
	{
		$templates = array();

		/**
		 * Note: 'prefix + name' max length should be 64
		 */
		$option_prefix = $this->get_wp_option_prefix($builder_type); // + md5 (length=32)

		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		foreach ((array)$wpdb->get_results($wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( $option_prefix ) .'%'
		), ARRAY_A) as $row) {
			$templates[
				// extract (suffix) md5 used as id
				preg_replace('/^'. preg_quote($option_prefix, '/') .'/', '', $row['option_name'])
			] = get_option($row['option_name']);
		}

		$templates +=
			/**
			 * Append old templates
			 * This can't be removed because a lot of installations already use this
			 */
			fw_get_db_extension_data('builder', 'templates/'. $builder_type, array());

		return $templates;
	}
}
