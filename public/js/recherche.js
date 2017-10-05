$(document).ready(function() {

    /*function deleteErrors(){
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        $('.has-success').removeClass('has-success');
    }*/

   $('form').submit(function(e){

        e.preventDefault();
        //deleteErrors();

        /*$('select#categorie-search').val();
        $('input#localisation-search').val();*/

        $.ajax(
        {
            url: 'recherche',
            type: 'POST',
            data: {
                categorie:  $('#categorie-search').val(),
                localisation: $('#localisation-search').val()
            },
            dataType: 'JSON',
            timeout: 4000,
            success: function(data)
            {
                console.log(data);
                $('#count-search-result').text(data.nbAnnoncesPubliees);
            },
            error: function(data, errors)
            {
                console.log("Une erreur s'est produite : " + errors);
                $('#annonces').replaceWith("<h2 class='alert alert-danger text-center'> Une erreur s'est produite lors du chargement. </h2>");
            }
        }); // Fin de la requête ajax

   }); // Fin de l'événement d'écoute sur submit

}); // fin de la fonction "ready"
