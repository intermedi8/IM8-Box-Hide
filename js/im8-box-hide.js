jQuery(function($) {

	var $screenMetaLinks = $('#screen-meta-links');
	if ($screenMetaLinks.length) {
		var $poweredBy = $('<div id="screen-options-link-wrap"></div>');
		$poweredBy.append('<a class="show-settings" href="http://intermedi8.de" target="_blank">'+localizedData.poweredBy+'</a>');
		$('#screen-meta-links').append($poweredBy);
	}


	function registerButtonClick(postType) {
		$('#im8-box-hide-'+postType+' .js_btn').click(function() {
			var n = this.id.split('__');
			var $checkboxes = $('#im8-box-hide-'+postType+' input[name*="\\['+n[0]+'\\]\\['+n[1]+'\\]"]');
			$checkboxes.attr('checked', $checkboxes.filter(':checked').length < $checkboxes.length);
		});
	}


	if (localizedData.postTypes.length) {
		function printTable(postType, metaBoxes) {
			var $tbody = $('#im8-box-hide-'+postType).find('tbody');
			var alternate = true;

			var m = metaBoxes.length;
			for (var i = 0; i < m; ++i) {
				var id = metaBoxes[i][0];
				var title = metaBoxes[i][1];
				var trClass = (alternate) ? ' class="alternate"' : '';
				alternate = ! alternate;
				var $tr = $('<tr'+trClass+'></tr>');
				$tr.append('<th class="post-title">'+title+'</th>');
				$tr.append('<td><img id="'+postType+'__'+id+'" class="js_btn" src="'+localizedData.groupIcon+'" title="'+localizedData.groupToggle+'" alt="'+localizedData.groupToggle+'" align="bottom" /></td>');

				var r = localizedData.roles.length;
				for (var j = 0; j < r; ++j) {
					var role = localizedData.roles[j];
					var checked = (
						'undefined' != typeof localizedData.hide[role]
						&& 'undefined' != typeof localizedData.hide[role][postType]
						&& 'undefined' != typeof localizedData.hide[role][postType][id]
						&& !! localizedData.hide[role][postType][id]
					) ? ' checked="checked"' : '';
					$tr.append('<td class="num"><input type="checkbox" name="'+localizedData.optionName+'['+role+']['+postType+']['+id+']" value="1"'+checked+' /></td>');
				}

				$tbody.append($tr);
			}
		}

		var n = localizedData.postTypes.length;
		for (var i = 0; i < n; ++i) {
			var postType = localizedData.postTypes[i];
			$.ajax({
				type: 'post',
				url: localizedData.postURL+'?post_type='+postType,
				data: {
					action: 'im8-box-hide',
					_ajax_nonce: localizedData.nonce
				},
				beforeSend: function() {
					$('#im8-box-hide-'+postType+' img.loading').show();
				},
				success: function(data) {
					if (data) {
						$('#im8-box-hide-'+data.postType+' img.loading').remove();
						printTable(data.postType, data.metaBoxes);
						registerButtonClick(data.postType);
					}
				}
			});
		}
	}

});