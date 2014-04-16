/* FROM SUBMIT PLUGIN */
(function ($) {
    $.fn.formSubmit = function (options) {
        var settings = $.extend({url: false, 'dataType':'html', disableSubmit: true, beforeSubmit: null, onError: null, onSuccess: null}, options);

        return this.each(function () {
			var $form = $(this);

	        $form.submit(function (e) {
                e.preventDefault();

                if(settings.disableSubmit) {
	                $form.find('[type=submit]').attr('disabled', 'disabled');
                }

                if(settings.beforeSubmit) {
	                settings.beforeSubmit();
                }

                if(settings.url == false) {
                    var url = $form.attr('action') == '' ? window.location.href : $form.attr('action');
                } else {
                    var url = settings.url;
                }

		        var formData = $(this).serialize();

                $.ajax({
                    url: url,
                    dataType:settings.dataType,
                    data: formData,
                    type: 'post',
                    success:function (data, textStatus, jqXHR) {
	                    $form.find('[type=submit]').removeAttr('disabled');

                        if (settings.onSuccess) {
                            settings.onSuccess(data, textStatus, jqXHR, $form);
                        }
                    },
                    error:function (data, textStatus, jqXHR, $form) {
	                    if (settings.onError) {
                            settings.onError(data, textStatus, jqXHR, $form);
                        }
                    }
                });
                return false;
            });
        });
    };
})(jQuery);