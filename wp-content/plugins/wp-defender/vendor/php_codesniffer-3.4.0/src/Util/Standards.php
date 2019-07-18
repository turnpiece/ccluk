<?php
/**
 * Functions for helping process standards.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Util;

use PHP_CodeSniffer\Config;

class Standards {


	/**
	 * Get a list paths where standards are installed.
	 *
	 * @return array
	 */
	public static function getInstalledStandardPaths() {
		return array();

	}//end getInstalledStandardPaths()


	/**
	 * Get the details of all coding standards installed.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a Sniffs subdirectory.
	 *
	 * The details returned for each standard are:
	 * - path:      the path to the coding standard's main directory
	 * - name:      the name of the coding standard, as sourced from the ruleset.xml file
	 * - namespace: the namespace used by the coding standard, as sourced from the ruleset.xml file
	 *
	 * If you only need the paths to the installed standards,
	 * use getInstalledStandardPaths() instead as it performs less work to
	 * retrieve coding standard names.
	 *
	 * @param boolean $includeGeneric If true, the special "Generic"
	 *                                coding standard will be included
	 *                                if installed.
	 * @param string $standardsDir A specific directory to look for standards
	 *                                in. If not specified, PHP_CodeSniffer will
	 *                                look in its default locations.
	 *
	 * @return array
	 * @see    getInstalledStandardPaths()
	 */
	public static function getInstalledStandardDetails(
		$includeGeneric = false,
		$standardsDir = ''
	) {
		return array();

	}//end getInstalledStandardDetails()


	/**
	 * Get a list of all coding standards installed.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a Sniffs subdirectory.
	 *
	 * @param boolean $includeGeneric If true, the special "Generic"
	 *                                coding standard will be included
	 *                                if installed.
	 * @param string $standardsDir A specific directory to look for standards
	 *                                in. If not specified, PHP_CodeSniffer will
	 *                                look in its default locations.
	 *
	 * @return array
	 * @see    isInstalledStandard()
	 */
	public static function getInstalledStandards(
		$includeGeneric = false,
		$standardsDir = ''
	) {

		return array();

	}//end getInstalledStandards()


	/**
	 * Determine if a standard is installed.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a ruleset.xml file.
	 *
	 * @param string $standard The name of the coding standard.
	 *
	 * @return boolean
	 * @see    getInstalledStandards()
	 */
	public static function isInstalledStandard( $standard ) {
		return false;

	}//end isInstalledStandard()


	/**
	 * Return the path of an installed coding standard.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a ruleset.xml file.
	 *
	 * @param string $standard The name of the coding standard.
	 *
	 * @return string|null
	 */
	public static function getInstalledStandardPath( $standard ) {

		return null;

	}//end getInstalledStandardPath()


	/**
	 * Prints out a list of installed coding standards.
	 *
	 * @return void
	 */
	public static function printInstalledStandards() {


	}//end printInstalledStandards()


}//end class
