var win = window.dialogArguments || opener || parent || top;

win.kcsInsertFiles = function() {
	var count = win.kcSettings.upload.nu.length;

	if ( count ) {
		var $list = win.kcSettings.upload.id,
				$lastItem = $list.children().last(),
				$nuEls = jQuery();

		while ( count ) {
			count--;
			var $nuItem = $lastItem.clone();

			jQuery('input', $nuItem).each(function() {
				this.value = win.kcSettings.upload.nu[count][0];
			});
			jQuery('.title', $nuItem).text(win.kcSettings.upload.nu[count][1]);
			$nuItem.find('img').attr('src', win.kcSettings.upload.nu[count][2]);

			$nuEls = $nuEls.add( $nuItem );
		}

		$list.append( $nuEls );
		if ( $lastItem.is('.hidden') ) {
			$nuEls.show();
			$lastItem.remove();
		}
	}
};



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
		$(this).closest('li').fadeOut(function() {
			$(this).remove();
		});

	});

	// Add files button
	$('a.kcsf-upload').live('click', function(e) {
		e.preventDefault();
		var $el = $(this),
				$group = $el.parent();

		win.kcSettings.upload.id = $( '#'+$group.attr('id')+' > ul' );
		win.kcSettings.upload.files = [];
		$('input.mid', $group).each(function() {
			win.kcSettings.upload.files.push(this.value);
		});

		tb_show( '', $el.attr('href') );
	});

	$('ul.kc-sortable').sortable({
		axis: 'y',
		start: function(ev, ui) {
			ui.placeholder.height( ui.item.outerHeight() );
		}
	});

});
