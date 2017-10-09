$(document).ready(function() {

   $('form').submit(function(e){

        e.preventDefault();

        console.log('Categorie : ' + $('#form_categorie').val())
        console.log('localisation : ' + $('#form_localisation').val())

        $.ajax(
        {
            url: 'recherche',
            type: 'POST',
            data: {
                categorie:  $('#form_categorie').val(),
                localisation: $('#form_localisation').val()
            },
            dataType: 'json',
            timeout: 4000,
            success: function(data)
            {

                $('.phrase-count-result').html('<b class="nbAnnoncesPubliees">'+ data.nbAnnoncesPubliees + '</b> annonces correspondent à votre recherche');
                $('.affinerRecherche').html('<input class="form-control" type="text" placeholder="Affinez votre recherche" required="">');

                // -- Récupération des annonces depuis JSON
                let annonces    = data.annoncesPubliees;
                // -- On compte le nombre d'annonce pour les parcourir
                let nbAnnonces  = annonces.length;

                // -- On cache temporairement les annonces avec une animation
                $('.annonces').slideUp(function() {
                    // -- On supprime les annonces déjà présente
                    $(this).html('');

                    // -- On parcourir notre résultat et on l'insere sur la page puis le resultat reapparait.
                    for(let i = 0 ; i < nbAnnonces ; i++) {

                        $('.annonces').append(`
                            <div class="col-md-6 ann1">
                                <div class="col-md-2 col-md-push-2">
                                    <div class="imgprofil">
                                        <img src="">
                                    </div>
                                </div>
                                <div class="col-md-8 col-md-push-2">
                                    <h2 class="titre_annonce"><a href="#">${annonces[i].titreService}</a></h2>
                                    <p class="categorie_annonce">${annonces[i].nomCategorieService}</p>
                                    <p class="annonceur">${annonces[i].prenom} ${annonces[i].nom}</p>
                                    <p class="tarif_annonce">${annonces[i].tarifService} EUR</p>
                                    <p class="date_publication_annonce">${annonces[i].datePublicationService}</p>
                                    <p class="localisation_annonce">${annonces[i].commune}</p>
                                </div>
                            </div>                    
                        `)
                    }
                }).slideDown();



            },
            error: function(data, errors)
            {
                console.log("Une erreur s'est produite : " + errors);
                $('#annonces').replaceWith("<h2 class='alert alert-danger text-center'> Une erreur s'est produite lors du chargement. </h2>");
            }
        }); // Fin de la requête ajax



   }); // Fin de l'événement d'écoute sur submit

}); // fin de la fonction "ready"
