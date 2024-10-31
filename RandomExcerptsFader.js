(function ($) {
    "use strict";
    $.fn.nextOrFirst = function (selector) {
        var next = this.next(selector);
        return (next.length) ? next : this.prevAll(selector).last();
    };

    if (!$.reFader) {
        $.reFader = {};
    }

    $.reFader.RandomExcerptsFader = function (el, fade, duration, options) {
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("reFader.RandomExcerptsFader", base);

        base.init = function () {
            base.options = $.extend({}, $.reFader.RandomExcerptsFader.defaultOptions, options);

            // For WordPress Plugin
            base.options.duration = ($('.duration', base.$el).length > 0 && $.reFader.RandomExcerptsFader.defaultOptions.duration === base.options.duration) ? $('.duration', base.$el).text() : base.options.duration;

            var tallest = 0;
            $('p', base.$el).each(function (index) {
                tallest = parseInt(($(this).height() > tallest) ? $(this).height() : tallest) + 10;
            }).css({'height': tallest + 'px'});
            $('p', base.$el).css({'display': 'none'});
            $('p:first-child', base.$el).css({'display': 'block'}).addClass('active');

            base.reFader();
        };

        base.reFader = function (paramaters) {
            var tallest = 0;
            base.interval = setInterval(function () {
                $('.active', base.$el).removeClass('active').fadeOut(base.options.fade, function () {
                    var nextSlide = $(this).nextOrFirst('p').addClass('active');
                    nextSlide.fadeIn(base.options.fade);
                });

            }, base.options.duration);
        };

        // Run initializer
        base.init();
    };

    $.reFader.RandomExcerptsFader.defaultOptions = {
        // defaults
        fade: 1000,
        duration: 5000
    };

    $.fn.RandomExcerptsFader = function (fade, options) {
        return this.each(function () {
            (new $.reFader.RandomExcerptsFader(this, options));
        });
    };
    $(window).load(function () {
        $('.RandomExcerpts').RandomExcerptsFader();
    });

}(jQuery));
