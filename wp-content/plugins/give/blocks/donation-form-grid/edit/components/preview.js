/**
 * Internal dependencies
 */
import Inspector from '../inspector';

/**
 * Render Form Preview
 */

const FormGridPreview = ( props ) => {
	return (
		<div id="donation-form-grid-preview-block">
			<Inspector { ... { ...props } } />
			<div dangerouslySetInnerHTML={ { __html: props.html } }></div>
		</div>
	);
};

export default FormGridPreview;
