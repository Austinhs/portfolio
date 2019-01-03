/**
 * index.js
 * Standards scraper
 * 
 * Prerequisites:
 * - install unrtf
 * - start chrome in remote debugging mode (/path/to/chrome --remote-debugging-port=9222)
 * 
 * Usage:
 * node index.js > standards.json
 */

const CDP     = require('chrome-remote-interface');
const Browser = require('./browser');
const rtf     = require('rtf-parse');
const got     = require('got');
const hasha   = require('hasha');
const fs      = require('fs');
const path    = require('path');
const child   = require('child_process');

const str = 'JSON.stringify';
const qsa = 'document.querySelectorAll';

const toHTML = async (rtf_url) => {
	const hash      = hasha(rtf_url).substr(0, 16);
	const rtf_path  = path.resolve(`./cache/${hash}.rtf`);
	const html_path = path.resolve(`./cache/${hash}.html`);

	let html = '';

	try {
		fs.statSync(rtf_path);
	}
	catch(e) {
		const response = await got(rtf_url);
		const rtf      = response.body;

		fs.writeFileSync(rtf_path, rtf);
	}

	try {
		fs.statSync(html_path);
	}
	catch(e) {
		child.execSync(`unrtf -n --html ${rtf_path} > ${html_path}`);
	}

	return `file://${html_path}`;
}

const getFrameworkUrl = async (browser) => {
	const expr = `
		let currentFrameworkTitle = document.querySelector('.mainContent h1').innerText;
		let currentFrameworkUrl = '';
		document.querySelectorAll('.sidebarSection ul a').forEach((a) => {
			if (currentFrameworkTitle === a.innerText) {
				currentFrameworkUrl = a.href;
			}
		});
		JSON.stringify(currentFrameworkUrl);
	`;
	const url = 'http://www.fldoe.org/academics/career-adult-edu/career-tech-edu/curriculum-frameworks/';

	const frameworkUrl = await browser.eval(expr, url); // gets the url for the current year CTE frameworks
	return JSON.parse(frameworkUrl);
};

const getPrograms = async (browser) => {
	const expr = `
		${str}(Array.from(${qsa}('#newPageContent ul li a')).map((a) => {
			return {
				curriculum : a.innerText,
				url : a.href,
			};
		}));
	`;

	const url      = await getFrameworkUrl(browser);
	const programs = await browser.eval(expr, url);

	return JSON.parse(programs);
};

