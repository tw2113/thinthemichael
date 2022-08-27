document.getElementById("instago_dynamic_url").onclick = function() {
	let text = this.innerText.trim();
	let success = document.getElementById('copy_result');
	navigator.clipboard.writeText(text)
		.then(() => {
			console.log('URL copied successfully');
			success.style.display = 'inline';
		})
		.catch(err => {
			console.log('Error in copying text: ', err);
		});
}
