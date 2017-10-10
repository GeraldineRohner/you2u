$(function(){

	var choixChat = $('#choice');
	var raisonAdoption = $('#reason');
	var errors = false;

	// Soumission du formulaire
	$('#requestCat').on('submit', function(e){
		e.preventDefault(); // On empêche l'envoi du formulaire

		// On vérifie la longueur de la valeur sélectionnée dans le select
		// Les classes .has-error et .has-success proviennent de bootstrap et doivent être appliqué sur la classe parente .form-group
		if(choixChat.val().length == 0){
			choixChat.parent().addClass('has-error');
			errors = true;
		}
		else {
			choixChat.parent().addClass('has-success');
		}

		// On vérifie la longueur du textearea (minimum 15 caractères)
		if(raisonAdoption.val().length < 15){
			raisonAdoption.parent().addClass('has-error');
			errors = true;
		}
		else {
			raisonAdoption.parent().addClass('has-success');
		}

		if(errors === false){
			$(this).replaceWith('<div class="alert alert-success">Votre demande a bien été envoyée ! Nous vous répondrons dans les meilleurs délais.</div>');
		}
	});


	// On retire les classes .has-success ou .has-error dès que les champs changent
	choixChat.on('change', function(e){
		$(this).parent().removeClass('has-success has-error');
		errors = false;
	});

	raisonAdoption.on('change', function(e){
		$(this).parent().removeClass('has-success has-error');
		errors = false;
	});

});