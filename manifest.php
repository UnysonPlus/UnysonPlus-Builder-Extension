<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name']        = __( 'Builder', 'fw' );
$manifest['description'] = __( 'Unyson Page Builder Extension', 'fw' );

$manifest['version']     = '1.2.13';

// Repository Info
$manifest['github_update'] = 'ThemeFuse/Unyson-Builder-Extension';
$manifest['github_repo']   = 'https://github.com/ThemeFuse/Unyson-Builder-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';

// Documentation
$manifest['uri'] = 'http://manual.unyson.io/en/latest/extension/builder/index.html';
