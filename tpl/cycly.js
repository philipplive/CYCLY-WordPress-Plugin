class BikeModule {
	constructor(element) {
		// Elemente
		this.element = element;
		this.catagorieFilterElement = this.element.querySelector('select[name=categorie]');
		this.bikes = this.element.querySelectorAll('.items .item');
		this.moreButton = this.element.querySelector('.moreButton')

		// Dialog
		this.dialog = new Dialog(this.element.querySelector('.dialog-wrapper'));

		// Einstellungen
		this.branch = element.dataset.branch;

		// Sonstiges
		this.showMore = false;
		this.showMoreLimit = 10;

		// Start
		this.init();

		// Filter
		this.categorieId = 0;
	}

	init() {
		// Filter Events
		this.catagorieFilterElement.addEventListener('change', () => {
			this.categorieId = this.catagorieFilterElement.value;
			this.filterChangeEvent();
		});

		// Anzeigen
		this.filterChangeEvent();

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
		console.log('hide');
	}

	// Mehr Button anzeigen
	showMoreButton() {
		this.showMore = false;
		this.moreButton.style.display = 'inline-block';
		console.log('show');
	}

	// Event bei Filteränderung
	filterChangeEvent() {
		this.showMore = false;

		this.bikes.forEach((item) => {
			item.classList.add('fadeout');
		});

		window.setTimeout(() => {
			this.filter();

			window.setTimeout(() => {
				this.bikes.forEach((item) => {
					item.classList.remove('fadeout');
				});
			}, 10);
		}, 200);
	}

	// Filter anwenden
	filter(categorieId) {
		let count = 0;
		let countOffset = 0;

		this.bikes.forEach((item) => {
				let show = false;

				// Categorie Filter
				if (item.dataset.categoryid == this.categorieId || this.categorieId == 0)
					show = true;

				// Mehr Button berücksichtigen
				if (!this.showMore && count >= this.showMoreLimit) {
					show = false;
					countOffset++;
				}

				// Anzeigen/Ausblenden
				if (show) {
					item.style.display = 'block';
					count++;
				} else
					item.style.display = 'none';
			}
		);

		if (countOffset)
			this.showMoreButton();
		else
			this.hideMoreButton();
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

// Start Event
document.addEventListener("DOMContentLoaded", function () {
	// Bikeanzeige
	let bikeElement = document.getElementById('cycly-bikes');

	if (bikeElement)
		new BikeModule(bikeElement);
});