var cuiteur = new Object();
cuiteur.user = null;
cuiteur.tendances = null;
cuiteur.suggestions = null;
cuiteur.notyerror = null;
cuiteur.isIdle = null;
cuiteur.maxBR = 3;

cuiteur.preloadOverImg = function (selector, str_over_mask) {
    str_over_mask = typeof str_over_mask !== 'undefined' ? str_over_mask : "_over";
    selector = typeof selector !== 'undefined' ? selector : ".hover_btn";
    if (!$(selector).length) {
        return;
    }
    $(selector).each(
        function () {
            var src = $(this).css('background-image');
            var bg_url = src.match(/^url\(['"]?(.+)["']?\)$/);
            src = bg_url ? bg_url[1] : "";
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

cuiteur.updateInfoUser = function () {
    if (cuiteur.isIdle) {
        return;
    }
    //si pas de bloc
    if (!($("#infoUser").length)) {
        return false;
    }

    $.ajax({
        type: "GET",
        url: "../cache/session/" + uid + ".json",
        success: function (msg) {
            try {
                //verfi si le msg est deja convert en json
                cuiteur.user = msg['usPseudo'] ? msg : $.parseJSON(msg);
            }
            catch (err) {
                console.log(err);
            }

            //mise a jour de l'html
            var part = $("#infoUser");
            $("#usImg", part).attr('src', cuiteur.user['usImg']).animate({
                opacity: 1
            }, 1200);
            $("#usNbrBlabla", part).text(cuiteur.user['usNbrBlabla'] + (cuiteur.user['usNbrBlabla'] > 1 ? " blablas" : " blabla"));
            $("#usNbrAbonnement", part).text(cuiteur.user['usNbrAbonnement'] + (cuiteur.user['usNbrAbonnement'] > 1 ? " abonnements" : " abonnement"));
            $("#usNbrAbonne", part).text(cuiteur.user['usNbrAbonne'] + (cuiteur.user['usNbrAbonne'] > 1 ? " abonnés" : " abonné"));
        }
    });
}

cuiteur.updateTendances = function () {
    if (cuiteur.isIdle) {
        return;
    }
    //si pas de bloc
    if (!($("#tendances").length)) {
        return false;
    }
    $.ajax({
        type: "GET",
        url: "../cache/tendances.json",
        success: function (msg) {
            try {
                //verfi si le msg est deja convert en json
                cuiteur.tendances = msg['year'] ? msg : $.parseJSON(msg);
            }
            catch (err) {
                console.log(err);
            }
            //on met a plat le tableau
            var tmp = [];
            tmp = _.flatten(cuiteur.tendances);
            tmp = _.map(tmp, _.keys);//on ne prend que les cles
            tmp = _.flatten(tmp, true);//comme _.key retourne un tableau on le met a plat
            tmp = _.uniq(tmp);
            /*
             for (var attr in cuiteur.tendances) {
             if (attr && attr.length && attr !== 'flat') {
             for (var item in cuiteur.tendances[attr]) {
             for (var tend_name in cuiteur.tendances[attr][item]) {
             tmp.push(tend_name);
             }
             }
             }
             }*/
            cuiteur.tendances.flat = tmp;

            //mise a jour de l'html
            var part = $("#tendances");
            cuiteur.tendances['nbr_affichee'] = $(".tendance").length;
            tmp = 0;
            $(".tendance", part).each(function () {
                if (tmp < cuiteur.tendances['nbr_affichee'] && tmp < cuiteur.tendances.flat.length) {
                    var curr = cuiteur.tendances.flat[tmp];
                    $(this).text('#' + curr).attr('href', 'tendances.php?id=' + curr);
                    ++tmp;
                } else {
                    $(this).hide(0);
                }
            });
            part.css('opacity', 1);

        }
    });
}


cuiteur.updateSuggestions = function () {
    if (cuiteur.isIdle) {
        return;
    }
    //si pas de bloc
    if (!($("#suggestions").length)) {
        return false;
    }

    $.ajax({
        type: "GET",
        url: "../cache/" + "suggest/" + uid + ".json",
        success: function (msg) {
            try {
                //verfi si le msg est deja convert en json
                cuiteur.suggestions = (_.isArray(msg)) ? msg : $.parseJSON(msg);
            }
            catch (err) {
                console.log(err);
            }

            //mise a jour de l'html
            var part = $("#suggestions");

            cuiteur.suggestions['nbr_affichee'] = $(".suggestion").length;
            var i = 0;
            $(".suggestion", part).each(function () {
                if (i < cuiteur.suggestions['nbr_affichee'] && i < cuiteur.suggestions.length) {
                    var sugg = cuiteur.suggestions[i];
                    $("img", $(this)).attr('src', sugg['usImg']);
                    $("a", $(this)).attr('href', 'utilisateur.php?user=' + sugg['usID']).text(sugg['usPseudo']);
                    ++i;
                } else {
                    $(this).hide(0);
                }

            });
        }
    });
}


/**
 * Affecte les cles clavier du documents.
 */
cuiteur.setKeyBinding = function () {
    //Note : see afterClose Noty event
    $(document).bind('keydown', 'esc', function (event) {
        if (cuiteur.notyerror) {
            event.preventDefault();
            cuiteur.notyerror.close();
            cuiteur.notyerror = null; // => Close without recall.
            return;
        }
    });
    $(document).bind('keydown', 'return', function (event) {
        if (cuiteur.notyerror) {
            event.preventDefault();
            cuiteur.notyerror.close();
            return;
        }


    });
    if (!$("#txtMessage").length) {
        return;
    }
    $("#txtMessage").bind('keydown', 'return', function (event) {
        event.preventDefault();
        $("#btnPublier").trigger('click');
    });
}


cuiteur.appendCuit = function(cuithtml,prepend){
    if(!_.isString(cuithtml)){
        return false;
    }
    var $ul = $('ul#bcMessages');
    var cuit = null;
    if(prepend){
        //$cuithtml.prependTo($ul).effect('hide',{},0);
        $ul.prepend(cuithtml);
        cuit = $("li.cuitBlabla:first",$ul);//le message qui vien d'etre ajoute
    }else{
        $ul.append(cuithtml);
        cuit = $("li.cuitBlabla:last",$ul);//le message qui vien d'etre ajoute
    }
    cuit.hide(0);//on le cache
    cuiteur.formatCuit();//formatage
    cuiteur.sortCuit();//tri
    cuit.fadeIn(450);//reaffichage

}

/**
 * On prend la main sur la form publication (le textarea pour cuité).
 * Ceci afin d'utiliser ajax
 */
cuiteur.hookTextArea = function () {
    if (!$("#frmPublier").length) {//exit la fonction si ca existe pas dans la page
        return;
    }

    /* submit handler*/
    $("#frmPublier").submit(function (event) {
        event.preventDefault(); //ne pas envoyer le formulaire avec la methode par defaut.

        $("#txtMessage").blur();

        $("#txtMessage,#btnPublier").prop("disabled", false); // Overwrite b4 read.

        var text = $.trim($("textarea#txtMessage").val()); //read, trim and set
        $("textarea#txtMessage").val(text);


        if (text.length === 0) {
            $("#txtMessage").focus();
            return;
        }
        // get values
        var values = $(this).serialize();
        console.log("datasend : " + values);
        //Disable edit mode
        $("#txtMessage,#btnPublier").prop("disabled", true);
        // Send data
        $.ajax({
            url: "../php/post.php",
            type: "post",
            data: values,
            success: function (msg) {
                try {
                    //verfi si le msg est deja convert en json
                    msg = msg['msg'] ? msg : $.parseJSON(msg);
                }
                catch (err) {
                    console.log(err);
                }
                $("textarea#txtMessage").val('');
                //reenable edit mode
                $("#txtMessage,#btnPublier").prop("disabled", false);
                $("#txtMessage").focus();
                //TODO
                console.log("recieved : ")
                console.log(msg)
                cuiteur.appendCuit(msg['msg'], true);
                _.delay(cuiteur.updateInfoUser, 500);//temps de latence afin de mettre a jour correctement
            },
            error: function () {
                cuiteur.notyerror = noty({
                    text: "Erreur lors de l'envoie. click pour relancé...",
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
                            $("#txtMessage,#btnPublier").prop("disabled", false);
                            $("#txtMessage").focus();
                            if (cuiteur.notyerror) {
                                $("#btnPublier").trigger('click');
                            }
                            cuiteur.notyerror = null;
                        }
                    }
                });
            }
        });
    });
}


cuiteur.call_cron = function () {
    $.ajax({
        type: "GET",
        url: "../php/cron.php",
        success: function (msg) {
            cuiteur.updateInfoUser();
            cuiteur.updateTendances();
        }});
}

cuiteur.initIdleTimer = function () {
    if (cuiteur.isIdle !== null) { //deja initialiser
        return;
    }
    cuiteur.isIdle = false;
    var timeoutTime = 5 * 60 * 1000; // 5 minutes
    var handler = function () {
        cuiteur.isIdle = true;
    }
    var timeoutTimer = setTimeout(handler, timeoutTime);
    $(document).bind('mousedown keydown mousemove', function (event) {
        cuiteur.isIdle = false;
        clearTimeout(timeoutTimer);
        timeoutTimer = setTimeout(handler, timeoutTime);
    });
}


cuiteur.timeAgo = function (selector) {
    var $timeago = $('time.timeago',selector);
    if ($timeago.length) {
        $timeago.timeago();
    }
    return $timeago;
}


cuiteur.sortCuit = function () {
    var cuits = $("li.cuitBlabla");
    if (!cuits.length) {
        return false;
    }

    var items = cuits.get();
    items.sort(function (a, b) {

        var dateA = $('time.timeago', $(a)).attr('datetime');
        dateA = new Date(dateA);//conversion en obj date
        var dateB = $('time.timeago', $(b)).attr('datetime');
        dateB = new Date(dateB);//conversion en obj date

        if (dateA < dateB) return 1;
        if (dateA > dateB) return -1;
        return 0;
    });
    //application des changements
    var ul = $('ul#bcMessages');
    $.each(items, function (i, li) {
        ul.append(li);
    });


}

cuiteur.preventErasing = function (sucessfct, cancelfct) {
    $("#dialog-confirm").dialog({
        resizable: true,
        height: 240,
        width: 390,

        modal: true,
        buttons: {
            "Poursuivre": function(){
                if(_.isFunction(sucessfct)){
                    sucessfct();
                }
                $(this).dialog("close");
            },
            "Annuler": function () {
                if(_.isFunction(cancelfct)){
                    cancelfct();
                }
                $(this).dialog("close");
            }
        }
    });
}

cuiteur.formatCuit = function () {
    var cuits = $("li.cuitBlabla");
    if (!cuits.length) {
        return false;
    }
    cuits.each(function () {
            var $this = $(this);
            if ($this.attr('cuitFormated')) {//deja formate
                return;
            }
            $this.attr('cuitFormated', "1");
            //timeago
            cuiteur.timeAgo($this);

            var cuit = $("div.textcuit>p:first", $this);

            for (var i = 0; i < cuiteur.maxBR; ++i) { //on limite le nombre de BR
                cuit.html(cuit.html().replace(/\n/, "<br>"));//met des br a la place des \n
            }
            cuit.html($.trim(cuit.html().replace(/[\t\r\n\f]/g, " ")));//enleve tous les caracteres bizzard

            //Traitement des TAGS
            var tags = cuit.html().match(/#[\w]{3,50}/g);
            tags = _.uniq(tags);
            _.each(tags, function (tag) {
                console.log(tag);
                var tag_sans_diese = tag.substring(1);
                var regex = new RegExp(tag, 'g');
                cuit.html(cuit.html().replace(regex, '#<a href="tendances.php?' + tag_sans_diese + '">' + tag_sans_diese + '</a>'));
            });

            //Hook "recuiter"
            var recuit_btn = $("a.recuit", $this);
            recuit_btn.click(function (event) {
                //TODO
            });
            //Hook "repondre"
            var repondre_btn = $("a.repondre", $this);
            repondre_btn.click(function (event) {
                event.preventDefault();
                var txtMess_curr = $("textarea#txtMessage").val();
                var toset = "@" + $.trim($(".userName", $this).text()) + ' ';
                var replacefct = function () {
                    $("textarea#txtMessage").focus().val("").val(toset);
                }//met le @Taget et place le curseur a la fin
                if (txtMess_curr.length > 4 && txtMess_curr !== toset) {
                    cuiteur.preventErasing(replacefct);
                } else {
                    replacefct();
                }

                return true;

            });
        }
    );
}

/**
 *
 */
cuiteur.init = function () {
    cuiteur.preloadOverImg();
    cuiteur.setKeyBinding();
    cuiteur.hookTextArea();
    cuiteur.updateInfoUser();
    cuiteur.updateTendances();
    cuiteur.updateSuggestions();
    cuiteur.initIdleTimer();
    //cuiteur.timeAgo();
    cuiteur.formatCuit();

    setInterval("cuiteur.call_cron()", 45 * 1000);//toutes les 45 secs
}