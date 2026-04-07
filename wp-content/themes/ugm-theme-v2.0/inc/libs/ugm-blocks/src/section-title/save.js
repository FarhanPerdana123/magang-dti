/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	useBlockProps,
	InnerBlocks,
	RichText
} from '@wordpress/block-editor';
import classnames from "classnames";

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {Object} props            Properties passed to the function.
 * @param {Object} props.attributes Available block attributes.
 * @return {WPElement} Element to render.
 */
export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();
	let classNames = classnames(
		'ugm-blocks-block-heading',
		'align-'+attributes.align,
		'style-'+attributes.headingStyle
	);

	const tagName = 'h' + attributes.level;
	return (
		<div { ...blockProps }>
			<div className={ classNames }>
				<span class="line-before"></span>
				<RichText.Content tagName={ tagName } value={ attributes.content } />
				<span class="line-after"></span>
			</div>
		</div>
	);
}
