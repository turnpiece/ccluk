/**
 * Asset Optimization scripts.
 *
 * @package Hummingbird
 */

import Fetcher from '../utils/fetcher';
import {__, getLink} from '../utils/helpers';
import Row from '../minification/Row';
import RowsCollection from '../minification/RowsCollection';
import Scanner from '../minification/Scanner';

( function($) {
  'use strict';

  WPHB_Admin.minification = {

    module: 'minification',
    moduleNoticeId: 'wphb-notice-minification-advanced-settings-updated',
    $checkFilesButton: null,
    $checkFilesResultsContainer: null,
    checkURLSList: null,
    checkedURLS: 0,

    init: function() {
      const self = this;

      // Init files scanner.
      this.scanner = new Scanner(
          wphb.minification.get.totalSteps,
          wphb.minification.get.currentScanStep
      );
      this.scanner.onFinishStep = this.updateProgressBar;

      // Check files button.
      this.$checkFilesButton = $('#check-files');

      if (this.$checkFilesButton.length) {
        this.$checkFilesButton.click(function(e) {
          e.preventDefault();

          SUI.dialogs['check-files-modal'].show();

          $(this).attr('disabled', true);
          self.updateProgressBar(self.scanner.getProgress());
          self.scanner.scan();
        });
      }

      // Cancel scan button.
      $('body').on('click', '#cancel-minification-check', (e) => {
        e.preventDefault();
        this.updateProgressBar(0, true);
        this.scanner.cancel()
            .then(() => {
              window.location.href = getLink('minification');
            });
      });

      // Track changes done to minification files.
      $(':input.toggle-checkbox, :input[id*="wphb-minification-include"]')
          .on('change', function() {
            const row = $(this).closest('.wphb-border-row');
            const rowStatus = row.find('span.wphb-row-status-changed');
            $(this).toggleClass('changed');
            if (row.find('.changed').length !== 0) {
              rowStatus.removeClass('sui-hidden');
            } else {
              rowStatus.addClass('sui-hidden');
            }
            const changed = $('.wphb-minification-files').find('input.changed');
            if ( changed.length !== 0 ) {
              $('#wphb-publish-changes').removeClass('disabled');
            } else {
              $('#wphb-publish-changes').addClass('disabled');
            }
          });

      // Enable/disable bulk update button.
      $(':input.wphb-minification-file-selector, :input.wphb-minification-bulk-file-selector').on('change', function() {
        $(this).toggleClass('changed');
        const changed = $('.wphb-minification-files').find('input.changed');
        const bulkUpdateButton = $('#bulk-update');

        if (changed.length === 0) {
          bulkUpdateButton.addClass('button-notice disabled');
        } else {
          bulkUpdateButton.removeClass('button-notice disabled');
        }
      });

      // Show warning before switching to advanced view
      const switchButtons = $('.box-title-basic > a.wphb-switch-button');
      switchButtons.on('click', function(e) {
        e.preventDefault();

        SUI.dialogs['wphb-advanced-minification-modal'].show();
      });

      // Switch back to basic mode
      $('.box-title-advanced > a').on('click', function(e) {
        e.preventDefault();

        SUI.dialogs['wphb-basic-minification-modal'].show();
      });

      // Filter action button on Asset Optimization page
      $('#wphb-minification-filter-button').on('click', function(e) {
        e.preventDefault();
        $('.wphb-minification-filter').toggle('slow');
        $('#wphb-minification-filter-button').toggleClass('active');
      });

      // Discard changes button click
      $('.wphb-discard').on('click', function(e) {
        e.preventDefault();

        if (confirm(__('discardAlert'))) {
          location.reload();
        }
        return false;
      });

      // Enable discard button on any change
      $('.wphb-enqueued-files input').on('change', function() {
        $('.wphb-discard').attr('disabled', false);
      });

      // CDN checkbox update status
      const checkboxes = $('input[type=checkbox][name=use_cdn]');
      checkboxes.change(function() {
        const cdnValue = $(this).is(':checked');

        // Handle two CDN checkboxes on Asset Optimization page
        checkboxes.each(function() {
          this.checked = cdnValue;
        });

        // Update CDN status
        Fetcher.minification.toggleCDN(cdnValue)
            .then(() => {
              WPHB_Admin.notices.show(self.moduleNoticeId, true);
            });
      });

      // Exclude file buttons.
      const excludeButtons = $('.wphb-minification-exclude > :input.toggle-checkbox');
      excludeButtons.on('change', function () {
        const row = $(this).closest('.wphb-border-row');
        row.toggleClass('disabled');
        const label =  $("label[for='" + $(this).attr('id') + "']");
        if ( label.hasClass('fileIncluded') ) {
          label.attr('data-tooltip', wphb.strings.includeFile);
          label.removeClass('fileIncluded');
        } else {
          label.attr('data-tooltip', wphb.strings.excludeFile);
          label.addClass('fileIncluded');
        }
      });

      /**
       * Regenerate individual file.
       *
       * @since 1.9.2
       */
      $('.wphb-compressed .wphb-filename-extension').on( 'click', function() {
        const row = $(this).closest('.wphb-border-row');

        row.find('.fileinfo-group').removeClass('wphb-compressed');

        row.find('.wphb-row-status')
            .removeClass('sui-hidden wphb-row-status-changed')
            .addClass('wphb-row-status-queued sui-tooltip-constrained')
            .attr('data-tooltip', wphb.strings.queuedTooltip)
            .find('i').attr('class', 'sui-icon-loader sui-loading');

        Fetcher.minification.resetAsset( row.attr('data-filter') );
      });

      $('input[type=checkbox][name=debug_log]').change(function() {
        const enabled = $(this).is(':checked');
        Fetcher.minification.toggleLog(enabled)
            .then(() => {
              WPHB_Admin.notices.show(self.moduleNoticeId, true);
              if ( enabled ) {
                $('.wphb-logging-box').show();
              } else {
                $('.wphb-logging-box').hide();
              }
            });
      });

      /**
       * Save critical css file
       */
      $('#wphb-minification-tools-form').on('submit', function(e) {
        e.preventDefault();

        const spinner = $(this).find('.spinner');
        spinner.addClass('visible');

        Fetcher.minification.saveCriticalCss($(this).serialize())
            .then( ( response ) => {
              spinner.removeClass('visible');
              if ('undefined' !== typeof response && response.success) {
                WPHB_Admin.notices.show(self.moduleNoticeId, true, 'success', response.message);
              } else {
                WPHB_Admin.notices.show(self.moduleNoticeId, true, 'error', response.message);
              }
            });
      });

      /**
       * Parse custom asset dir input
       *
       * @since 1.9
       */
      const textField = document.getElementById('file_path');
      if ( null !== textField ) {
        textField.onchange = function(e) {
          e.preventDefault();
          Fetcher.minification.updateAssetPath($(this).val())
              .then(() => {
                WPHB_Admin.notices.show(self.moduleNoticeId, true, 'success');
              });
        };
      }

      /**
       * Asset optimization network settings page.
       *
       * @since 2.0.0
       */

      // Show/hide settings, based on checkbox value.
      $('#wphb-network-ao').on('click', function() {
        $('.sui-border-frame:first-of-type').toggleClass('sui-hidden');
      });

      // Handle settings select.
      $('#wphb-box-minification-network-settings').on('change', 'input[type=radio]', function(e) {
        const divs = document.querySelectorAll('input[name='+e.target.name+']');

        // Toggle logs frame.
        if ( 'log' === e.target.name ) {
          $('.wphb-logs-frame').toggle(e.target.value);
        }

        for (let i = 0; i < divs.length; ++i) {
          divs[i].parentNode.classList.remove('active');
        }

        e.target.parentNode.classList.add('active');
      });

      // Submit settings.
      $('#wphb-ao-network-settings').on('click', function(e) {
        e.preventDefault();

        const spinner = $('.sui-box-footer').find('.spinner');
        spinner.addClass('visible');

        const form = $('#ao-network-settings-form').serialize();
        Fetcher.minification.saveNetworkSettings( form )
            .then((response) => {
              spinner.removeClass('visible');
              if ('undefined' !== typeof response && response.success) {
                WPHB_Admin.notices.show(self.moduleNoticeId, true, 'success');
              } else {
                WPHB_Admin.notices.show(self.moduleNoticeId, true, 'error', wphb.strings.errorSettingsUpdate);
              }
            });
      });

      /**
       * Asset Optimization filters
       * @type {RowsCollection|*}
       */
      this.rowsCollection = new WPHB_Admin.minification.RowsCollection();

      const rows = $('.wphb-border-row');

      rows.each(function(index, row) {
        let _row;
        if ($(row).data('filter-secondary')) {
          _row = new WPHB_Admin.minification.Row(
              $(row), $(row).data('filter'),
              $(row).data('filter-secondary')
          );
        } else {
          _row = new WPHB_Admin.minification.Row($(row), $(row).data('filter'));
        }
        self.rowsCollection.push(_row);
      });

      // Filter search box
      const filterInput = $('#wphb-s');
      // Prevent enter submitting form to rescan files.
      filterInput.keydown(function(e) {
        if ( 13 === e.keyCode ) {
          event.preventDefault();
          return false;
        }
      });
      filterInput.keyup(function() {
        self.rowsCollection.addFilter($(this).val(), 'primary');
        self.rowsCollection.applyFilters();
      });

      // Filter dropdown
      $('#wphb-secondary-filter').change(function() {
        self.rowsCollection.addFilter($(this).val(), 'secondary');
        self.rowsCollection.applyFilters();
      });

      // Refresh rows on any filter change
      $('.filter-toggles').change(function() {
        const element = $(this);
        const what = element.data('toggles');
        const value = element.prop('checked');
        const visibleItems = self.rowsCollection.getVisibleItems();

        for (const i in visibleItems) {
          visibleItems[i].change(what, value);
        }
      });

      // Files selectors
      const filesList = $('input.wphb-minification-file-selector');
      filesList.on('click', function(){
        const $this = $(this);
        const element = self.rowsCollection.getItemById($this.data('type'), $this.data('handle'));
        if (!element) {
          return;
        }

        if ($this.is(':checked')) {
          element.select();
        } else {
          element.unSelect();
        }
      });

      /**
       * Handle select/deselect of all files of a certain type for
       * use on bulk update.
       *
       * @type {*|jQuery|HTMLElement}
       */
      const selectAll = $('.wphb-minification-bulk-file-selector');
      selectAll.click(function() {
        const $this = $(this);
        const items = self.rowsCollection.getItemsByDataType($this.attr('data-type'));
        for (const i in items) {
          if (items.hasOwnProperty(i)) {
            if ($this.is(':checked')) {
              items[i].select();
            } else {
              items[i].unSelect();
            }
          }
        }
      });

      /* Show details of minification row on mobile devices */
      $('body').on('click', '.wphb-border-row', function() {
        if (window.innerWidth < 783) {
          $(this).find('.wphb-minification-row-details').toggle();
          $(this).find('.fileinfo-group').toggleClass('opened');
        }
      });

      /**
       * Catch window resize and revert styles for responsive dive
       * 1/4 of a second should be enough to trigger during device
       * rotations (from portrait to landscape mode)
       *
       * @type {debounced}
       */
      const minificationResizeRows = _.debounce(function() {
        if (window.innerWidth >= 783) {
          $('.wphb-minification-row-details').css('display', 'flex');
        } else {
          $('.wphb-minification-row-details').css('display', 'none');
        }
      }, 250);

      window.addEventListener('resize', minificationResizeRows);

      return this;
    },

    updateProgressBar: function(progress, cancel = false) {
      if (progress > 100) {
        progress = 100;
      }
      // Update progress bar
      $('.sui-progress-block .sui-progress-text span').text(progress + '%');
      $('.sui-progress-block .sui-progress-bar span').width(progress + '%');
      if (progress >= 90) {
        $('.sui-progress-state .sui-progress-state-text').text('Finalizing...');
      }
      if (cancel) {
        $('.sui-progress-state .sui-progress-state-text').text('Cancelling...');
      }
    },

    /**
     * Switch from advanced to basic view.
     * Called from switch view modal.
     *
     * @param {string} mode
     */
    switchView: function(mode) {
      Fetcher.minification
          .toggleView(mode)
          .then(() => {
            window.location.href = getLink('minification');
          });
    },

    /**
     * Go to the Asset Optimization files page.
     *
     * @since 1.9.2
     */
    goToSettings: function() {
      SUI.dialogs['wphb-assets-modal'].hide();

      Fetcher.minification
          .toggleCDN($('input#enable_cdn').is(':checked'))
          .then(() => {
            window.location.href = getLink('minification');
          });
    },

  }; // End WPHB_Admin.minification

  WPHB_Admin.minification.Row = Row;
  WPHB_Admin.minification.RowsCollection = RowsCollection;
}(jQuery));
