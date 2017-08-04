/**
 * Created by svrg on 13/07/17.
 */
$(function() {

    $("#elem").autocomplete({
        source: "../widgets/autocomplete/autocomplete.php",
        minLength: 2,
        select: function(event, ui) {
            var url = ui.item.id;
            if(url !== '#') {
                location.href = '../form_elem.php?kod_elem=' + url;
            }
        },

        html: true, // optional (jquery.ui.autocomplete.html.js required)

        // optional (if other layers overlap autocomplete list)
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        }
    });

});