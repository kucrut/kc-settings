<script type="text/javascript">
	// <![CDATA
	jQuery(document).ready(function($) {
		$('a.kc-rem').live('click', function() {
			$(this).parent().remove();
			return false;
		});

		$('a.kc-add').live('click', function() {
			var rows		= $(this).prev('div.kc-rows'),
					lastRow	= rows.children(':last-child'),
					rowNum	= lastRow.index() + 1,
					nuRow		= lastRow.clone();

			if ( $('input',lastRow).val() == '' && $('textarea',lastRow).val() == '' )
				return false;

			$('input', nuRow).attr('name', lastRow.attr('class') + '['+ rowNum +'][0]').val('');
			$('textarea', nuRow).attr('name', lastRow.attr('class') + '['+ rowNum +'][1]').val('');

			nuRow.appendTo(rows);
			return false;
		});
	});
	// ]]>
</script>
