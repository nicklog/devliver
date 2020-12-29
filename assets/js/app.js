// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');

import jQuery from 'jquery';
import '@popperjs/core';
import 'bootstrap';
import 'bootstrap-confirmation2';
import 'admin-lte';
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';

jQuery(function ($) {
    hljs.initHighlighting();
    
    $('.package-link').on('click', function (e) {
        e.preventDefault();

        $('.package-details').hide();
        $('#' + $(this).data('package')).show();
    });

    $('[data-confirmation]').confirmation({
        rootSelector: '[data-confirmation]',
        popout: true,
        singleton: true,
        btnOkLabel: 'Confirm',
        btnCancelLabel: 'Cancel!',
        btnOkClass: 'btn btn-xs btn-success',
        btnCancelClass: 'btn btn-xs btn-danger',
        title: 'Confirmation',
        content: function () {
            return $(this).data('confirmation');
        }
    });
    
    $('[data-tooltip]').tooltip({
        container: 'body',
        trigger: 'hover',
        'title': function () {
            return $(this).data('tooltip');
        }
    });
});

