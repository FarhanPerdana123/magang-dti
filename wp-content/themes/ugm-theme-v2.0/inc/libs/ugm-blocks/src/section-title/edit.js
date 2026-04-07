import {
	TextControl,
	ToolbarGroup,
	Dropdown,
	ToolbarButton,
	BaseControl,
	__experimentalNumberControl as NumberControl,
	Disabled,
	ToolbarDropdownMenu
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { alignLeft, alignCenter, alignRight } from '@wordpress/icons';
import {
	RichText,
	BlockControls,
	useBlockProps,
	store as blockEditorStore
} from '@wordpress/block-editor';
import {
    verse,
	chevronRight
} from '@wordpress/icons';
import { store as coreStore } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';
import HeadingLevelDropdown from './heading-level-dropdown';
import classnames from "classnames";

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
		align,
		level,
		content,
		headingStyle
	} = attributes;

	const tagName = 'h' + level;

	useEffect( () => {
		if ( attributes.id === undefined ) {
			const instanceId = `ugm-blocks-section-title-${ clientId.substr( 0, 16 ) }`;
			setAttributes({
				id: instanceId
			});
		}
	}, []);


	const { __unstableMarkNextChangeAsNotPersistent } =
		useDispatch( blockEditorStore );
	const instanceId = useInstanceId( Edit );

	const alignControls = [
		{
			icon: alignLeft,
			title: __( 'Left', 'ugm-theme' ),
			onClick: () => updateAlign( 'left' ),
			isActive: align === 'left',
		},
		{
			icon: alignCenter,
			title: __( 'Center', 'ugm-theme' ),
			onClick: () =>
				updateAlign( 'center' ),
			isActive: align === 'center',
		},
		{
			icon: alignRight,
			title: __( 'Right', 'ugm-theme' ),
			onClick: () =>
				updateAlign( 'right' ),
			isActive: align === 'right',
		},
	];
	const updateAlign = ( value ) =>
		setAttributes( { align: value } );
	const updateHeadingStyle = ( value ) =>
		setAttributes( { headingStyle: value } );

	let classNames = classnames(
		'ugm-blocks-block-heading',
		'align-'+align,
		'style-'+headingStyle
	);

	return (
		<>
			<BlockControls group="block">
				<ToolbarDropdownMenu
					icon={ verse }
					label="Heading Style"
					controls={ [
						{
							icon: chevronRight,
							title: 'Style 1',
							onClick: () => updateHeadingStyle( 1 ),
							isActive: 1 == headingStyle
						},
						{
							icon: chevronRight,
							title: 'Style 2',
							onClick: () => updateHeadingStyle( 2 ),
							isActive: 2 == headingStyle
						},
						{
							icon: chevronRight,
							title: 'Style 3',
							onClick: () => updateHeadingStyle( 3 ),
							isActive: 3 == headingStyle
						}
					] }
				/>
				<HeadingLevelDropdown
					selectedLevel={ level }
					onChange={ ( newLevel ) =>
						setAttributes( { level: newLevel } )
					}
				/>
				<ToolbarGroup controls={ alignControls } />
			</BlockControls>
			<div { ...blockProps }>
				<div className={ classNames }>
					<span class="line-before"></span>
					<RichText
						withoutInteractiveFormatting={ true }
						tagName={tagName}
						value={ attributes.content }
						onChange={ ( content ) => setAttributes( { content } ) }
						placeholder={ __( 'Add text here...', 'tonjoo-blocks' ) }
					/>
					<span class="line-after"></span>
				</div>
			</div>
		</>
	);
}
