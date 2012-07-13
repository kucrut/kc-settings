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