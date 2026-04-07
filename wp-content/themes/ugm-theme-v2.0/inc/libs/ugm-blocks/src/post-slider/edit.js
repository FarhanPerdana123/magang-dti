import classnames from "classnames";
import { useSelect, useDispatch } from '@wordpress/data';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import {
	Disabled,
	ToolbarGroup
} from '@wordpress/components';
import { 
	BlockControls,
	useBlockProps,
	store as blockEditorStore
} from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';
import { date } from '@wordpress/date';
import { settings, positionLeft, positionRight } from '@wordpress/icons';
import PostSliderInspectorControls from './inspector';

const IDs = [];

export default function Edit( { clientId, attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	
	const {
		query,
		imagePosition
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
			const instanceId = `ugm-blocks-post-slider-${ clientId.substr( 0, 16 ) }`;
			setAttributes({
				id: instanceId
			});
		}
	}, []);

	const imagePositionControls = [
		{
			icon: positionLeft,
			title: __( 'Image on Left', 'ugm-theme' ),
			onClick: () => updateImagePosition( 'left' ),
			isActive: imagePosition === 'left',
		},
		{
			icon: positionRight,
			title: __( 'Image on Right', 'ugm-theme' ),
			onClick: () =>
				updateImagePosition( 'right' ),
			isActive: imagePosition === 'right',
		},
	];

	const updateQuery = ( newQuery ) =>
		setAttributes( { query: { ...query, ...newQuery } } );
	const updateImagePosition = ( newPosition ) =>
		setAttributes( { imagePosition: newPosition } );

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

	let classNames = classnames(
		'ugm-blocks-block-post-slider',
		'post-slider',
		'image-'+imagePosition	
	);

	return (
		<>
			<BlockControls>
				<ToolbarGroup controls={ imagePositionControls } />
			</BlockControls>
			<PostSliderInspectorControls
				attributes={ attributes }
				setQuery={ updateQuery }
			/>
			<div { ...blockProps }>
				{ posts && posts.length && (
					<Disabled>
						<div className={ classNames } id={attributes.id}>
							<article class="post">
								<div class="post-img">
									<a href={ posts[0].link }>
										{ posts[0]._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
											<img src={ posts[0]._embedded?.['wp:featuredmedia']?.[0]?.source_url } alt={ posts[0].title.rendered } />
										) }
									</a>
								</div>
								<div class="post-content">
									<div class="post-title">
										<h3><a href={ posts[0].link }>{ posts[0].title.rendered }</a></h3>
									</div>
									<div class="entry-content" dangerouslySetInnerHTML={{__html: posts[0].excerpt.rendered}}>
									</div>
									<a href={ posts[0].link } class="btn btn-more">Baca selengkapnya</a>
									<ul class="slick-dots">
										{ posts.map((item, index) => {
											return <li>•</li>
										}) }
									</ul>
								</div>
							</article>
						</div>
					</Disabled>
				) }
			</div>
		</>
	);
}
