(function($){

  $.extend({
        mask: function(options){
            var op = $.extend({
                opacity: 0.7,
                z: 10000,
                bgcolor: '#000'
            }, options);

            $('<div class="jquery_addmask">&nbsp;</div>').appendTo(document.body).css({
                position: 'absolute',
                top: '0px',
                left: '0px',
                'z-index': op.z,
                width: $(document).width(),
                height: $(document).height(),
                'background-color': op.bgcolor,
                opacity: 0
            }).fadeIn('slow', function(){
                $(this).fadeTo('slow', op.opacity);
            });

            return this;
        }, 
        unmask: function() {
            $('div.jquery_addmask').fadeTo('slow', 0, function(){
                $(this).remove();
            });

            return this;
        }
    });

})(jQuery);