const getCategories = async (browser, url) => {
	const expr = `
		//hack: add missing course links to the page so the scraper picks them up
		if(location.href.endsWith("/info-technology.stml")) {
			let year = location.href.match(/[0-9]{4}-[0-9]{2}/)[0];
			let information_technology_assistant = "http://www.fldoe.org/core/fileparse.php/9943/urlt/ITA_"+year+".rtf"; // OTA0040 standards rtf file
			Array.from(document.querySelectorAll('.mainContent .dynamicDiv h2 + ul, .mainContent .dynamicDiv h3 + ul')).forEach((ul) => {
				const category = ul.previousElementSibling.innerText;
				if(category === "PSAV Programs") {
					const a = document.createElement('a');
					a.innerText = "Information Technology Assistant (OTA0040)";
					a.href = information_technology_assistant;
					ul.appendChild(a);
				}
			});
		}
		else if(location.href.endsWith("/health-science.stml")) {
			let health_science_core;
			Array.from(document.querySelectorAll('.mainContent .dynamicDiv h3 + p')).forEach((p) => {
				const category = p.previousElementSibling.innerText;
				if(category === "Health Science Careers Core- PSAV and College") {
					health_science_core = p.querySelector('a').href; // HSC0003 standards rtf file
				}
			});
			Array.from(document.querySelectorAll('.mainContent .dynamicDiv h3 + ul')).forEach((ul) => {
				const category = ul.previousElementSibling.innerText;
				if(category === "PSAV Programs") {
					const a = document.createElement('a');
					a.innerText = "Basic Healthcare Worker (HSC0003)";
					a.href = health_science_core;
					ul.appendChild(a);
				}
			});
		}

		//HACK - adds deleted programs from 2017-2018
		//These can be removed in the future once districts are no longer teaching them.
		if(location.href.endsWith("/manufacturing.stml")) {
			Array.from(document.querySelectorAll('.mainContent .dynamicDiv h2 + ul, .mainContent .dynamicDiv h3 + ul')).forEach((ul) => {
				const category = ul.previousElementSibling.innerText;
				if(category === "PSAV Programs") {
					const a1 = document.createElement('a');
					a1.innerText = "Major Appliance and Refrigeration Repair (I470106)";
					a1.href = "http://www.fldoe.org/core/fileparse.php/18404/urlt/I470106-1718.rtf";
					ul.appendChild(a1);

					const a2 = document.createElement('a');
					a2.innerText = "Automation and Production Technology (J100100)";
					a2.href = "http://www.fldoe.org/core/fileparse.php/18404/urlt/J100100-1718.rtf";
					ul.appendChild(a2);
				}
			});
		}
		if(location.href.endsWith("/transportation-distribution-logistics.stml")) {
			Array.from(document.querySelectorAll('.mainContent .dynamicDiv h2 + ul, .mainContent .dynamicDiv h3 + ul')).forEach((ul) => {
				const category = ul.previousElementSibling.innerText;
				if(category === "PSAV Programs") {
					const a1 = document.createElement('a');
					a1.innerText = "Automotive Collision Repair and Refinishing (I470603)";
					a1.href = "http://www.fldoe.org/core/fileparse.php/18404/urlt/I470603-1718.rtf";
					ul.appendChild(a1);

					const a2 = document.createElement('a');
					a2.innerText = "Heavy Equipment Operation (I490202)";
					a2.href = "http://www.fldoe.org/core/fileparse.php/18404/urlt/I490202-1718.rtf";
					ul.appendChild(a2);
				}
			});
		}

		${str}(Array.from(${qsa}('.mainContent .dynamicDiv h2 + ul, .mainContent .dynamicDiv h3 + ul')).map((ul) => {
			const category = ul.previousElementSibling.innerText;
			const a        = Array.from(ul.querySelectorAll('a:not(:empty)'));

			console.log(a.innerText);
			
			const courses = a.map((a) => {
				const url     = a.href;
				const matches = a.innerText.match(/(.+?)\\s*\\(([^\\)]+)\\)$/) || [];
				const title   = matches[1] || 'Unknown Title';
				const num     = matches[2] || 'Unknown Course Number';

				let parent = null;

				if(a.closest('ul') !== ul) {
					const parent_li = a.closest('ul').closest('li');
					const parent_a  = parent_li && parent_li.firstElementChild;
					const matches   = parent_a.innerText.match(/(.+?)\\s*\\(([^\\)]+)\\)$/) || [];

					parent = matches[2] || 'Unknown Parent';
				}

				return { url, title, num, parent};
			});

			return { category, courses };
		}));
	`;

	const categories_str = await browser.eval(expr, url);
	const categories     = JSON.parse(categories_str);

	for(let i = 0; i < categories.length; i++) {
		const courses = categories[i].courses;

		for(let z = 0; z < courses.length; z++) {
			const course = courses[z];
			const data   = await getCourse(browser, course.url);

			Object.assign(course, data);
		}
	}

	return categories;
};

