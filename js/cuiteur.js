var ajax = new Object();
//TODO 1 step
ajax.user = null;
ajax.notyerror = null;
ajax.tendances = null;
ajax.suggestions = null;
ajax.newmessages = null; //Object qui contient tous les messages nouvellement chargés
ajax.previoususer = null; //stocke l'utilisateur du dernier message chargé
ajax.isinit = false;
ajax.nbrcuit = 0;


function getIsoDateTime(unixtime) {
    var now = new Date(unixtime * 1000);
    return $.datepicker.formatDate('yy-mm-dd', now);
}

function getUnixTimestamp() {
    return Math.round((new Date()).getTime() / 1000);
}

function preloadOverImg(selector, str_over_mask) {
    str_over_mask = typeof str_over_mask !== 'undefined' ? str_over_mask : "_over";
    selector = typeof selector !== 'undefined' ? selector : "#go,img.crtl";
    $(selector).each(
        function (index) {
            var src = $(this).attr('src');
            var filename = src.split(".");
            if (filename.length < 2) {
                return false;
            }
            filename = filename[filename.length - 2];
            var newfilename = filename + str_over_mask;
            $('<img/>')[0].src = src.replace(filename, newfilename);
        }
    );
}

/*
 */
function setOverImg(selector, str_over_mask) {
    //def args
    str_over_mask = typeof str_over_mask !== 'undefined' ? str_over_mask : "_over";
    selector = typeof selector !== 'undefined' ? selector : "#go,img.crtl";

    $(selector).each(
        function (index) {
            var src = $(this).attr('src');
            $(this).hover(
                function () {
                    var filename = src.split(".");
                    filename = filename[filename.length - 2];
                    var newfilename = filename + str_over_mask;
                    $(this).attr('src', src.replace(filename, newfilename));
                },
                function () {
                    $(this).attr('src', src.replace(str_over_mask, ""));
                }
            );
        }
    );
}


function setKeyBinding() {
    //global bind :

    //Note : see afterClose Noty event
    $(document).bind('keydown', 'esc', function (event) {
        if (ajax.notyerror) {
            event.preventDefault();
            ajax.notyerror.close();
            ajax.notyerror = null; // => Close without recall.
            return;
        }
    });
    $(document).bind('keydown', 'return', function (event) {
        if (ajax.notyerror) {
            event.preventDefault();
            ajax.notyerror.close();
            return;
        }


    });

    //specifiq bind :
    /*$("#cuit").bind('keydown', 'shift+return', function(event){
     //Nothing to do for now
     });*/

    $("#cuit").bind('keydown', 'return', function (event) {
        event.preventDefault();
        $("#go").trigger('click');
    });
}

ajax.prepareTextArea = function () {
    /* submit handler*/
    $("#post").submit(function (event) {
        $("#cuit").blur();

        $("#cuit,#go").prop("disabled", false); // Overwrite b4 read.

        var text = $.trim($("textarea#cuit").val()); //read, trim and set
        $("textarea#cuit").val(text);
        console.log("datasend : " + text);

        event.preventDefault();//ne pas envoie formulaire.

        if (text.length === 0) {
            $("#cuit").focus();
            return;
        }


        // get values
        var values = $(this).serialize();


        //Disable edit mode
        $("#cuit,#go").prop("disabled", true);
        // Send data
        $.ajax({
            url: "../php/post.php",
            type: "post",
            data: values,
            success: function (msg) {
                alert(msg);
                $("textarea#cuit").val('');
                //reenable edit mode
                $("#cuit,#go").prop("disabled", false);
                $("#cuit").focus();
                ajax.loadSuggestions();
                //ajax.updateHTML();
                //TODO
            },
            error: function () {
                ajax.notyerror = noty({
                    text: "Erreur lors de l'envoie. click pour retry...",
                    type: 'error',
                    layout: "top",
                    callback: {
                        onShow: function () {
                        },
                        afterShow: function () {
                        },
                        onClose: function () {
                        },
                        afterClose: function () {
                            //reenable edit mode
                            $("#cuit,#go").prop("disabled", false);
                            $("#cuit").focus();
                            if (ajax.notyerror) {
                                $("#go").trigger('click');
                            }
                            ajax.notyerror = null;
                        },
                    },
                });
            }
        });
    });
}

