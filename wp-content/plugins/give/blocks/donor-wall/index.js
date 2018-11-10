/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import giveLogo from './data/icon';
import GiveDonorWallGrid from './edit/block';

/**
 * Register Block
 */

export default registerBlockType( 'give/donor-wall', {

    title: __( 'Give Donor Wall' ),
    description: __( 'The Give Donor Wall block inserts an existing donation form into the page. Each form\'s presentation can be customized below.' ),
    category: 'widgets',
    icon: giveLogo,
    keywords: [
        __( 'donation' ),
        __( 'wall' ),
    ],
    supports: {
        html: false,
    },
    attributes: blockAttributes,
    edit: GiveDonorWallGrid,

    save: () => {
        // Server side rendering via shortcode
        return null;
    },
} );
