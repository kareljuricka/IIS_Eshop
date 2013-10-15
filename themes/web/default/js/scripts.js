function scrollToElement(selector, time, verticalOffset) {
    time = typeof(time) != 'undefined' ? time : 500;
    verticalOffset = typeof(verticalOffset) != 'undefined' ? verticalOffset : 0;
    element = $(selector);
    offset = element.offset();
    offsetTop = offset.top + verticalOffset - 30;
    $('html, body').animate({
        scrollTop: offsetTop
    }, time);
}

$(document).ready(function() {

$('a#link-about').click(function () {
    scrollToElement('#about');
});

$('a#link-gallery').click(function () {
    scrollToElement('#gallery');
});


$('a#link-skills').click(function () {
    scrollToElement('#skills');
});

$('a#link-contact').click(function () {
    scrollToElement('#contact');
});


$('a#web-top-link').click(function () {
    scrollToElement('#web-top');
});


});
