/**
 * Strings internationalization
 *
 * @param str
 *
 * @returns {*|string}
 */
export const __  = ( str ) => {
    return wphb.strings[ str ] || '';
};

/**
 * Get a link to a HB screen
 *
 * @param {string} screen Screen slug
 * @returns {string}
 */
export const getLink = ( screen ) => {
    return wphb.links[ screen ] || '';
};