ajax._loadBaseFct = function (name, url, data, update, successfct, errorfct) { //TODO error
    data = typeof data !== 'undefined' ? data : "";
    var type = typeof name;
    if (type !== "string") {
        return 0;
    }
    $.ajax({
        type: "GET",
        url: url,
        data: data,
        success: function (msg) {
            if (msg && msg[name]) {
                ajax[name] = msg[name];
                if (update !== false) {
                    ajax.updateHTML();
                }
                //callback
                type = typeof successfct;
                if (type === "function") {
                    successfct();
                    return;
                }
                if (type === "string") {
                    eval(successfct);
                    return;
                }
            }
        }
    });
}

ajax.loadCurrUser = function (update) {
    this._loadBaseFct("user", "../test/userinfo.json", null, update);
}

ajax.loadTendances = function (update) {
    this._loadBaseFct("tendances", "../test/tendances.json", null, update);
}

ajax.loadSuggestions = function (update) {
    this._loadBaseFct("suggestions", "../test/suggestions.json", null, update);
}

ajax.updateHTML = function () {
    if (this.user === null) {
        return false;
    }
    // -------------- Update User info --------------
    var cu = this.user; //alias


    //auto
    for (attr in cu) { // iterate on attribute
        $("p#" + attr).text(cu[attr]);
    }
    //fix
    $("#countblabla").text($("#countblabla").text() + " blabla" + ((cu.countblabla > 1) ? "s" : "" ));
    $("#countabonnement").text($("#countabonnement").text() + " abonnement" + ((cu.countabonnement > 1) ? "s" : "" ));
    $("#userimg").attr('src', cu.userimg);

    //links
    $("#linkblabla").attr("href", "../php/usersblabla.php?userid=" + cu.id);
    $("#abolink").attr("href", "../php/abonnement.php?userid=" + cu.id);

    // -------------- Update tendances --------------
    if (this.tendances === null) {
        return false;
    }
    var tend = this.tendances;
    //auto
    for (attr in tend) {
        $("p#" + attr + "name").text("#" + tend[attr].name); //text update
        $("a#" + attr + "link").attr("href", "../php/tendance.php?id=" + tend[attr].id); // link update
    }

    // -------------- Update suggestions --------------
    if (this.suggestions === null) {
        return false;
    }
    var sugg = this.suggestions;
    //auto
    for (attr in sugg) {
        $("p#" + attr).text("#" + sugg[attr].username); //text update
        $("a#" + attr + "link").attr("href", "../php/user.php?id=" + sugg[attr].id); // link update
    }


    return true;


}

var debug = null;

ajax.htmlmodCuit = function (addtop) {
    addtop = typeof addtop !== 'undefined' ? addtop : true;
    if (ajax.newmessages === null) {
        return false;
    }
    this.nbrcuit = $(".section .imgUser").length;

    var section = $(".section:first");
    var message = null;
    var cumuldelay = 0;
    var lastadd = null;
    var user = null;
    for (message_name in ajax.newmessages) {
        message = ajax.newmessages[message_name];
        user = message.user;

        if (user["previous"] === 1) {// On peut envoyer "previous:1" pour faire ref au precedents utilisateur
            user = ajax.previoususer;
        }
        else if (user["self"] === 1) {// On peut envoyer "self:1" pour faire ref a soit meme
            user = ajax.user;
            ajax.previoususer = ajax.user;
        }
        else {//stocke l'user
            ajax.previoususer = user;
        }


        if (!addtop) {
            $("ul", section).append(cuitdata.cuithtml);
            lastadd = $("li:last", section); //recherche dans les results d'une precedante recherche

        } else {
            $("ul", section).prepend(cuitdata.cuithtml);
            lastadd = $("li:first", section);
        }
        lastadd.hide(0);
        lastadd.delay(cumuldelay).fadeIn("slow");
        cumuldelay += 500; //500 ms avant l'apparition de la box suivante
        ++this.nbrcuit;

        //update the lastadd
        //message id
        lastadd.attr('id', "cuitid" + message.id);
        //username
        $(".userName", lastadd).text(user.username);
        //usernamelink
        $("a.userlink", lastadd).attr('href', '../php/user.php?id=' + user.id);
        //icon
        $("img.imgUser", lastadd).attr('src', user.userimg);
        //texte cuité
        $(".textcuit p", lastadd).text(message.textcuit); // TODO end Ligne
        //time
        $('time.timeago', lastadd).attr('datetime', getIsoDateTime(message.time)).timeago();

    }


    ajax.newmessages = null; //clean

    return true;


}

ajax.loadNewCuit = function (argument) {
    this._loadBaseFct('newmessages', '../test/newmessages.json', null, false, function () {
        ajax.htmlmodCuit()
    })
}