const getCourse = async (browser, url) => {
	if(!url.endsWith('.rtf')) {
		return { url, manual : true };
	}

	url = await toHTML(url);

	const expr = `
		(function() {
			// First, set up some anchors
			const anchors = {};

			Array.from(${qsa}('center')).forEach((el) => {
				if(el.innerText.match(/Curriculum\\sFramework/)) {
					anchors.framework = el;
				}
				else if(el.innerText.match(/Student\\sPerformance\\sStandards/)) {
					if(!anchors.standards) anchors.standards = el;
				}
			});

			// a is before b
			const before = (a, b) => {
				return !!(a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING);
			};

			// a is after b
			const after = (a, b) => {
				return !!(a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_PRECEDING);
			};

			// Populate the course information
			const course = {};

			let section = {
				standards : []
			};

			let standards_table_count = 0;
			Array.from(${qsa}('table')).map((table, i) => {
				// Properties table
				if(i === 0) {
					let skip = false;

					if(anchors.framework) {
						skip = skip || !after(table, anchors.framework);
					}

					if(anchors.standards) {
						skip = skip || !before(table, anchors.standards);
					}

					if(!skip) {
						const properties = {};
						const rows       = Array.from(table.querySelectorAll('tr'));

						rows.forEach((row) => {
							const key_el = row.firstElementChild;

							if(!key_el) {
								return;
							}

							const value_el = key_el.nextElementSibling;

							if(!value_el) {
								return;
							}

							const key   = key_el.innerText.trim();
							const value = value_el.innerText.trim();

							if(key.length && value.length) {
								properties[key] = value;
							}
						});

						course.properties = properties;
					}
				}

				//program structure table
				if((i === 1) && !anchors.standards) {
					const structures = {};

					let temps = Array.from(table.querySelectorAll('tr'));

					let first;
					let second;

					if(temps) {
						first = temps.firstElementChild;
					}
					if(first) {
						second = first.nextElementSibling;
					}

					if(first && second) {
						const one = first.innerText.trim();
						const two = second.innerText.trim();

						temps.forEach((temp) => {
							if(first.length && second.length) {
									structures[first] = second;
							}

						});
					}

					course.structures = structures;
				}

				// Standards tables
				if(anchors.standards && after(table, anchors.standards)) {
					standards_table_count++;
					const cells = Array.from(table.querySelectorAll('td'));

					let section = {
						standards : []
					};

					cells.forEach((cell) => {
						const matches  = cell.innerText.match(/^(\\d+\\.\\d+\\S*?)[\\t\\u00A0 ]+(\\S.+)$/ms); //"01.01 Lorem ipsum dolor sit amet."
						const matches2 = cell.innerText.match(/^([a-z]\\.)[\\t\\u00A0 ]+(\\S.+)$/ms);         //"a. Lorem ipsum dolor sit amet."

						if(!section.tags) {
							let td   = table.querySelector('tr').firstElementChild;
							let text = td.innerText.trim().split('\\n');

							if(td.innerText.trim() === "CTE Standards and Benchmarks") {
								// course information is not part of the table
								var course_nums = [];
								Array.from(document.querySelectorAll('b')).forEach((el) => {
									if(el.innerText.match(/Course\\sNumber:\\s*(.+)/)) {
										course_nums.push(el);
									}
								});

								if(course_nums[standards_table_count - 1]) {
									text = course_nums[standards_table_count - 1].innerText.trim().split('\\n');
								}
							}

							section.tags = text.map((tag) => {
								const match = tag.match(/^(.+):\\s*(.+)$/);

								if(match) {
									return {
										key   : match[1].trim(),
										value : match[2].trim(),
									};
								}

								return tag.trim();
							});
						}
						else if (cell.innerText.search(/Course\\sNumber:\\s*(.+)/) !== -1) {
							text = cell.innerText.trim().split('\\n');

							if(section.standards.length) {
								if(!course.standards) {
									course.standards = [];
								}

								course.standards.push(section);
							}

							section = {
								standards : []
							};

							section.tags = text.map((tag) => {
								const match = tag.match(/^(.+):\\s*(.+)$/);

								if(match) {
									return {
										key   : match[1].trim(),
										value : match[2].trim(),
									};
								}

								return tag.trim();
							});
						}

						if(matches && matches[1] && matches[2]) {
							section.standards.push({
								id    : matches[1].trim(),
								title : matches[2].trim().substring(0, 1000),
							});
						}
						else if(matches2 && matches2[1] && matches2[2]) {
							section.standards.push({
								id    : matches2[1].trim(),
								title : matches2[2].trim().substring(0, 1000),
							});
						}
					});

					if(section.standards.length) {
						if(!course.standards) {
							course.standards = [];
						}

						course.standards.push(section);
					}
				}
			});

			return ${str}(course);
		}());
	`;

	const course_str = await browser.eval(expr, url);
	const course     = JSON.parse(course_str);



	return course;
};

const getData = async (browser) => {
	let programs = await getPrograms(browser);

	for(let i = 0; i < programs.length; i++) {
		const program = programs[i];

		program.categories = await getCategories(browser, program.url);
	}

	return programs;
};

CDP((client) => {
	const browser = new Browser(client);
	const data    = getData(browser);

	try {
		fs.accessSync('./cache');
	}
	catch (e) {
		fs.mkdirSync('./cache');
	}

	data
		.then((data) => {
			console.log(JSON.stringify(data, null, '  '));
			browser.close();
		})
		.catch((e) => {
			console.log(e);
			browser.close();
		});
}).on('error', (error) => {
	console.log(error);
});
