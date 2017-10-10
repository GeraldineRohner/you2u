$(document).ready(function() {

    function decodeEntities(encodedString) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = encodedString;
        return textArea.value;
    }

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
                url = $(location).attr('href');
                //- On lui retire -10 car on sera toujours dans l'URL Recherche. 
                urlPublic = url.slice(0,-10);
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
                            <div class="col-md-4 annonce-item">
                                <div class="col-sm-6">
                                    <img class="img-responsive" src="http://localhost/projet/you2u/public/img/manquante.png" alt="">
                                </div>
                                <div class="col-sm-6">
                                    <h4 class="titre_annonce text-left"><a href="${urlPublic}/${annonces[i].nomCategorieService}/${annonces[i].titreServiceSlug}_${annonces[i].idService}.html">` + decodeEntities(annonces[i].titreService) + `</a></h4>
                                    <p class="categorie_annonce"><em>${annonces[i].nomCategorieService}</em></p>
                                </div>
                                <div class="col-sm-12">
                                    <p>&nbsp;</p>                             
                                    <p class="annonceur">
                                        ${annonces[i].prenom} vous propose ce service pour ${annonces[i].tarifService} EUR. <br><br>
                                        <strong>Date de la Publication : </strong>${annonces[i].datePublicationService} <br>
                                        <strong>Commune : </strong> ${annonces[i].commune} <br>
                                    </p>
                                    <a href="${urlPublic}/${annonces[i].nomCategorieService}/${annonces[i].titreServiceSlug}_${annonces[i].idService}.html" class="btn btn-primary">Consulter</a>
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
