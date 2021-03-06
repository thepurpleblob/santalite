/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Javascript bits and bobs.
 */

/**
 * Random header image
 */
$(function() {
    var www = $('#data-www').data('www');

    // Random number 1..3
    var random = 1 + Math.floor(Math.random() * 3);
    var css = 'linear-gradient( rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ), ';
    var url = www + '/src/asset/image/header' + random + '.jpg';
    $('#bannerimg').css('background-image', css + 'url("' + url + '")');
});

$(function() {

    /**
     * CSS trickery
     */
    $('.limitbuttons button').addClass('btn-sm');


    /**
     * stop validation when cancel is pressed
     */
    $('.cancelbutton').on("click", function() {
    console.log('cancel clicked');
        $('form').attr("novalidate", "novalidate");
    });

    // Enable data tables
    $('#purchasesTable').DataTable({
        "lengthMenu": [25, 50, 75, 100],
	columDefs: [{
            type: 'num-string',
	    targets: 0
	}],
        "order": [[0, 'desc']]
    });
});

