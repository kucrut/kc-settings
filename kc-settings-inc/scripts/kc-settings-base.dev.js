/**
 * KC Settings Base
 */


// Credit: http://stackoverflow.com/questions/1584370/how-to-merge-two-arrays-in-javascript
Array.prototype.unique = function() {
	var a = this.concat();
	for(var i=0; i<a.length; ++i) {
		for(var j=i+1; j<a.length; ++j) {
			if(a[i] === a[j])
				a.splice(j, 1);
		}
	}

	return a;
};


function kcCountObj( obj ) {
	var count = 0;
	for (var k in obj) {
		if ( obj.hasOwnProperty(k) ) {
			++count;
		}
	}
	return count;
}


function kcsbSlug( str ) {
	strNu = str.replace(/^\-+/, '');
	strNu = strNu.replace(/^_+/, '');
	strNu = strNu.replace(/[^A-Za-z0-9\-_]/g, '');

	if ( strNu.match(/^\-+/) || strNu.match(/^_+/) )
		strNu = kcsbSlug( strNu );

	return strNu;
}


function invertColor( color ) {
	inverted = new RGBColor(color);
	if ( inverted.ok ) {
		color = 'rgb(' + (255 - inverted.r) + ', ' + (255 - inverted.g) + ', ' + (255 - inverted.b) + ')';
	}

	return color;
}


/* Post Finder dialog */
(function($, document) {
	var
	func = 'kcPostFinder',
	active = false,
	$_doc = $(document),
	selectors = ['.kc-find-post'],
	$_box, $_input, $_response, $_submit, $_close,
	getSelectors = function() {
		return selectors.join( ', ');
	}
	activate = function() {
		$_input = $('#find-posts-input');
		$_response = $('#find-posts-response');
		$_submit = $('#find-posts-submit');
		$_close = $('#find-posts-close');

		// Insert
		$_submit.on('click.kcPostFinder', function(e) {
			e.preventDefault();

			// Be nice!
			if ( !$_box.data('kcTarget') )
				return;

			var $selected = $_response.find('input:checked');
			if ( !$selected.length )
				return false;

			var $target = $_box.data('kcTarget'),
			    current = $target.val(),
			    current = current === '' ? [] : current.split(','),
			    newID   = $selected.val();

			if ( $target.is('.unique') ) {
				$target.val( newID );
			}
			else if ( $.inArray(newID, current) < 0 ) {
				current.push(newID);
				$target.val( current.join(',') );
			}
		});

		// Double click on the radios
		$_doc.on('dblclick.kcPostFinder', 'input[name="found_post_id"]', function() {
			$_submit.trigger('click.kcPostFinder');
		});

		// Close
		$_doc.on('click.kcPostFinder', '#find-posts-close', function() {
			$_input.val('');
			$_box.removeData('kcTarget');
		});

		active = true;
	},
	deactivate = function() {
		unbind();
		$_submit.off('click.kcPostFinder');
		$_doc.off('dblclick.kcPostFinder');
		$_doc.off('click.kcPostFinder');
		$_box = $_input = $_response = $_submit = $_close = null;
		active = false;
	},
	action = function(e) {
		$_box.data('kcTarget', $(this));
		findPosts.open();
	},
	bind = function() {
		$_doc.on( 'dblclick.kcPostFinder', getSelectors(), action );
	},
	unbind = function() {
		$_doc.off( 'dblclick.kcPostFinder', getSelectors(), action );
	},
	publicMethod = $[func] = function( sel ) {
		var $this = this;

		if ( active ) {
			if ( !sel )
				return;

			unbind();
		}
		else {
			$_box = $('#find-posts');
			if ( !$_box.length )
				return;

			activate();
		}

		if ( sel )
			selectors = selectors.concat( sel.split(',') );

		bind();

		return $this;
	};

	publicMethod.destroy = function() {
		deactivate();
	};
}(jQuery, document));


