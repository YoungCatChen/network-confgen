(function ($) {
	
	this.new_result_slot = function() {
		return $('<div></div>').appendTo($('#results'));
	}

	this.check_param = function() {
		if ($.trim($('#form-user').val()) == '')
			return 'You have not enter your username.';
		if ($.trim($('#form-pass').val()) == '')
			return 'You have not enter your password.';

		var data = $('#form').serialize();
		
		if (data.indexOf('index') < 0)
			return 'You have not chosen a server.';

		return '';
	}

	this.post_form = function(otherparam, msg_before_post) {
		var div = new_result_slot();
		div.html(msg_before_post);
		var checked = check_param();
		
		if (checked != '') {
			div.html(checked);
			return;
		}

		var data = $('#form').serialize() + '&ajax=1' + otherparam;
		div.load('#', data);
	}

})(jQuery);


(function ($) { $(function() {
	
	$('#form-submit').click(function(e) {
		post_form('&add=1', 'Adding...');
		return false;
	});

	$('#form-genmark').click(function(e) {
		post_form('&genmark=1', 'Generating...');
		return false;
	});

}); })(jQuery);

