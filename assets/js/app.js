// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');

require('@popperjs/core');
const bootstrap = window.bootstrap = require('bootstrap');
require('@tabler/core');
const hljs = require('highlight.js');

(function() {
    hljs.initHighlighting();

    let packageDetails = Object.values(document.getElementsByClassName('package-details'));
    let packageLinks = Object.values(document.getElementsByClassName('package-link'));

    packageLinks.forEach(link => {
        link.addEventListener('click', event => {
            event.preventDefault();
            
            packageDetails.forEach(item => {
                item.style.display = 'none';
            })
            
            document.getElementById(link.getAttribute('data-package')).style.display = '';
        });
    })

    //
    // $('[data-confirmation]').confirmation({
    //     rootSelector: '[data-confirmation]',
    //     popout: true,
    //     singleton: true,
    //     btnOkLabel: 'Confirm',
    //     btnCancelLabel: 'Cancel!',
    //     btnOkClass: 'btn btn-xs btn-success',
    //     btnCancelClass: 'btn btn-xs btn-danger',
    //     title: 'Confirmation',
    //     content: function () {
    //         return $(this).data('confirmation');
    //     }
    // });
})();
