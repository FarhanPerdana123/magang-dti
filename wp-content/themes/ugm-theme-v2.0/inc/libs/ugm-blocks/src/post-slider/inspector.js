import {
	BaseControl,
	PanelBody,
	SelectControl,
	TextControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import {
	useTaxonomies
} from '../utils.js';
import AuthorControl from './controls/author-control';
import { TaxonomyControls } from './controls/taxonomy-controls';

export default function PostSliderInspectorControls( {
	attributes,
	setQuery
} ) {
	const { query } = attributes;
	const {
		order,
		orderBy,
		author: authorIds,
		postType,
		taxQuery,
		perPage
	} = query;

	const orderOptions = [
		{
			label: __( 'Newest to oldest' ),
			value: 'date/desc',
		},
		{
			label: __( 'Oldest to newest' ),
			value: 'date/asc',
		},
		{
			/* translators: label for ordering posts by title in ascending order */
			label: __( 'A → Z' ),
			value: 'title/asc',
		},
		{
			/* translators: label for ordering posts by title in descending order */
			label: __( 'Z → A' ),
			value: 'title/desc',
		},
	];

	const [ querySearch, setQuerySearch ] = useState( query.search );
	const taxonomies = useTaxonomies( postType );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'ugm-theme' ) }>
					<BaseControl>
						<NumberControl
							label={ __( 'Posts Count', 'ugm-theme' ) }
							min={ 1 }
							max={ 100 }
							onChange={ ( value ) => {
								if (
									isNaN( value ) ||
									value < 1 ||
									value > 100
								) {
									return;
								}
								setQuery( { perPage: value } );
							} }
							step="1"
							value={ perPage }
							isDragEnabled={ false }
						/>
					</BaseControl>
					<SelectControl
						label={ __( 'Order by' ) }
						value={ `${ orderBy }/${ order }` }
						options={ orderOptions }
						onChange={ ( value ) => {
							const [ newOrderBy, newOrder ] = value.split( '/' );
							setQuery( { order: newOrder, orderBy: newOrderBy } );
						} }
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls>
				<ToolsPanel
					className="block-library-query-toolspanel__filters"
					label={ __( 'Filter Posts', 'ugm-theme' ) }
					resetAll={ () => {
						setQuery( {
							author: '',
							parents: [],
							search: '',
							taxQuery: null,
						} );
						setQuerySearch( '' );
					} }
				>
					{ !! taxonomies?.length && (
						<ToolsPanelItem
							label={ __( 'Taxonomies', 'ugm-theme' ) }
							hasValue={ () =>
								Object.values( taxQuery || {} ).some(
									( terms ) => !! terms.length
								)
							}
							onDeselect={ () =>
								setQuery( { taxQuery: null } )
							}
						>
							<TaxonomyControls
								onChange={ setQuery }
								query={ query }
							/>
						</ToolsPanelItem>
					) }
					<ToolsPanelItem
						hasValue={ () => !! authorIds }
						label={ __( 'Authors', 'ugm-theme' ) }
						onDeselect={ () => setQuery( { author: '' } ) }
					>
						<AuthorControl
							value={ authorIds }
							onChange={ setQuery }
						/>
					</ToolsPanelItem>
					<ToolsPanelItem
						hasValue={ () => !! querySearch }
						label={ __( 'Keyword', 'ugm-theme' ) }
						onDeselect={ () => setQuerySearch( '' ) }
					>
						<TextControl
							label={ __( 'Keyword', 'ugm-theme' ) }
							value={ querySearch }
							onChange={ setQuerySearch }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>
		</>
	)

}