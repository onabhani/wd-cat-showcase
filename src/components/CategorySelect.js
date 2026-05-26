import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { SelectControl, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export default function CategorySelect( { value, onChange } ) {
	const [ terms, setTerms ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		let cancelled = false;

		apiFetch( {
			path: addQueryArgs( '/wdcs/v1/categories', { per_page: 100 } ),
		} )
			.then( ( data ) => {
				if ( cancelled ) return;
				setTerms( Array.isArray( data ) ? data : [] );
				setLoading( false );
			} )
			.catch( () => {
				if ( cancelled ) return;
				setTerms( [] );
				setLoading( false );
			} );

		return () => {
			cancelled = true;
		};
	}, [] );

	if ( loading ) {
		return <Spinner />;
	}

	const options = [
		{ label: __( '— Select a category —', 'wd-cat-showcase' ), value: 0 },
		...terms.map( ( t ) => ( {
			label: t.parent ? `— ${ t.name }` : t.name,
			value: t.id,
		} ) ),
	];

	return (
		<SelectControl
			label={ __( 'Product category', 'wd-cat-showcase' ) }
			value={ value }
			options={ options }
			onChange={ ( v ) => onChange( parseInt( v, 10 ) || 0 ) }
		/>
	);
}
