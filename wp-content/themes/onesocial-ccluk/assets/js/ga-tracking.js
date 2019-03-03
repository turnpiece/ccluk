jQuery(document).ready(function($){
   
   $('#mc-embedded-subscribe-form').find('#mc-embedded-subscribe').on('click', function() {
        ga(
            'send',
            'event',
            'Signups',
            'Newsletter Subscribe',
            'Clicked'
        );
   })

   $('#site-navigation').find('.nav-menu > .menu-item').find('a').on('click', function() {
        ga(
            'send',
            'event',
            'Main Menu',
            $(this).text(),
            'Clicked'
        );
   })
});
