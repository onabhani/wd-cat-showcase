import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import metadata from '../block.json';
import Edit from './edit';
import './style.scss';

registerBlockType( metadata.name, {
	icon: 'screenoptions',
	edit: Edit,
	// Dynamic block — render is handled in PHP. `save` returns null.
	save: () => null,
} );
