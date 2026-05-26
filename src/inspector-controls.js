import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	SelectControl,
	RangeControl,
} from '@wordpress/components';

import CategorySelect from './components/CategorySelect';

export default function InspectorControlsPanel( { attributes, setAttributes } ) {
	const update = ( key ) => ( value ) => setAttributes( { [ key ]: value } );

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Category', 'wd-cat-showcase' ) } initialOpen={ true }>
				<CategorySelect
					value={ attributes.categoryId }
					onChange={ ( id ) => setAttributes( { categoryId: id } ) }
				/>
				<ToggleControl
					label={ __( 'Show child categories', 'wd-cat-showcase' ) }
					checked={ attributes.showChildren }
					onChange={ update( 'showChildren' ) }
				/>
				{ attributes.showChildren && (
					<>
						<ToggleControl
							label={ __( 'Show parent as first tab', 'wd-cat-showcase' ) }
							checked={ attributes.showParentAsFirstTab }
							onChange={ update( 'showParentAsFirstTab' ) }
						/>
						{ attributes.showParentAsFirstTab && (
							<TextControl
								label={ __( 'Parent tab label (optional)', 'wd-cat-showcase' ) }
								value={ attributes.parentTabLabel }
								onChange={ update( 'parentTabLabel' ) }
								help={ __( 'Defaults to the category name.', 'wd-cat-showcase' ) }
							/>
						) }
					</>
				) }
			</PanelBody>

			<PanelBody title={ __( 'Header', 'wd-cat-showcase' ) } initialOpen={ false }>
				<TextControl
					label={ __( 'Label (small text above title)', 'wd-cat-showcase' ) }
					value={ attributes.label }
					onChange={ update( 'label' ) }
				/>
				<TextControl
					label={ __( 'Title', 'wd-cat-showcase' ) }
					value={ attributes.title }
					onChange={ update( 'title' ) }
					help={ __( 'Defaults to the selected category name.', 'wd-cat-showcase' ) }
				/>
				<ToggleControl
					label={ __( 'Show "View All" link', 'wd-cat-showcase' ) }
					checked={ attributes.showViewAll }
					onChange={ update( 'showViewAll' ) }
				/>
				{ attributes.showViewAll && (
					<>
						<TextControl
							label={ __( 'View All text', 'wd-cat-showcase' ) }
							value={ attributes.viewAllText }
							onChange={ update( 'viewAllText' ) }
						/>
						<TextControl
							label={ __( 'View All URL (optional)', 'wd-cat-showcase' ) }
							value={ attributes.viewAllUrl }
							onChange={ update( 'viewAllUrl' ) }
							help={ __( 'Defaults to the selected category archive.', 'wd-cat-showcase' ) }
						/>
					</>
				) }
			</PanelBody>

			<PanelBody title={ __( 'Products', 'wd-cat-showcase' ) } initialOpen={ false }>
				<RangeControl
					label={ __( 'Products to show', 'wd-cat-showcase' ) }
					value={ attributes.itemsPerPage }
					onChange={ update( 'itemsPerPage' ) }
					min={ 1 }
					max={ 24 }
				/>
				<SelectControl
					label={ __( 'Product source', 'wd-cat-showcase' ) }
					value={ attributes.productSource }
					options={ [
						{ label: __( 'Bestselling', 'wd-cat-showcase' ), value: 'bestselling' },
						{ label: __( 'Latest', 'wd-cat-showcase' ), value: 'recent_product' },
						{ label: __( 'Featured', 'wd-cat-showcase' ), value: 'featured_product' },
						{ label: __( 'On sale', 'wd-cat-showcase' ), value: 'sale_products' },
					] }
					onChange={ update( 'productSource' ) }
				/>
				<SelectControl
					label={ __( 'Order by', 'wd-cat-showcase' ) }
					value={ attributes.orderby }
					options={ [
						{ label: 'popularity', value: 'popularity' },
						{ label: 'date', value: 'date' },
						{ label: 'price', value: 'price' },
						{ label: 'rating', value: 'rating' },
						{ label: 'menu_order', value: 'menu_order' },
						{ label: 'rand', value: 'rand' },
					] }
					onChange={ update( 'orderby' ) }
				/>
				<SelectControl
					label={ __( 'Order direction', 'wd-cat-showcase' ) }
					value={ attributes.order }
					options={ [
						{ label: 'DESC', value: 'DESC' },
						{ label: 'ASC', value: 'ASC' },
					] }
					onChange={ update( 'order' ) }
				/>
				<ToggleControl
					label={ __( 'Hide out of stock', 'wd-cat-showcase' ) }
					checked={ attributes.hideOutOfStock }
					onChange={ update( 'hideOutOfStock' ) }
				/>
				<TextControl
					label={ __( 'Spacing (px)', 'wd-cat-showcase' ) }
					value={ attributes.spacing }
					onChange={ update( 'spacing' ) }
				/>
			</PanelBody>
		</InspectorControls>
	);
}
