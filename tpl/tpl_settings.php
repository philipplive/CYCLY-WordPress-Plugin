<div class="wrap">
	<h2>CYCLY Plugin</h2>
	<form action="options.php" method="post">
		<div class="card">
			<h2>Einleitung</h2>
			<p>Folgende Module können auf der Website eingebunden werden:</p>
			<ul>
				<li>- Veloliste direk in einem Beitrag: [show_bikes branch="1"]</li>
				<li>- Öffnungszeiten-Widget im Widget Bereich</li>
			</ul>
			<h2>Wichtig</h2>
			<p>Für den Betrieb müssen gültige API-Zugangsdaten hinterlegt werden. Diese finden Sie in Ihrem <a target="_blank" href="https://cycly.ch/tour/">Cycly Kundencenter</a>.</p>
		</div>
		<?php settings_fields('cylcy_settings'); ?>
		<?php do_settings_sections('cylcy_settings'); ?>

		<input name="Submit" type="submit"
			   value="Einstellungen speichern"
			   class="button button-primary"/>
	</form>
	<?php do_settings_sections('cylcy_debug'); ?>
</div>