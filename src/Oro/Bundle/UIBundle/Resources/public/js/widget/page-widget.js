define(['underscore', 'backbone', 'oroui/js/mediator', 'oro/block-widget'
], function(_, Backbone, mediator, BlockWidget) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/page-widget
     * @class   oro.PageWidget
     * @extends oro.BlockWidget
     */
    return BlockWidget.extend({
        options: _.extend({}, BlockWidget.prototype.options, {
            type: 'page',
            contentContainer: '.layout-content',
            template: _.template('<div>' +
                '<div class="container-fluid page-title">' +
                    '<div class="navigation navbar-extra navbar-extra-right">' +
                        '<div class="row">' +
                            '<div class="pull-left">' +
                                '<div class="clearfix">' +
                                    '<div class="page-title__path pull-left">' +
                                        '<div class="clearfix">' +
                                            '<div class="pull-left">' +
                                                '<h1 class="page-title__entity-title widget-title"><%- title %></h1>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +

                            '<div class="pull-right title-buttons-container widget-actions-container"></div>' +
                        '</div>' +
                    '</div>' +
                    '</div>' +
                '<div class="scrollable-container layout-content <%= contentClasses.join(\' \') %>"></div>' +
            '</div>'),
            replacementEl: null
        }),

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$replacementEl = $(this.options.replacementEl);
            this.options.container = this.$replacementEl.parent();
            this.on('adoptedFormResetClick', this.remove);

            BlockWidget.prototype.initialize.apply(this, arguments);
        },

        remove: function() {
            BlockWidget.prototype.remove.apply(this);

            var latestShownPageWidget = $('.page-widget').last();
            latestShownPageWidget.show();
            if (!latestShownPageWidget.length) {
                this.$replacementEl.show();
            }
            mediator.trigger('layout:adjustHeight');
        },

        _show: function() {
            var latestShownPageWidget = $('.page-widget').last();
            latestShownPageWidget.hide();
            if (!latestShownPageWidget.length) {
                this.$replacementEl.hide();
            }
            this.widget.addClass('page-widget');

            BlockWidget.prototype._show.apply(this);
            this.getActionsElement().find('button').wrap('<div class="btn-group"/>');

            mediator.trigger('layout:adjustHeight');
        }
    });
});