/* Form row cloner */
(function($, document) {
	var
	func = 'kcRowCloner',
	active = false,
	$_doc = $(document),
	callbacks = {
		add: [],
		del: []
	},

	activate = function() {
		bind();
		active = true;
	},

	deactivate = function() {
		unbind();
		active = false;
		callbacks = {
			add: [],
			del: []
		};
	},

	action = function(e) {
		var $anchor = $(e.target), func;

		if ( $anchor.is('a.add') )
			func = add;
		else if ( $anchor.is('a.del') )
			func = del;
		else
			return;

		e.preventDefault();
		var $item  = $(e.currentTarget),
		    isLast = !$item.next('.row').length,
		    $block = $item.parent();

		func.call( e, {
			'anchor': $anchor,
			'item': $item,
			'mode': $item.data('mode'),
			'isLast': isLast,
			'block': $block
		} );
	},

	add = function( args ) {
		var e = this,
		    nu = clear( args.item.clone(true).addClass('adding').hide() );

		$('[data-dep]', nu).removeData('kcfdInit');
		$('.hasdep', nu).kcFormDep();
		args.item.after( nu );
		args.nuItem = nu;
		args.block = args.block.kcReorder( args.mode, true );
		doCallbacks( 'add', e, args );

		args.nuItem.fadeIn('slow', function() {
			args.nuItem.removeClass('adding');
		});
	},

	del = function( args ) {
		var e = this;

		if ( !args.item.siblings('.row').length ) {
			args.item = clear( args.item );
			args.item.find('.hasdep').trigger('change');
			args.removed = false;
			doCallbacks( 'del', e, args, 'pret' );
		}
		else {
			args.removed = true;
			args.item.addClass('removing').fadeOut('slow', function() {
				args.item.remove();
				if ( !args.isLast )
					args.block = args.block.kcReorder( args.mode, true );
				delete args.item;
				doCallbacks( 'del', e, args );
			});
		}
	},

	clear = function( item ) {
		item.find(':input').each(function() {
			var $input = $(this);
			if ( $input.data('nocleanup') === true )
				return;

			if ( $input.is('select') || this.type == 'text' || this.type == 'textarea' )
				$input.removeAttr('style').val('');
			else if ( this.type == 'checkbox' || this.type == 'radio' )
				$input.prop('checked', this.checked);
		});

		return item;
	},

	doCallbacks = function( mode, e, args, x ) {
		for ( var i=0; i < callbacks[mode].length; i++ )
			callbacks[mode][i].call( e, args );
	},

	bind = function() {
		$_doc.on( 'click.kcRowCloner', 'li.row', action );
	},

	unbind = function() {
		$_doc.off( 'click.kcRowCloner', 'li.row', action );
	},

	publicMethod = $[func] = function( ) {
		var $this = this;

		if ( active )
			return;

		activate();
		return $this;
	};

	publicMethod.destroy = function() {
		deactivate();
	};

	publicMethod.addCallback = function( mode, callback ) {
		if ( callbacks.hasOwnProperty(mode) && $.isFunction(callback) )
			callbacks[mode].push( callback );
	};
})(jQuery, document);


