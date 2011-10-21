(function($) {
	$.fn.kcRowClone = function(opt) {

		function showHideButtons( ev ) {
			var $this		= $(this),
					options	= ev.data;

			$this.children(options.cloneClass).each(function() {
				var $row 			= $(this),
						$actions	= $row.children(options.actionClass),
						$add			= $actions.find('.add'),
						$del			= $actions.find('.del');
						$up				= $actions.find('.move.up');
						$down			= $actions.find('.move.down');

				if ( $row.is(':first-child') ) {
					$add.hide();
					$up.hide();
				}

				if ( $row.is(':last-child') ) {
					$add.show();
					$up.show();
					$down.hide();
				}
				else {
					$add.hide();
					$down.show();
				}

				if ( $row.is(':only-child') ) {
					$add.show();
					$del.hide();
					$up.hide();
					$down.hide();
				}
				else {
					$del.show();
				}
			});

			return $this;
		}


		function reorder( ev, mode ) {

			if ( mode != '' || mode != 'undefined' ) {
				var pPrefix	= mode + '\\]\\[';
						rPrefix = mode + ']['
			}
			else {
				var pPrefix	= rPrefix = null;
			}

			var options	= ev.data,
					regex		= new RegExp( pPrefix+'(\\d+)' )
					$root		= $(this),
					$rows		= $root.children(options.cloneClass),
					i				= -1;

			$rows.each(function() {
				i++;
				$(this).find(':input').each(function() {
					this.name = this.name.replace(regex, function(str, p1) {
						return rPrefix + i;
					});
				});
			});

			return $root;
		}


		function actionButtons( ev ) {
			var options	= ev.data.options,
					$root		= ev.data.root,
					$button	= $(this),
					mode		= $button.attr('rel'),
					$row		= $button.closest(options.cloneClass);

			if ( $button.is('.clear') ) {
				$button.closest(options.cloneClass).find(':input').val('');
			}
			else {
				if ( $button.is('.del') ) {
					options.beforeRemove.call( this, $row, options );

					$row.css('backgroundColor', options.colors.remove)
							.fadeOut(options.speed, function() {
								$(this).remove();
							});

					options.afterRemove.call( this, $row, options );
				}
				else if ( $button.is('.add') ) {
					var $new	= $row.clone(true).hide();
					$new.find(':input').val('');

					options.beforeAdd.call( this, $new, options );

					$root.append(
						$new.css('backgroundColor', options.colors.add)
								.fadeIn(options.speed, function() {
									$(this).css('background', 'none')
										.find(':input').first().focus();
								})
					);

					options.afterAdd.call( this, $new, options );
				}
				else if ( $button.is('.move') ) {
					if ( $button.is('.up') )
						var $new = $row.prev().before( $row.detach() ).prev().hide();
					else
						var $new = $row.next().after( $row.detach() ).next().hide();

					options.beforeMove.call( this, $new, options );

					$new.css('backgroundColor', options.colors.move)
							.fadeIn(options.speed, function() {
								$(this).css('background', 'none')
											.find(':input').first().focus();
							});

					options.afterMove.call( this, $new, options );
				}


				var wait = setInterval(function() {
					if ( !$root.find(':animated').length ) {
						clearInterval(wait);
						$root.triggerHandler( 'kcrc_change', mode )
								.triggerHandler( 'kcrc_init' );
					}
				}, options.speed);
			}

			return false;
		}


		return this.each(function() {
			var $self		= $(this),
					options	= $.extend( true, {}, $.fn.kcRowClone.defaults, opt ),
					$rows		= $self.children(options.cloneClass);

			if ( !$rows.length )
				return;

			$self.data('kcRowClone', options);

			$self.bind( 'kcrc_change', options, reorder );
			$self.bind( 'kcrc_init', options, showHideButtons );

			$rows.each(function() {
				$(this).children(options.actionClass).delegate('a', 'click', {'options': options, 'root': $self}, actionButtons);
			});

			$self.triggerHandler( 'kcrc_init' );
		});
	};


	$.fn.kcRowClone.defaults = {
		cloneClass	: '.row',
		actionClass	: '.actions',
		speed				: 400,
		colors			: {
			add				: '#b4f28f',
			remove		: '#e8a09e',
			move			: '#f8f8be'
		},
		beforeAdd			: function() {},
		afterAdd			: function() {},
		beforeRemove	: function() {},
		afterRemove		: function() {},
		beforeMove		: function() {},
		afterMove			: function() {}
	};

})(jQuery);