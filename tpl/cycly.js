class BikeModule {
	constructor(element) {
		// Elemente
		this.element = element;
		this.catagorieFilterElement = this.element.querySelector('select[name=categorie]');
		this.bikes = this.element.querySelectorAll('.items .item');

		// Dialog
		this.dialog = new Dialog(this.element.querySelector('.dialog-wrapper'));

		// Einstellungen
		this.branch = element.dataset.branch;

		// Start
		this.init();
	}

	init() {
		// Filter Events
		this.catagorieFilterElement.addEventListener('change', () => {
			this.filterChangeEvent();
		});

		// Click Events
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
	}

	// Event bei Filteränderung
	filterChangeEvent() {
		this.bikes.forEach((item) => {
			item.classList.add('fadeout');
		});

		window.setTimeout(() => {
			let categorieId = this.catagorieFilterElement.value;

			// Bikes Filtern
			this.bikes.forEach((item) => {
				if (item.dataset.categoryid == categorieId || categorieId == 0)
					item.style.display = 'block';
				else
					item.style.display = 'none';
			});

			window.setTimeout(() => {
				this.bikes.forEach((item) => {
					item.classList.remove('fadeout');
				});
			}, 10);
		}, 200);
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

	setContent(content){
		this.container.innerHTML = content;
		this.element.classList.add('show');
		return this;
	}

	removeContent(){
		this.container.innerHTML = '';
		this.element.classList.remove('show');
	}

	show(){
		this.wrapper.classList.add('show');
	}

	hide(){
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