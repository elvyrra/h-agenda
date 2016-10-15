<?php

namespace Hawk\Plugins\Agenda;

App::router()->prefix('/h-agenda', function(){
    App::router()->auth(App::session()->isLogged(), function(){
        // Display the user's agenda
        App::router()->get('h-agenda-index','', array( 'action' => 'AgendaController.index'));

        // Print the user's agenda
        App::router()->get('h-agenda-print', '/agenda.pdf', array(
            'action' => 'AgendaController.pdf'
        ));

        // Edit event
        App::router()->any('h-agenda-edit-event', '/edit/{id}', array(
            'where' => array(
                'id' => '\d+',
            ),
            'action' => 'AgendaController.editEvent'
        ));

        // Remove an event from the user's agenda
        App::router()->delete('h-agenda-remove-event', '/delete', array(
            'action' => 'AgendaController.removeEvent'
        ));
    });
});
