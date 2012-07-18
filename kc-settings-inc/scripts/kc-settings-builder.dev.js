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
						$input.val('').focus();
				});
		});
	};


	$.fn.kcsbCheck = function() {
		var $input = $(this);

		if ( ($input.attr('name') === 'kcsb[id]' && $input.val() === 'id') || $input.val() === '' ) {
			$input.val('').focus().css('borderColor', '#ff0000');
			return false;
		} else {
			$input.removeAttr('style');
		}
	};
})(jQuery);


jQuery(document).ready(function($) {
	var $doc = $(this);
	var pluginArgs = {
		sortable : {
			axis: 'y',
			start: function(ev, ui) {
				ui.placeholder.height( ui.item.outerHeight() );
			},
			stop: function(ev, ui) {
				ui.item.children().each(function() {
					$('> details > summary > .actions .count', this).text( $(this).index() + 1);
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
			$('> details > summary > .actions .count', args.nuItem).text( args.nuItem.index() + 1 );
		}
		else {
			args.block.children().each(function() {
				$('> details > summary > .actions .count', this).text( $(this).index() + 1 );
			});
		}
		$('ul.kc-rows').sortable( pluginArgs.sortable );
	});

	$.kcRowCloner.addCallback( 'del', function( args ) {
		if ( args.isLast )
			return;

		args.block.children().each(function() {
			$('> details > summary > .actions .count', this).text( $(this).index() + 1 );
		});
	});


	var $builder = $('#kcsb');

	// Scroll to form
	if ( !$builder.is('.hidden') )
		$builder.kcGoto();

	// Sort
	$('ul.kc-rows').sortable( pluginArgs.sortable );

	// Field deps
	$('.hasdep', $builder).kcFormDep();

	// Check 'slug/id' fields
	$doc.on('blur', 'input.kcsb-slug', function() {
		var $input = $(this);
		$input.val( kcsbSlug( $input.val() ) );
	});

	$('input.kcsb-ids').kcsbUnique();

	$doc.on('blur', 'input.required, input.clone-id', function() {
		$(this).kcsbCheck();
	});


	// Show form
	$('#new-kcsb').on('click', function(e) {
		e.preventDefault();
		$builder.kcGoto();
	});


	$('a.kcsb-cancel').on('click', function(e) {
		e.preventDefault();
		$('#kcsb').slideUp('slow');
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
		    $input = $(this).siblings('input');

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

		$el.siblings('input').val('');
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
});