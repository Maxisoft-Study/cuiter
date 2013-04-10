//Some functions.

function replaceWordsWithHTMLLink(words, link, text) {

    for (var i = words.length - 1; i >= 0; i--) {
        var exp = words[i];
        text = text.replace(exp, "<a href='" + link + "'>" + exp + "</a>");
    }
    ;
    return text;
}