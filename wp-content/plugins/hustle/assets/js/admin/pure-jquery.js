(function( $, doc ) {
	"use strict";

	$(document).on("click", '.wpoi-listing-wrap header.can-open .toggle, .wpoi-listing-wrap header.can-open .toggle-label', function(e){
		e.stopPropagation();
	});


	$(".accordion header .optin-delete-optin, .accordion header .edit-optin, .wpoi-optin-details tr .button-edit").hide().css({
		transition : 'none'
	});

	$(document).on({
		mouseenter: function () {
			var $this = $(this);
			$this.find(".optin-delete-optin, .edit-optin").stop().fadeIn("fast");
		},
		mouseleave: function () {
			var $this = $(this);
			$this.find(".toggle-checkbox").removeProp("disabled");
			$this.find(".edit-optin").removeProp("disabled");
			$this.removeClass("disabled");
			$this.find(".optin-delete-optin, .edit-optin, .delete-optin-confirmation").stop().fadeOut("fast");
		}
	}, ".accordion header");

	$(document).on({
		mouseenter: function () {
			var $this = $(this);
			$this.find(".button-edit").stop().fadeIn("fast");
		},
		mouseleave: function () {
			var $this = $(this);
			$this.find(".button-edit").stop().fadeOut("fast");
		}
	}, ".wpoi-optin-details tr");

	$(document).on("click", ".wpoi-tabs-menu a", function(event){
		event.preventDefault();
		var tab = $(this).attr("tab");
		Optin.router.navigate(tab, true);
	});

	$(document).on("click", ".edit-optin", function(event){
		event.stopPropagation();
		event.preventDefault();
		window.location.href = $(this).attr("href");
	});

	$(document).on("click", ".wpoi-type-edit-button", function(event){
		event.preventDefault();
		var optin_id = $(this).data("id");
		var optin_type = $(this).data("type");
		window.location.href = "admin.php?page=inc_optin&optin=" + optin_id + "#display/" + optin_type;
	});

	/**
	 * Make "for" attribute work on tags that don't support "for" by default
	 *
	 */
	$(document).on("click", '*[for]', function(e){
		var $this = $(this),
			_for = $this.attr( 'for'),
			$for = $("#" + _for);

		if( $this.is("label") || !$for.length ) return;

		$for.trigger("change");
		$for.trigger("click");
	});

	$("#wpoi-complete-message").fadeIn();

	$(document).on("click", '#wpoi-complete-message .next-button button', function(e){
		$("#wpoi-complete-message").fadeOut();
	});

	$(document).on("click", ".wpoi-listing-page .wpoi-listing-wrap header.can-open", function(e){
		$(this).find(".open").trigger("click");
	});

	/**
	 * On click of arrow of any optin in the listing page
	 *
	 */
	$(document).on("click", ".wpoi-listing-page .wpoi-listing-wrap .can-open .open", function(e){
		e.stopPropagation();
		var $this = $(this),
			$panel = $this.closest(".wpoi-listing-wrap"),
			$section = $panel.find("section"),
			$others = $(".wpoi-listing-wrap").not( $panel ),
			$other_sections = $(".wpoi-listing-wrap section").not( $section );

		$other_sections.slideUp(300, function(){
			$other_sections.removeClass("open");
		});
		$others.find(".dev-icon").removeClass("dev-icon-caret_up").addClass("dev-icon-caret_down");

		$section.slideToggle(300, function(){
			$panel.toggleClass("open");
			$panel.find(".dev-icon").toggleClass( "dev-icon-caret_up dev-icon-caret_down" );
		});

	});



	Optin.decorate_number_inputs = function( elem ){
		var $items =  elem && elem.$el ? elem.$el.find( '.wph-input--number input' ) : $('.wph-input--number input'),
			tpl = Hustle.create_template('<div class="wph-nbr--nav"><div class="wph-nbr--button wph-nbr--up {{disabled}}">+</div><div class="wph-nbr--button wph-nbr--down {{disabled}}">-</div></div>')
		;
		$items.each(function(){
		   var $this = $(this),
			   disabled_class = $this.is(":disabled") ? "disabled" : "";

			if( !$this.siblings( ".wph-nbr--nav").length ) // Add + and - buttons only if it's not already added
				$this.after(tpl( {disabled: disabled_class } ));

		});

	};

	Hustle.Events.on("view.rendered", Optin.decorate_number_inputs);

	// Listen to number input + and - click events
	(function (){
		$(document).on( "click", '.wph-nbr--up:not(.disabled)', function(e){
			var $this = $(this),
				$wrap = $this.closest( ".wph-input--number"),
				$input = $wrap.find( "input"),
				oldValue = parseFloat( $input.val() ),
				min = $input.attr('min'),
				max = $input.attr('max'),
				newVal;

			if (oldValue >= max){
				newVal = oldValue;
			} else {
				newVal = oldValue + 1;
			}

			if( newVal !== oldValue ){
				$input.val(newVal)
					.trigger('change');
			}
		});

		$(document).on( "click", '.wph-nbr--down:not(.disabled)', function(e){
			var $this = $(this),
				$wrap = $this.closest( ".wph-input--number"),
				$input = $wrap.find( "input"),
				oldValue = parseFloat( $input.val() ),
				min = $input.attr('min'),
				max = $input.attr('max'),
				newVal;


			if (oldValue <= min){
				newVal = oldValue;
			} else {
				newVal = oldValue - 1;
			}

			if( newVal !== oldValue ){
				$input.val(newVal)
					.trigger('change');
			}
		});
   }());

   // Sticky eye icon
   (function (){
	   function sticky_relocate(){
		   var window_top = $(window).scrollTop();
		   var div_top = $(".wph-sticky--anchor");

		   if ( ! div_top.length ) return;

			div_top = div_top.offset().top;
		   if (window_top > div_top) {
			   $(".wph-preview--eye").addClass("wph-sticky--element");
			   $(".wph-sticky--anchor").height($(".wph-preview--eye").outerHeight());
		   } else {
			   $(".wph-preview--eye").removeClass("wph-sticky--element");
			   $(".wph-sticky--anchor").height(0);
		   }
	   }
	   $(function(){
		   $(window).scroll(sticky_relocate);
		   sticky_relocate();
	   });
   }());

}( jQuery, document ));
