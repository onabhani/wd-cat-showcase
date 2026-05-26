import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import InspectorControlsPanel from './inspector-controls';
import './editor.scss';

const generateBlockId = () => {
	if ( typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function' ) {
		return `wdcs-${ crypto.randomUUID() }`;
	}
	return `wdcs-${ Date.now() }-${ Math.floor( Math.random() * 1e9 ) }`;
};

export default function Edit( { attributes, setAttributes, clientId } ) {
	const blockProps = useBlockProps();

	// Persist a unique blockId on first insert so Woodmart's scoped CSS doesn't collide
	// across multiple instances on the same page.
	useEffect( () => {
		if ( ! attributes.blockId ) {
			setAttributes( { blockId: generateBlockId() } );
		}
	}, [] );

	return (
		<div { ...blockProps }>
			<InspectorControlsPanel
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>

			{ attributes.categoryId > 0 ? (
				<ServerSideRender
					block="hdqah/wd-cat-showcase"
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => (
						<p>{ __( 'Loading showcase preview…', 'wd-cat-showcase' ) }</p>
					) }
					EmptyResponsePlaceholder={ () => (
						<p>{ __( 'No output. Check the selected category and Woodmart activation.', 'wd-cat-showcase' ) }</p>
					) }
				/>
			) : (
				<div className="wdcs-editor-placeholder">
					<p>
						{ __(
							'Select a product category in the block sidebar to render the showcase.',
							'wd-cat-showcase'
						) }
					</p>
				</div>
			) }
		</div>
	);
}
