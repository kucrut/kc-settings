(function($) {
	$.fn.kcsbUnique = function() {
		return this.each(function() {
			var $this	= $(this),
			    olVal	= $this.val();

			$this.data('olVal', olVal)
				.blur(function() {
					var $input = $(this),
					    olVal  = $this.data('olVal'),
					    nuVal  = $input.val();

					if ( nuVal != olVal && $.inArray(nuVal, kcsbIDs[$input.data('ids')]) > -1 )
						$input.val('').css('borderColor', '#ff0000');
				});
		});
	};


	$.fn.kcsbCheck = function() {
		var $input = $(this);

		if ( ($input.attr('name') === 'kcsb[id]' && $input.val() === 'id') || $input.val() === '' ) {
			$input.parents('details').each(function() {
				var $details = $(this);
				if ( !$details.attr('open') )
					$details.children('summary').click();
			});
			$input.val('').focus().css('borderColor', '#ff0000').kcGoto();

			return false;
		}
		else {
			$input.removeAttr('style');
		}
	};
})(jQuery);


jQuery(document).ready(function($) {
	var $doc = $(this);
	var $builder = $('#kcsb');

	var pluginArgs = {
		sortable : {
			axis: 'y',
			start: function(ev, ui) {
				ui.placeholder.height( ui.item.height() );
			},
			stop: function(ev, ui) {
				ui.item.parent().children().each(function(idx) {
					$('> details > summary .count', this).text( idx + 1);
				});
			}
		}
	};

	$.kcRowCloner();
	$.kcRowCloner.addCallback( 'add', function( args ) {
		args.nuItem.find('.kc-rows').each(function() {
			$(this).children('.row').not(':first').remove();
		});

		$('input.kcsb-ids', args.nuItem).removeData('olVal').kcsbUnique();

		if ( args.isLast ) {
			$('> details > summary .count', args.nuItem).text( args.nuItem.index() + 1 );
		}
		else {
			args.block.children().each(function() {
				$('> details > summary .count', this).text( $(this).index() + 1 );
			});
		}
		if ( args.mode === 'sections' ) {
			$('ul.general .hasdep', $builder).kcFormDep().trigger('change');
		}
		$('ul.kc-rows').sortable( pluginArgs.sortable );
	});
	$.kcRowCloner.addCallback( 'afterAdd', function( args ) {
		args.nuItem.kcGoto();
	});

	$.kcRowCloner.addCallback( 'del', function( args ) {
		if ( args.isLast )
			return;

		args.block.children().each(function() {
			$('> details > summary .count', this).text( $(this).index() + 1 );
		});
	});


	// Scroll to form
	if ( !$builder.is('.hidden') )
		$builder.kcGoto();

	// Sort
	$('ul.kc-rows').sortable( pluginArgs.sortable );

	// Field deps
	$('.hasdep', $builder).kcFormDep();
	// Special: disable metabox checkboxes for attachment
	$('#_kcsb-post_type').on('change', function() {
		if ( this.value === 'attachment' ) {
			$('li._sd_post-type').hide()
				.find('input').prop('disabled', true);
		}
		else {
			$('li._sd_post-type').show()
				.find('input').prop('disabled', false);
		}
	}).trigger('change');

	// Check 'slug/id' fields
	$doc.on('blur', 'input.kcsb-slug', function() {
		var $input = $(this);
		$input.val( kcsbSlug( $input.val() ) );
	});

	$('input.kcsb-ids').kcsbUnique();

	$doc.on('blur', 'input.required', function() {
		$(this).kcsbCheck();
	});


	// Show form
	$('#new-kcsb').on('click', function(e) {
		if ( $builder.is('.hidden') ) {
			e.preventDefault();
			$builder.kcGoto();
		}
	});


	$('a.kcsb-cancel').on('click', function(e) {
		e.preventDefault();
		$builder.slideUp('slow');
	});


	// Setting clone
	$('a.clone-open').on('click', function(e) {
		e.preventDefault();
		$(this).parent().children().hide().filter('div.kcsb-clone').fadeIn(function() {
			$(this).find('input.clone-id').focus();
		});
	});


	$('a.clone-do').on('click', function(e) {
		var $el    = $(this),
		    $input = $el.siblings('input');

		if ( $input.kcsbCheck() === false )
			return false;

		$el.attr( 'href', $el.attr('href')+'&new='+$input.val() );
	});


	$('input.clone-id').on('keypress', function(e) {
		var key = e.keyCode || e.which;
		if ( key === 13 ) {
			e.preventDefault();
			$(this).blur().siblings('a.clone-do').click();
		}
	});


	$('.kcsb-tools a.close').on('click', function(e) {
		e.preventDefault();
		var $el = $(this);

		$el.siblings('input').val('').removeAttr('style');
		$el.parent().fadeOut(function() {
			$(this).siblings().show();
		});
	});


	$('form.kcsb', $builder).submit(function(e) {
		var isOK = true;

		$(this).find('input.required').not(':disabled').each(function() {
			if ( $(this).kcsbCheck() === false ) {
				isOK = false;
				return false;
			}
		});

		if ( !isOK )
			return false;
	});

	$doc.on('click', 'a.kc-sh', function(e) {
		e.preventDefault();

		var $el = $(this).blur(),
		    $target = $( $el.data('target') );

		if ( $target.is(':visible') ) {
			$target.slideUp(function() {
				$el.text(kcSettings.texts.show);
			});
		}
		else {
			$target.slideDown(function() {
				$el.text(kcSettings.texts.hide);
			});
		}
	});
});