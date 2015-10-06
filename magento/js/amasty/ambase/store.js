Event.observe(window, 'message', function(e){
    if (e.data.action == 'setHeight')
    {
        var height = e.data.height;
        $('amasty_store').setStyle({height: height+'px'});
    }
});
