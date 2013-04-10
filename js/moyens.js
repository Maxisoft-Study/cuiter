function scrollToAnchor(aid) {
    var aTag = $("a[id='" + aid + "']");
    if (aTag.length) {
        $('html,body').animate({scrollTop: aTag.offset().top - $('header').height() });
    }
}