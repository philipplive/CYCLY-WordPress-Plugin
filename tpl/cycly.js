class BikeModule {
	constructor(element) {
		// Elemente
		this.element = element;
		this.bikesContainer = this.element.querySelector('.items');
		this.bikes = [].slice.call(this.element.querySelectorAll('.items .item'));
		this.moreButton = this.element.querySelector('.moreButton')

		l(this.bikes.length);

		// Sortierelement
		this.sortFilterElement = this.element.querySelector('select[name=sort]');

		// Filter
		this.filters = [
			new Filter(this.element, 'categoryid'),
			new Filter(this.element, 'manufacturerkey'),
			new Filter(this.element, 'typeid')
		];

		// Dialog
		this.dialog = new Dialog(this.element.querySelector('.dialog-wrapper'));

		// Sonstiges
		this.showMore = false;
		this.showMoreLimit = 10;

		// Start
		this.init();

		// Filter
		this.SORT = {
			PRICE_DESC: 1, // Absteigend
			PRICE_ASC: 2, // Aufsteigend
			YEAR_DESC: 3,
			YEAR_ASC: 4,
		};

		this.sort = this.sortFilterElement.value;
		this.categorieId = 0;
		this.manufacturer = 0;
	}

	init() {
		// Sortierevent
		this.sortFilterElement.addEventListener('change', () => {
			this.sort = this.sortFilterElement.value;
			this.filterChangeEvent();
		});

		// Filterevent
		this.filters.forEach((item) => {
			item.setChangeEvent(() => {
				this.filterChangeEvent();
			});
		});

		// Anzeigen
		this.filterChangeEvent();
		this.bikesContainer.removeAttribute("style");

		// Click Events Bikes
		this.bikes.forEach((item) => {
				item.querySelector('a').addEventListener('click', (event) => {
					this.dialog.show();

					HfCore.request('cycly-connector/bike/' + item.querySelector('a').dataset.id)
						.then(data => {
							this.dialog.setContent(data);
						})
						.catch(error => {
							console.error('Error:', error);
						});
				});
			}
		);

		// Click Event Mehr Button
		this.moreButton.addEventListener('click', (event) => {
			this.pressMoreButtonEvent();
		});
	}

	// Mehr Button Event
	pressMoreButtonEvent() {
		this.hideMoreButton();
		this.filter();
	}

	// Mehr button ausblenden
	hideMoreButton() {
		this.showMore = true;
		this.moreButton.style.display = 'none';
	}

	// Mehr Button anzeigen
	showMoreButton() {
		this.showMore = false;
		this.moreButton.style.display = 'inline-block';
	}

	// Event bei Filteränderung
	filterChangeEvent() {
		this.showMore = false;

		this.bikesContainer.classList.add('fadeout');

		window.setTimeout(() => {
			this.filter();

			window.setTimeout(() => {
				this.bikesContainer.classList.remove('fadeout');
			}, 10);
		}, 200);
	}

	// Filter anwenden und Bikeliste neu zeichnen
	filter(categorieId) {
		let count = 0;
		let countOffset = 0;

		this.bikesContainer.innerHTML = '';

		// Sortiere alle Elemente
		this.bikes.sort((a, b) => {
			let result = 0;
			let val1, val2;

			if (this.sort == this.SORT.YEAR_ASC || this.sort == this.SORT.YEAR_DESC) {
				val1 = parseInt(a.dataset.year);
				val2 = parseInt(b.dataset.year);
			}

			if (this.sort == this.SORT.PRICE_ASC || this.sort == this.SORT.PRICE_DESC) {
				val1 = parseInt(a.dataset.price);
				val2 = parseInt(b.dataset.price);
			}

			if (val1 > val2)
				result = 1;
			else if (val1 == val2)
				result = 0;
			else
				result = -1;

			// Reihenfolge umkehren
			if (this.sort % 2)
				result *= -1;

			return result;
		});

		// Filter (anzeigen/ausblenden)
		this.bikes.forEach((item) => {
				let show = true;

				// Filter
				this.filters.forEach((filter) => {
					if (!filter.compare(item.dataset))
						show = false;
				})

				// Mehr Button berücksichtigen
				if (!this.showMore && count >= this.showMoreLimit) {
					show = false;
					countOffset++;
				}

				// Hinzufügen
				if (show) {
					this.bikesContainer.appendChild(item);
					count++;
				}
			}
		);

		// Mehr-Button
		if (countOffset)
			this.showMoreButton();
		else
			this.hideMoreButton();

		// Empty-Message
		if(!count) {
			let msg = document.createElement('div');
			msg.classList.add('empty');
			msg.innerHTML = 'Leider wurden keine Fahrzeuge gefunden';
			this.bikesContainer.appendChild(msg);
		}
	}
}

// Popup Dialog für Bikeanzeige
class Dialog {
	constructor(element) {
		this.wrapper = element;
		this.element = element.querySelector('.dialog');
		this.container = element.querySelector('.dialog-container');


		element.querySelector('.button-close').addEventListener('click', e => {
			e.preventDefault();
			this.hide();
		});

		this.element.addEventListener('click', e => {
			e.preventDefault();
			e.stopPropagation();
		});

		this.wrapper.addEventListener('click', e => {
			e.preventDefault();
			this.hide();
		});
	}

	setContent(content) {
		this.container.innerHTML = content;
		this.element.classList.add('show');
		return this;
	}

	removeContent() {
		this.container.innerHTML = '';
		this.element.classList.remove('show');
	}

	show() {
		this.wrapper.classList.add('show');
	}

	hide() {
		this.removeContent();
		this.wrapper.classList.remove('show');
	}
}

// Filter
class Filter {
	constructor(baseElement, name) {
		this.element = baseElement.querySelector('select[name=' + name + ']');
		this.name = name;
	}

	// Filter vorhanden?
	isEnabled() {
		return this.element ? true : false;
	}

	setChangeEvent(callback) {
		if (this.isEnabled())
			this.element.addEventListener('change', callback);
	}

	// Aktuell gewählter Wert zurückgeben (Alle = 0)
	getValue() {
		if (this.isEnabled())
			return this.element.value;

		return 0;
	}

	// Dataset vergleichen mit dem Filter
	compare(dataset) {
		if(this.getValue() == 0)
			return true;

		return dataset[this.name] == this.getValue();
	}
}

// Start Event
document.addEventListener("DOMContentLoaded", function () {
	// Bikeanzeige
	document.querySelectorAll('.cycly-vehicles').forEach((item) => {
		new BikeModule(item);
	});
});

// Debug
function l(log) {
	console.log(log);
}