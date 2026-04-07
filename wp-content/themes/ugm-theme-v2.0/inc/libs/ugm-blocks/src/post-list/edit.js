import {
	ToolbarGroup,
	Dropdown,
	ToolbarButton,
	BaseControl,
	PanelBody,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalNumberControl as NumberControl,
	ToggleControl,
	SelectControl,
	TextControl,
	Disabled
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { settings, list, grid } from '@wordpress/icons';
import { 
	BlockControls,
	useBlockProps,
	InspectorControls,
	store as blockEditorStore
} from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import {
	useEffect,
	useState
} from '@wordpress/element';
import { date } from '@wordpress/date';
import classnames from "classnames";
import {
	useTaxonomies
} from '../utils.js';
import AuthorControl from './controls/author-control';
import { TaxonomyControls } from './controls/taxonomy-controls';

export default function Edit( { clientId, attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	const {
		query,
		show,
		showAll,
		layout,
		columnCount
	} = attributes;
	const {
		order,
		orderBy,
		author: authorIds,
		postType,
		taxQuery,
		perPage,
		search
	} = query;

	useEffect( () => {
		if ( attributes.id === undefined ) {
			const instanceId = `ugm-blocks-post-list-${ clientId.substr( 0, 16 ) }`;
			setAttributes({
				id: instanceId
			});
		}
	}, []);

	
	const { __unstableMarkNextChangeAsNotPersistent } =
		useDispatch( blockEditorStore );
	const instanceId = useInstanceId( Edit );

	const displayLayoutControls = [
		{
			icon: list,
			title: __( 'List view' ),
			onClick: () => updateDisplayLayout( 'list' ),
			isActive: layout === 'list',
		},
		{
			icon: grid,
			title: __( 'Grid view' ),
			onClick: () =>
				updateDisplayLayout( 'grid' ),
			isActive: layout === 'grid',
		},
	];
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

	const [ querySearch, updateQuerySearch ] = useState( query.search );
	const taxonomies = useTaxonomies( postType );
	
	const updateColumnCount = ( newColumnCount ) =>
		setAttributes( { columnCount: newColumnCount } );
	const updateDisplayLayout = ( newLayout ) =>
		setAttributes( { layout: newLayout } );
	const updateShow = ( newShow ) =>
		setAttributes( { show: { ...show, ...newShow } } );
	const updateShowAll = ( newShowAll ) =>
		setAttributes( { showAll: { ...showAll, ...newShowAll } } );
	const updateQuery = ( newQuery ) =>
		setAttributes( { query: { ...query, ...newQuery } } );

	const posts = useSelect(
		( select ) => {
			const { getEntityRecords, getTaxonomies } = select( coreStore );
			const taxonomies = getTaxonomies( {
				type: 'postType',
				per_page: -1,
				context: 'view',
			} );
			const query = {
				order,
				orderby: orderBy,
			};
			// There is no need to build the taxQuery if we inherit.
			if ( taxQuery ) {
				// We have to build the tax query for the REST API and use as
				// keys the taxonomies `rest_base` with the `term ids` as values.
				const builtTaxQuery = Object.entries( taxQuery ).reduce(
					( accumulator, [ taxonomySlug, terms ] ) => {
						const taxonomy = taxonomies?.find(
							( { slug } ) => slug === taxonomySlug
						);
						if ( taxonomy?.rest_base ) {
							accumulator[ taxonomy?.rest_base ] = terms;
						}
						return accumulator;
					},
					{}
				);
				if ( !! Object.keys( builtTaxQuery ).length ) {
					Object.assign( query, builtTaxQuery );
				}
			}
			if ( perPage ) {
				query.per_page = perPage;
			}
			if ( authorIds ) {
				query.author = authorIds;
			}
			if ( search ) {
				query.search = search;
			}
			if ( order ) {
				query.order = order;
			}
			return getEntityRecords( 'postType', 'post', {
				_embed: true,
				...query,
				// ...restQueryArgs,
			} );
		},
		[
			perPage,
			order,
			orderBy,
			clientId,
			authorIds,
			search,
			postType,
			taxQuery,
			// restQueryArgs,
		]
	);

	let wrapperClass = classnames(
		'posts',
		"posts-"+layout,
		"column-"+columnCount,
		{ "show-thumbnail": show.thumbnail },
		{ "show-date": show.date },
		{ "show-excerpt": show.excerpt },
		{ "show-link": showAll.active }
	);

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<Dropdown
						contentClassName="block-library-query-toolbar__popover"
						renderToggle={ ( { onToggle } ) => (
							<ToolbarButton
								icon={ settings }
								label={ __( 'Display settings' ) }
								onClick={ onToggle }
							/>
						) }
						renderContent={ () => (
							<>
								<BaseControl>
									<ToggleControl
										label={ __( 'Show Thumbnail', 'ugm-theme' ) }
										checked={ show.thumbnail }
										onChange={ (value) => updateShow({ thumbnail: value }) }
									/>
									<ToggleControl
										label={ __( 'Show Date', 'ugm-theme' ) }
										checked={ show.date }
										onChange={ (value) => updateShow({ date: value }) }
									/>
									<ToggleControl
										label={ __( 'Show Excerpt', 'ugm-theme' ) }
										checked={ show.excerpt }
										onChange={ (value) => updateShow({ excerpt: value }) }
									/>
									<ToggleControl
										label={ __( 'Show More Link', 'ugm-theme' ) }
										checked={ showAll.active }
										onChange={ (value) => updateShowAll({ active: value }) }
									/>
								</BaseControl>
							</>
						) }
					/>
				</ToolbarGroup>
				<ToolbarGroup controls={ displayLayoutControls } />
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'ugm-theme' ) }>
					<BaseControl>
						<NumberControl
							__unstableInputWidth="100px"
							label={ __( 'Posts Count' ) }
							labelPosition="edge"
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
								updateQuery( { perPage: value } );
							} }
							step="1"
							value={ perPage }
							isDragEnabled={ false }
						/>
					</BaseControl>
					{ 'grid' === layout && (
						<BaseControl>
							<NumberControl
								__unstableInputWidth="100px"
								label={ __( 'Column Count', 'ugm-theme' ) }
								labelPosition="edge"
								min={ 1 }
								max={ 4 }
								onChange={ ( value ) => {
									if (
										isNaN( value ) ||
										value < 1 ||
										value > 4
									) {
										return;
									}
									updateColumnCount( value );
								} }
								step="1"
								value={ columnCount }
								isDragEnabled={ true }
							/>
						</BaseControl>
					) }
					<BaseControl>
						<SelectControl
							__unstableInputWidth="100px"
							label={ __( 'Order by', 'ugm-theme' ) }
							value={ `${ orderBy }/${ order }` }
							options={ orderOptions }
							onChange={ ( value ) => {
								const [ newOrderBy, newOrder ] = value.split( '/' );
								updateQuery( { order: newOrder, orderBy: newOrderBy } );
							} }
						/>
					</BaseControl>
					{ showAll.active && (
						<BaseControl>
							<TextControl
								label={ __( 'Show More URL', 'ugm-theme' ) }
								value={ showAll.url }
								onChange={ (value) => updateShowAll({ url: value }) }
							/>
							<TextControl
								label={ __( 'Show More Text', 'ugm-theme' ) }
								value={ showAll.text }
								onChange={ (value) => updateShowAll({ text: value }) }
							/>
						</BaseControl>
					) }
				</PanelBody>
			</InspectorControls>
			<InspectorControls>
				<ToolsPanel
					className="block-library-query-toolspanel__filters"
					label={ __( 'Filter Posts', 'ugm-theme' ) }
					resetAll={ () => {
						updateQuery( {
							author: '',
							parents: [],
							search: '',
							taxQuery: null,
						} );
						updateQuerySearch( '' );
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
								updateQuery( { taxQuery: null } )
							}
						>
							<TaxonomyControls
								onChange={ updateQuery }
								query={ query }
							/>
						</ToolsPanelItem>
					) }
					<ToolsPanelItem
						hasValue={ () => !! authorIds }
						label={ __( 'Authors', 'ugm-theme' ) }
						onDeselect={ () => updateQuery( { author: '' } ) }
					>
						<AuthorControl
							value={ authorIds }
							onChange={ updateQuery }
						/>
					</ToolsPanelItem>
					<ToolsPanelItem
						hasValue={ () => !! querySearch }
						label={ __( 'Keyword', 'ugm-theme' ) }
						onDeselect={ () => updateQuerySearch( '' ) }
					>
						<TextControl
							label={ __( 'Keyword', 'ugm-theme' ) }
							value={ querySearch }
							onChange={ updateQuerySearch }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...blockProps }>
				{ posts && posts.length && (
					<Disabled>
						<div className="ugm-blocks-block-post-list">
							<div className={ wrapperClass }>
								{ posts.map( post => (
									<article className="post">
										{ show.thumbnail && (
											<div className="post-img">
												<a href={ post.link }>
													{ post._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
														<img src={ post._embedded?.['wp:featuredmedia']?.[0]?.source_url } alt={ post.title.rendered } />
													) }
												</a>
											</div>
										) }
										<div className="post-content">
											<div className="post-title">
												<h3><a href={ post.link }>{ post.title.rendered }</a></h3>
												{ show.date && (
													<span className="post-date">{ date( 'd F Y', post.date ) }</span>
												) }
											</div>
											{ show.excerpt && (
												<div className="entry-content" dangerouslySetInnerHTML={{__html: posts[0].excerpt.rendered}}></div>
											) }
										</div>
									</article>
								) ) }
							</div>
							{ showAll.active && (
								<div className="btn-box">
									<a href={ showAll.url } className="btn btn-more">{ showAll.text }</a>
								</div>
							) }
						</div>
					</Disabled>
					)
				}
			</div>
		</>
	);
}
