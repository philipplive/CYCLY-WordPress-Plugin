'use strict';

class HfCore {
	static request(method = '', parameters = {}) {
		return fetch('/wp-json/' + method, {
			method: 'POST', // or 'PUT'
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(parameters),
		}).then(response => response.text());
	}
}
