jQuery(function($)
{
  $(document).ready(function()
	{
	  $('.tomas-tooltips-howto-settings').hide();
	$('.tooltips-pro-how-to-each-bar').click(function()
	{
		var each_user_role_id = $(this).attr('data-user-role');
         $('#' + each_user_role_id ).fadeToggle();
        if ($('#' + 'bp-members-pro-compent-plus-' + each_user_role_id).text() == '+')
        {
        	 $('#' + 'bp-members-pro-compent-plus-' + each_user_role_id).text('-');
        }
        else
        {
        	if ($('#' + 'bp-members-pro-compent-plus-' + each_user_role_id).text() == '-')
        	{
        		$('#' + 'bp-members-pro-compent-plus-' + each_user_role_id).text('+');
        	}
        }
      });
   });
});