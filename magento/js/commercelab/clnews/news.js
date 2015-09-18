jQuery.noConflict();

jQuery(document).ready(function($){
    if (jQuery('.'+'c'+'l'+'c'+'o'+'p'+'y'+'r'+'i'+'g'+'h'+'t').length <= 0 ) {
        if (jQuery('.'+'news'+'-'+'item').length > 0) {
            jQuery('.'+'news'+'-'+'item').hide();
        }
        if (jQuery('.'+'news').length > 0) {
            jQuery('.'+'news').hide();
        }
    }
});