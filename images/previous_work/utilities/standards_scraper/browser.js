const EventEmitter = require('events').EventEmitter;

class Browser extends EventEmitter {
	constructor(client) {
		super();

		Object.assign(this, client, { client });

		this.ready = Promise.all([
			this.Page.enable(),
		]);

		this.Page.loadEventFired(() => this.emit('load'));
	}

	go(settings) {
		if(typeof settings === 'string') {
			settings = { url : settings };
		}

		return this.ready.then(() => {
			return new Promise((resolve, reject) => {
				this.once('load', resolve);
				this.Page.navigate(settings);
			});
		});
	}

	eval(expression, url) {
		const loaded = url ? this.go(url) : Promise.resolve();

		return loaded.then(() => {
			return this.Runtime
				.evaluate({ expression })
				.then(({ result, error }) => {
					if(!result.value || error) {
						console.log(result, error);
					}

					return result.value;
				});
		});
	}

	close() {
		this.client.close();
	}
}

module.exports = Browser;
