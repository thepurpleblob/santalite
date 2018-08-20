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

/**
 * CSS trickery
 */
$(function() {
    $('.limitbuttons button').addClass('btn-sm');
});