(function($) {
	var $doc = $(document);


	$.fn.kcGoto = function( opts ) {
		defaults = {
			offset: -20,
			speed: 800
		};
		opts = $.extend( {}, defaults, opts );

		return this.each(function() {
			var $target = $(this);

			$target.fadeIn(function() {
				$('html, body').stop().animate({
					scrollTop: ( $target.offset().top + opts.offset )
				}, opts.speed );
			});
		});
	};


	$.fn.kcsbUnique = function() {
		return this.each(function() {
			var $this	= $(this),
			    olVal	= $this.val();

			$this.data('olVal', olVal)
				.blur(function() {
					var $input = $(this),
					    olVal  = $this.data('olVal'),
					    nuVal  = $input.val();

					if ( nuVal != olVal && $.inArray(nuVal, kcSettings._ids[$input.data('ids')]) > -1 )
						$input.val('');
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


	$.fn.kcFormDep = function( opts ) {
		var defaults = {
		      disable: true,
		      callback: function() {}
		    },
		    opts = $.extend({}, defaults, opts),
		    onChange = function( e ) {
					var $el = $(e.target),
							val = $el.val();

					$el.data('depTargets').each(function() {
						var $c = $(this);
						if ( e.kcfdInit === true ) {
							if ( $c.data('kcfdInit') )
								return;
							else
								$c.data('kcfdInit', true);
						}

						var depon = $c.data('dep'),
						    show  = false;

						if ( !$el.prop('disabled') && (((typeof depon === 'string' || typeof depon === 'number') && depon == val) || (typeof depon === 'object' && $.inArray(val, depon) > -1)) )
							show = true;

						$c.toggle( show );
						if ( opts.disable === true ) {
							$c.find(':input').prop('disabled', !show).trigger('change');
						}
					});
				};

		return this.each(function() {
			var $el      = $(this),
			    val      = $el.val(),
			    $targets = ( $el.data('scope') !== undefined ) ?
			                 $el.closest( $el.data('scope') ).find( $el.data('child') ) :
			                 $( $el.data('child') );

			if ( $targets.length )
				$el.data('depTargets', $targets)
					.on('change', onChange).trigger( {type: 'change', kcfdInit: true} );
		});
	};


	$.fn.kcReorder = function( mode, all ) {
		var rgx1 = new RegExp(mode+'\\]\\[(\\d+)'),
		    rgx2 = new RegExp(mode+'\\-(\\d+)'),
		    $el  = $(this);

		if ( all === true ) {
			var $els = $el.children(),
			    i    = 0;
		} else {
			var $els = $el,
			    i    = $el.index();
		}

		$els.each(function() {
			var $x = $(this);
			$x.find(':input').each(function() {
				this.name = this.name.replace(rgx1, function(str, p1) {
					return mode + '][' + i;
				});

				if ( this.id !== '' ) {
					this.id = this.id.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					});
				}
			});

			$x.find('label').each(function() {
				var $label 	= $(this),
						atFor		= $label.attr('for');

				if ( atFor !== '' && atFor !== undefined ) {
					$label.attr( 'for', atFor.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					}) );
				}
			});

			i++;
		});

		return this;
	};


	$.fn.kcTabs = function() {
		var switchPanel = function(e) {
			e.preventDefault();
			var $anchor = $(e.currentTarget),
			    $parent = $anchor.parent();

			if ( $parent.hasClass('tabs') )
				return;

			$anchor.closest('ul').data('kcTabsPanels').hide().filter($anchor.data('kcTabsPanel')).show();
			$parent.addClass('tabs').siblings().removeClass('tabs');
		};

		return this.each(function() {
			var $list = $(this),
			    $anchors = $();
			    $panels = $();

			$list.children().each(function(idx) {
				var $anchor = $(this).children('a').first();
				if ( !$anchor.length )
					return;

				var $panel = $( $anchor.attr('href') );
				if ( !$panel.length )
					return;

				$panels = $panels.add($panel);
				$anchors = $anchors.add($anchor);
				$anchor.data('kcTabsPanel', $panel)
					.on('click', switchPanel);
			});

			$list.data({
				'kcTabsPanels': $panels,
				'kcTabsAnchors': $anchors
			});
			$anchors.first().trigger('click');
		});
	};


	/* Component metabox toggler */
	$.fn.kcMetaboxDeps = function() {
		var	$kcForm = $(this),
		    $mBoxRoot = $kcForm.find('div.metabox-holder');

		if ( !$mBoxRoot.length )
			return $kcForm;

		var prefix = $mBoxRoot.attr('id'),
		    $checks = $kcForm.find(':checkbox');

		if ( !$checks.length )
			return $kcForm;

		var $secTogglers = $();

		$checks.each(function() {
			var $sectBox = $( '#'+prefix+'-'+this.value );
			if ( !$sectBox.length )
				return;

			var $check = $(this),
			    $target = $('#'+prefix+'-'+this.value+'-hide');

			$check.data( 'sectHider', $target ).data( 'sectBox', $sectBox );
			if ( !(this.checked === $target[0].checked) ) {
				$target.prop('checked', this.checked).triggerHandler('click');
			}

			$secTogglers = $secTogglers.add( $check );
		});

		if ( !$secTogglers.length )
			return $kcForm;

		$secTogglers.change(function() {
			var $el = $(this);
			$el.data('sectHider').prop('checked', this.checked).triggerHandler('click');

			// Scroll to
			if ( this.checked )
				$el.data('sectBox').kcGoto( {offset: -40, speed: 'slow'} );
		});
	};

	/* Polyfills */
	if ( !Modernizr.details ) {
		$doc.on('click', 'summary', function(e) {
			if ( $(e.target).is('a') )
				return;

			var $summary = $(this),
					$details = $summary.parent();

			if ( $details.attr('open') )
				$details.removeAttr('open');
			else
				$details.attr('open', 'open');
		});
	}
})(jQuery);