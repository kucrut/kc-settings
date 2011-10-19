var win = window.dialogArguments || opener || parent || top;
win.pret = function() {
	var count = win.kcFiles.nu.length;


	if ( count ) {
		var $list = jQuery('#'+win.kcFiles.id+' ul'),
				$lastItem = $list.children().last(),
				$nuEls = jQuery();

		while ( count ) {
			count--;
			var $nuItem = $lastItem.clone();

			jQuery('input', $nuItem).each(function() {
				this.value = win.kcFiles.nu[count][0];
			});
			jQuery('.title', $nuItem).text(win.kcFiles.nu[count][1]);

			$nuEls = $nuEls.add( $nuItem );
		}

		$list.append( $nuEls );
		if ( $lastItem.is('.hidden') ) {
			$nuEls.fadeIn();
			$lastItem.remove();
		}
	}
};

win.kcFiles = {};


jQuery(document).ready(function($) {
	$('.kcs-rows').kcRowClone();

	// Datepicker
	var $dateInputs = $('input[type=date]');
	if ( $dateInputs.length && Modernizr.inputtypes.date === false ) {
		$dateInputs.datepicker({
			dateFormat: 'yy-mm-dd'
		});
	}

	// File
	$('.kcs-file a.del').live('click', function(e) {
		e.preventDefault();
		$(this).closest('li').remove();
	});

	$('a.kcsf-upload').live('click', function(e) {
		e.preventDefault();
		var $el = $(this),
				$group = $el.parent();

		win.kcFiles.id = $group.attr('id');
		win.kcFiles.name = $el.attr('rel');
		win.kcFiles.addText = $el.attr('title');
		win.kcFiles.files = [];
		$('input.mid', $group).each(function() {
			win.kcFiles.files.push(this.value);
		});

		tb_show( '', $el.attr('href') );
	});

});
