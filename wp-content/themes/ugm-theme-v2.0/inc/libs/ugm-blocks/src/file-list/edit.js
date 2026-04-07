import {
	ToggleControl,
	ToolbarGroup,
	Dropdown,
	ToolbarButton,
	BaseControl,
	TextControl,
	PanelBody,
	__experimentalNumberControl as NumberControl,
	Disabled
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { settings, list, grid } from '@wordpress/icons';
import { 
	BlockControls,
	useBlockProps,
	store as blockEditorStore
} from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';
import { date } from '@wordpress/date';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { clientId, attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	const {
		layout,
		postCount,
		columnCount,
		showAll
	} = attributes;

	useEffect( () => {
		if ( attributes.id === undefined ) {
			const instanceId = `ugm-blocks-event-list-${ clientId.substr( 0, 16 ) }`;
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

	const updatePostCount = ( newCount ) =>
		setAttributes( { postCount: newCount } );
	const updateColumnCount = ( newColumnCount ) =>
		setAttributes( { columnCount: newColumnCount } );
	const updateDisplayLayout = ( newLayout ) =>
		setAttributes( { layout: newLayout } );
	const updateShowAll = ( newShowAll ) =>
		setAttributes( { showAll: { ...showAll, ...newShowAll } } );

	const posts = useSelect(
		( select ) => {
			const { getEntityRecords } = select( coreStore );
			const { getBlocks } = select( blockEditorStore );
			return getEntityRecords( 'postType', 'file', {
				per_page: postCount
			} );
		},
		[
			postCount
		]
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
								<ToggleControl
									label={ __( 'Show More Link', 'ugm-theme' ) }
									checked={ showAll.active }
									onChange={ (value) => updateShowAll({ active: value }) }
								/>
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
							__unstableInputWidth="60px"
							label={ __( 'Files Count' ) }
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
								updatePostCount( value );
							} }
							step="1"
							value={ postCount }
							isDragEnabled={ false }
						/>
					</BaseControl>
					{ 'grid' === layout && (
						<BaseControl>
							<NumberControl
								__unstableInputWidth="60px"
								label={ __( 'Column Count' ) }
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
								isDragEnabled={ false }
							/>
						</BaseControl>
					) }
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
			<div { ...blockProps }>
				{ posts && posts.length && (
					<Disabled>
						<div class="ugm-blocks-block-file-list">
							<div class={ "files files-"+layout+" column-"+columnCount }>
								{ posts.map( post => (
									<article class="post post-file file">
										<div class="post-content">
											<div class="post-title">
												<h3><a href={ post.link }>{ post.title.rendered }</a></h3>
												<span class="post-date">{ date( 'd F Y', post.date ) }</span>
											</div>
										</div>
									</article>
								) ) }
							</div>
							{ showAll.active && (
								<div class="btn-box">
									<a href={ showAll.url } class="btn btn-more">{ showAll.text }</a>
								</div>
							) }
						</div>
					</Disabled>
				) }
			</div>
		</>
	);
}
