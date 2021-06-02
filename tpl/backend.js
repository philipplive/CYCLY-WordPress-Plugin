function updateCycly(){
	var dialog = document.querySelector('.cycly-update-dialog');

	HfCore.request('cycly-connector/update').then(data => {
		dialog.classList.remove('notice-none');
		dialog.classList.add('notice-success');
		dialog.innerHTML = 'Abgeschlossen :)';

		location.reload();
	});

	dialog.innerHTML = 'Bitte warten. Das Update wird ausgeführt und die Seite dann automatisch neu geladen.';
	dialog.classList.remove('notice-error');
	dialog.classList.add('notice-none');
}