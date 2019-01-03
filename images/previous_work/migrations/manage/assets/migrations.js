(function(global) {
	'use strict';

	var body = global.document.body;
	var main = body.getElementsByTagName('main')[0];

	if(App.context.allowed) {
		main.innerHTML = Template.main();
	}
	else {
		main.innerHTML = Template.login();
		return;
	}

	var modal          = body.getElementsByClassName('modal')[0];
	var modal_inner    = modal.getElementsByClassName('modal-inner')[0];
	var spinner        = main.getElementsByClassName('ani-spinner')[0];
	var table          = main.getElementsByClassName('migrations-table')[0];
	var filter         = main.getElementsByClassName('migrations-filter')[0];
	var run            = main.getElementsByClassName('run-migrations')[0];
	var stop           = main.getElementsByClassName('stop-migrations')[0];
	var show_pending   = main.getElementsByClassName('show-pending')[0];
	var show_completed = main.getElementsByClassName('show-completed')[0];
	var progress       = body.getElementsByClassName('progress')[0];
	var progress_inner = body.getElementsByClassName('progress-inner')[0];
	var running        = true;
	var cancelled      = false;
	var controller     = Controller.MigrationsController;

	// Set up the sort and map functions
	var elements         = Array.prototype.slice.call(table.querySelectorAll('[data-sort-key]'), 0);
	var indexed_elements = {};
	var sort_directions  = [];
	var sort_priorities  = [];

	elements.forEach(function sorter(element) {
		var key = element.getAttribute('data-sort-key');

		indexed_elements[key] = element;

		// Sort by date initially
		if(key === 'date') {
			sort_priorities.unshift(key);
			sort_directions.unshift(1);
		}
		else {
			sort_priorities.push(key);
			sort_directions.push(0);
		}

		element.addEventListener('click', function() {
			var i = sort_priorities.indexOf(key);

			// Change the direction
			var new_vals = {
				'0'  : 1,
				'1'  : -1,
				'-1' : 0
			};

			var old_val = sort_directions[i];
			var new_val = new_vals[old_val + ''];

			sort_directions[i] = new_val;

			// If there is a direction, make this first priority, otherwise, last
			var pusher = new_val === 0 ? 'push' : 'unshift';

			sort_priorities[pusher](sort_priorities.splice(i, 1).pop());
			sort_directions[pusher](sort_directions.splice(i, 1).pop());

			// Update the UI
			refreshMigrations();
		});
	});

	// Sort function to sort with multiple priorities
	var sort = function(a, b) {
		for(var i in sort_priorities) {
			var key = sort_priorities[i];
			var dir = sort_directions[i];

			if(dir === 0 || a[key] === b[key]) {
				continue;
			}

			if(a[key] === null) {
				diff = -1;
			}
			else if(b[key] === null) {
				diff = 1;
			}
			else {
				var diff = b[key] - a[key];

				if(global.isNaN(diff)) {
					diff = b[key] > a[key] ? 1 : -1;
				}
			}

			return diff * dir;
		}

		return 0;
	};

	// Map function to hide filtered rows and highlight matches
	var map = function(row) {
		var value = filter.value.toLowerCase();

		if(!value.length) {
			return row;
		}

		var highlight = {};
		var hide      = true;

		for(var col in row) {
			var text = (row[col] + '').toLowerCase();

			if(text.indexOf(value) !== -1) {
				highlight[col] = true;
				hide           = false;
			}
		}

		row._highlight = highlight;
		row._hide      = hide;

		return row;
	};

	// Handle filtering migrations
	filter.addEventListener('input', function() {
		var input = this;

		if(filter.__timeout) {
			global.clearTimeout(filter.__timeout);
		}

		filter.__timeout = global.setTimeout(function() {
			// Update the UI
			refreshMigrations();
		}, 50);
	});

	// Handle show pending/completed
	show_pending.addEventListener('change', refreshMigrations);
	show_completed.addEventListener('change', refreshMigrations);

	// Handle running migrations
	run.addEventListener('click', function() {
		if(running || !confirm("Would you like to run all pending migrations?")) {
			return;
		}

		controller.startMigrations().then(function() {
			pollMigrations();
		}, error);
	});

	// Handle stopping migrations
	stop.addEventListener('click', function() {
		if(!running || !confirm("Would you like to stop running migrations?")) {
			return;
		}

		cancelled = true;
		controller.stopMigrations(true).catch(error);
	});

	// Handle viewing the output and deleting migration records
	global.document.addEventListener('click', function(e) {
		if(e.target.classList && e.target.classList.contains('view-output')) {
			var output = e.target.nextElementSibling.innerText || '{}';

			try {
				var parsed = JSON.parse(output);
			}
			catch(e) {
				// If we fail to parse the output, just show what we can
				var parsed = {
					sql : {},

					output : [
						"Unfortunately, this output could not be parsed:",
						output
					].join('\n\n')
				};
			}

			var text            = parsed.output;
			var sql_log         = parsed.sql.sql_log || [];
			var sql_times       = parsed.sql.sql_times || [];
			var sql_fetch_times = parsed.sql.sql_fetch_times || [];

			var sql = sql_log.map(function(sql, i) {
				var sql_time       = sql_times[i];
				var sql_fetch_time = (sql_fetch_times[i] >= 0) ? sql_fetch_times[i] : 'N/A';

				return [
					"<h3>Statement:</h3>\n",
					"<pre>" + sql + "</pre>",
					"<h3>Run Time: " + sql_time + "</h3>",
					"<h3>Fetch Time: " + sql_fetch_time + "</h3>",
				].join('');
			}).join("<hr>");

			if(sql.length) {
				sql = "<hr>" + sql;
			}

			var html = Template.output_modal({
				text : text,
				sql  : sql
			});

			modal_inner.innerHTML = html;
			modal.classList.remove('hidden');

			setTimeout(function() {
				modal.classList.add('animate-in');
			});

			var close = modal_inner.getElementsByClassName('close-button')[0];

			close.addEventListener('click', function() {
				modal.classList.add('hidden');
				modal.classList.remove('animate-in');
			});
		}
		else if(e.target.classList && e.target.classList.contains('delete-button')) {
			if(running) {
				alert("Please wait until all pending migrations have completed.");
				return;
			}

			if(confirm("Are you sure you want to delete this migration?")) {
				// Get the ID of the migration record
				var id = +e.target.getAttribute('data-id');

				// Clear the caches
				delete getMigrations.cache;
				delete getCompleted.cache;

				// Delete the migration and refresh the rows
				controller.deleteMigration(id, refreshMigrations).catch(error);
			}
		}
	});

	// See if migrations are currently running and update status
	function pollMigrations() {
		controller.getStatus().then(function(status) {
			var complete          = !status || !status.pending || !status.total;
			var current_migration = document.querySelector('.current-migration');
			var migration_types   = document.querySelector('.migration-types');

			if(complete) {
				current_migration.classList.add('hidden');
				migration_types.classList.remove('hidden');

				// Stop migrations
				controller.stopMigrations().then(function() {
					// Alert any error message
					if(status && status.error) {
						alert(status.error);
						console.log(status.error);
					}

					// Hide the progress bar
					if(progress.classList.contains('pending')) {
						progress_inner.style.width = '100%';
						setTitle(cancelled ? '(Cancelled)' : '(Finished)');

						if(cancelled) {
							progress.classList.add('cancelled');
						}

						setTimeout(function() {
							progress.classList.remove('pending');
							setTitle();
						}, 3000);

						cancelled = false;
					}

					// Show the "Run Migrations" button
					run.classList.remove('hidden');
					stop.classList.add('hidden');

					// We are no longer running migrations
					running = false;

					// Clear the caches and update the UI
					delete getMigrations.cache;
					delete getCompleted.cache;
					refreshMigrations();
				}).catch(error);
			}
			else {
				// We are running migrations
				running = true;

				// Remove the cancelled class if necessary
				progress.classList.remove('cancelled');

				// Show the progress bar
				if(!progress.classList.contains('pending')) {
					progress.classList.add('pending');
				}

				// Show the "Stop Migrations" button
				stop.classList.remove('hidden');
				run.classList.add('hidden');

				// Update the progress bar and text
				var completed = status.total - status.pending;
				var ratio     = (completed + 1) / (status.total + 1);

				progress_inner.style.width = ratio * 100 + '%';
				setTitle('(' + completed + ' / ' + status.total + ')');

				if (status.current) {
					// Remove potential letters
					var migration_id = status.current.replace(/[^0-9]+$/, '');
					var jira_link    = '<a target="_blank" href="https://focussis.atlassian.net/browse/' + migration_id + '">' + status.current + "</a>";

					current_migration.querySelector('.jira-link').innerHTML = jira_link;
					current_migration.classList.remove('hidden');
					migration_types.classList.add('hidden');
				}

				setTimeout(pollMigrations, 100);
			}
		}).catch(error);
	}

	// Refresh the migration rows
	function refreshMigrations() {
		// Show the spinner
		spinner.classList.remove('hidden');

		refreshSorting(sort_priorities, sort_directions);

		getMigrationRows().then(function(rows) {
			// Sort and filter
			rows = clone(rows).sort(sort).map(map);

			//Get the migration_id and remove any possible characters then add the jira_id index for handlebars
			rows.forEach(function(row) {
				var jira = row.migration_id.replace(/[^0-9]+$/, '');
				row.jira_id = jira;
			});

			// Render to HTML
			table.tBodies[0].innerHTML = rows.map(Template.migration_row).join('');

			// Hide the spinner
			spinner.classList.add('hidden');
		}).catch(error);
	}

	// Refresh the sorting UI
	function refreshSorting(priorities, directions) {
		priorities.forEach(function(key, i) {
			var dir = directions[i];
			var el  = indexed_elements[key];

			if(dir === 0) {
				el.removeAttribute('data-sort-priority');
				el.removeAttribute('data-sort-direction');
			}
			else {
				el.setAttribute('data-sort-priority', i + 1);
				el.setAttribute('data-sort-direction', dir);
			}
		});
	}

	// Get all migrations
	function getMigrationRows() {
		var types = [];

		if(show_pending.checked) {
			types.push(getMigrations());
		}

		if(show_completed.checked) {
			types.push(getCompleted());
		}

		return Promise.all(types).then(function(results) {
			return results.reduce(function(p, c) {
				return p.concat(c);
			}, []);
		});
	}

	// Get new migrations
	function getMigrations() {
		if(!getMigrations.cache) {
			getMigrations.cache = controller.getMigrations();
		}

		return getMigrations.cache;
	}

	// Get completed migrations
	function getCompleted() {
		if(!getCompleted.cache) {
			getCompleted.cache = controller.getCompleted();
		}

		return getCompleted.cache;
	}

	// Handle errors
	function error(message) {
		alert(message);
		window.location.href = '../../';
	}

	// Set the document title
	function setTitle(title) {
		document.title = (title ? title + ' ' : '') + 'Manage Migrations';
	}

	// Utility function to clone an object
	function clone(o) {
		return JSON.parse(JSON.stringify(o));
	}

	// Check to see if migrations are in progress
	pollMigrations();
}(window));
