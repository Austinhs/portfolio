//This allows includeHandlebarTemplates.js to render the templates before
//we try to call pageRender('home') on a template not yet added to the DOM.
var loadLoop = setInterval(function() {
	if(location.hash) {
		var page = location.hash.substr(1);
		if($('#'+page)) {
			$('#'+page+'Tab').trigger('click');
			$('#pageContainer .loadingDiv').fadeOut('slow');
			stopInitialLoadLoop();
		}
	} else {
		if($('#home')) {
			pageRender('home');
			$('.ui.rating').rating({interactive: false});
			$('#pageContainer .loadingDiv').fadeOut('slow');
			stopInitialLoadLoop();
		}
	}
}, 100);

$(document).on('click', '#contactMessageSubmit', function() {
	alert('This feature is currently disabled, please use the contact information provided on this page instead. Sorry for the inconvenience');
});

$(document).on('click', '.menu .menuItem', function() {
	var $this = $(this);
	var tab   = $this.attr('id');

	if($this.attr('href')) {
		return;
	}

	if($this.hasClass('active') || $('.item').hasClass('loadingPage')) {
		return;
	}

	$('.item').addClass('loadingPage');
	pageRender(findPage(tab), function() {
		if(findPage(tab) == 'home') {
			$('.ui.rating').rating({interactive: false});
		}

		if(findPage(tab) == 'contact') {
			var contactMessage = new Quill('#contactMessage', {
				theme: 'snow'
			});
		}

		if(findPage(tab) == 'previous_work') {
			renderTableImageViewer();
		}
	});


	setTimeout(function() {
		$('.item').removeClass('loadingPage');
	}, 600);
});

/**
@arg page - the id name of a page currently: home, previous_work, contact
*/
function pageRender(page, callback) {
	var source      = document.getElementById(page+'-template').innerHTML;
	var template    = Handlebars.compile(source);
	var newPage     = template({});
	var $activePage = $('.activePage');

	location.hash = '#'+page;

	//Tab control
	var $newTab = $('#'+page+'Tab');
	$('.item.active').removeClass('active');
	$newTab.addClass('active');


	//Fly out the old page and fly in new page if it
	//exists otherwise just fly in new page
	if($activePage.get(0)) {
		$activePage.removeClass('activePage');
		$activePage.transition('fly left', '500ms', function() {
			$activePage.remove();

			$('#pageContainer').append(newPage);

			if(callback) {
				callback();
			}

			$('#'+page)
				.addClass('activePage')
				.addClass('transition')
				.addClass('hidden')
				.transition('fly right');
		});
	} else {
		$('#pageContainer').append(newPage);

		if(callback) {
			callback();
		}

		$('#'+page)
			.addClass('activePage')
			.addClass('transition')
			.addClass('hidden')
			.transition('fly right');
	}
}

function findPage(tab) {
	return tab.slice(0, -3);
}

function stopInitialLoadLoop() {
	clearInterval(loadLoop);
}
