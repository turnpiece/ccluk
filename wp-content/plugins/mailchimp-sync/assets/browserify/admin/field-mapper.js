'use strict';

function FieldMapper( $context ) {

	var $ = window.jQuery;

	function addRow() {
		var $row = $context.find(".field-map-row").last();
		var $newRow = $row.clone();
		var $userField = $newRow.find('.user-field');
		var $mailChimpField = $newRow.find('.mailchimp-field');

		$userField.val('').suggest( ajaxurl + "?action=mcs_autocomplete_user_field").attr('name', $userField.attr('name').replace(/\[(\d+)\]/, function (str, p1) {
			return '[' + (parseInt(p1, 10) + 1) + ']';
		}));

		// empty select boxes and set new `name` attribute
		$mailChimpField.val('').attr('name', $mailChimpField.attr('name').replace(/\[(\d+)\]/, function (str, p1) {
			return '[' + (parseInt(p1, 10) + 1) + ']';
		}));

		$newRow.insertAfter($row);

		setAvailableFields();
		return false;
	}

	function removeRow() {
		$(this).parents('.field-map-row').remove();
		setAvailableFields();
	}

	function setAvailableFields() {
		var selectBoxes = $context.find('.mailchimp-field');
		selectBoxes.each(function() {
			var otherSelectBoxes = selectBoxes.not(this);
			var chosenFields = $.map( otherSelectBoxes, function(a,i) { return $(a).val(); });

			$(this).find('option').each(function() {
				var value = $(this).val();
				var alreadyChosen = $.inArray( value, chosenFields) > -1;
				$(this).prop('disabled', ( value === '' || alreadyChosen ) );
			});
		});
	}

	$context.find('.user-field').suggest( ajaxurl + "?action=mcs_autocomplete_user_field" );
	$context.find('.mailchimp-field').change(setAvailableFields).trigger('change');
	$context.find('.add-row').click(addRow);
	$context.on('click', '.remove-row', removeRow);
}

module.exports = FieldMapper;