/**
 * Main Javascript Class
 *
 * Opens and closes search form etc.
 *
 * ====================================================================
 * @return {class}
 */

(($) => {
  // Controller
  const App = {};

  // Responsive
  const Responsive = {};

  /** --------------------------------------------------------------- */

  /**
   * Application
   */

  App.init = () => {
    $(App.domReady);
  };

  App.domReady = () => {
    Responsive.domReady();
  };

  /** --------------------------------------------------------------- */

  /**
   * Responsive Help
   */
  Responsive.domReady = () => {
    const $window = $(window);
    const $document = $(document);
    const $inner = $("#inner-wrap");

    let is_mobile = false;
    let mobile_modified = false;

    const viewport = () => {
      let e = window;
      let a = "inner";
      if (!("innerWidth" in window)) {
        a = "client";
        e = document.documentElement || document.body;
      }
      return { width: e[`${a}Width`], height: e[`${a}Height`] };
    };

    const check_is_mobile = () => {
      if (viewport().width <= 1024) {
        $("body").removeClass("is-desktop").addClass("is-mobile");
      } else {
        $("body").removeClass("is-mobile").addClass("is-desktop");
      }
      is_mobile = $("body").hasClass("is-mobile");
    };

    const render_layout = () => {
      if (is_mobile && $inner.height() < $window.height()) {
        $("#page").css(
          "min-height",
          $window.height() -
            ($("#mobile-header").height() + $("#colophon").height())
        );
      }

      if (is_mobile && !mobile_modified) {
        mobile_modified = true;
      } else if (!is_mobile && mobile_modified) {
        $document.trigger("menu-close.buddyboss");
      }
    };

    const do_render = () => {
      check_is_mobile();
      render_layout();
    };

    do_render();

    $window.on("load", () => {
      do_render();
    });

    let throttle;
    $window.on("resize", () => {
      clearTimeout(throttle);
      throttle = setTimeout(do_render, 150);
    });

    $window.on("load", () => {
      $("body").addClass("ccluk-page-loaded");
    });

    /**
     * Search
     */
    const $search_form = $("#header-search").find("form");

    $("#search-open").on("click", (e) => {
      e.preventDefault();
      $search_form.fadeIn();
      setTimeout(() => {
        $search_form.find("#s").trigger("focus");
      }, 301);
    });

    $document.on("click", (e) => {
      const container = $("#header-search");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $search_form.fadeOut();
      }
    });

    const search_width = () => {
      let buttons_width = 0;
      $("#header-search")
        .nextAll("div, a")
        .each(function () {
          buttons_width += $(this).width();
        });

      $search_form.width($(
        ".header-wrapper"
      ).width() - 320 - buttons_width);
    };

    search_width();
    $window.on("resize", search_width);

    /**
     * To Top Button
     */
    $(".to-top").on("click", (event) => {
      event.preventDefault();
      $("html, body").stop().animate({ scrollTop: "0px" }, 500);
    });

    /**
     * Mobile Navigation
     */
    const $body = $("body");
    const $page = $("#main-wrap");
    const $mobilePanel = $("#mobile-right-panel");
    const transitionEndNav = "transitionend webkitTransitionEnd otransitionend MSTransitionEnd";

    $(".right-btn").on("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      $mobilePanel.css({ opacity: 1 });

      $page.on(transitionEndNav, function () {
        if (!$body.hasClass("menu-visible-right")) {
          $mobilePanel.removeAttr("style");
          $page.off(transitionEndNav);
        }
      });

      $body.toggleClass("menu-visible-right");
    });

    // Close menu when clicking outside
    $document.on("click", (e) => {
      if ($body.hasClass("menu-visible-right") && !$(e.target).closest("#mobile-right-panel, .right-btn").length) {
        $body.removeClass("menu-visible-right");
      }
    });
  };

  App.init();
})(jQuery);