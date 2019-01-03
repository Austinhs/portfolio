function renderTableImageViewer() {
	var $modules     = $('.tableImageViewer');
	var builderCount = 1;
	var tableCount   = 0;
	var lastTable;

	$modules.hide();
	$modules.each(function(index) {
		var item              = $modules.get(index);
		var $item             = $(item);
		var moduleId          = $item.find('module').html();
		var moduleImage       = $item.find('moduleImage').html();
		var moduleImageCount  = $item.find('moduleImageCount').html();
		var moduleHeader      = $item.find('moduleHeader').html();
		var moduleDescription = $item.find('moduleDescription').html();

		if(builderCount === 1) {
			lastTable = 'tableImageViewer'+tableCount;

			var td1 = '<td class="column1 tableImageViewerColumn"></td>';
			var td2 = '<td class="column2 tableImageViewerColumn"></td>';
			var td3 = '<td class="column3 tableImageViewerColumn"></td>';

			var rowHeader      = '<tr class="rowHeader">'+td1+td2+td3+'</tr>';
			var rowImage       = '<tr class="rowImage">'+td1+td2+td3+'</tr>';
			var rowView        = '<tr class="rowView">'+td1+td2+td3+'</tr>';
			var rowDescription = '<tr class="rowDescription">'+td1+td2+td3+'</tr>';

			$('#tableImageViewerContainer').append('<table id='+lastTable+' class="tableImageViewerTable">'+rowHeader+rowImage+rowView+rowDescription+'</table>');

			tableCount++;

			// if($modules.length/3 > tableCount) {
			//   $('#tableImageViewerContainer').append('<hr>');
			// }
		}

		var columnClass = ' .column'+builderCount;
		var $table      = $('#'+lastTable);

		if(moduleImageCount) {
			var viewExtra = ' 1/'+moduleImageCount;
		} else {
			var viewExtra = '';
		}

		var contentHeader      = '<h2 class="columnHeader headerItem">'+moduleHeader+'</h2>';
		var contentImage       = '<img src="'+moduleImage+'" style="width: 100%;">';
		var contentView        = '<button class="ui button fluid basic blue tableImageViewerViewButton" data-module='+moduleId+'><i class="arrow circle up icon"></i>View'+viewExtra+'</button>';
		var contentDescription = '<p class="columnDescription">'+moduleDescription+'</p>';

		$table.find('.rowHeader'+columnClass).html(contentHeader);
		$table.find('.rowImage'+columnClass).html(contentImage);
		$table.find('.rowView'+columnClass).html(contentView);
		$table.find('.rowDescription'+columnClass).html(contentDescription);

		builderCount++;

		if(builderCount > 3) {
			builderCount = 1;
		}
	});
}

$(document).on('click','.tableImageViewerViewButton', function() {
	var moduleId = $(this).data('module');
	var source   = document.getElementById('viewImage-template').innerHTML;
	var template = Handlebars.compile(source);
	var html     = template({});
	
	images   = allImages[moduleId];
	$('#modal').html(html);
	$('#modal').modal({
		closable: false,
		transition: 'horizontal flip'
	}).modal('show');
	$('.ui.modal').modal('refresh');
	
  // Viewer variables
	curImageIdx = 1;
	total = images.length;
	wrapper = $('#image-gallery');
	curSpan = wrapper.find('.current');
	viewer = ImageViewer(wrapper.find('.image-container'));

	//display total count
	wrapper.find('.total').html(total);
	
  // Handler for next and prev
	wrapper.find('.next').click(function(){
			 curImageIdx++;
			if(curImageIdx > total) curImageIdx = 1;
			showImage();
	});

	wrapper.find('.prev').click(function(){
			 curImageIdx--;
			if(curImageIdx < 0) curImageIdx = total;
			showImage();
	});
	
	//initially show image
	showImage();
});

function showImage(){
		var imgObj = images[curImageIdx - 1];
		viewer.load(imgObj);
		curSpan.html(curImageIdx);
}

$(document).on('click', '.closeModal', function() {
	$('#modal').modal('hide');
});
