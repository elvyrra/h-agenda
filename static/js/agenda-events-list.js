/* global require, $, app, Lang */



'use strict';

require(['app', 'jquery', 'lang', 'emv'], function() {
    var list = app.lists['h-agenda-events-list'];
    
    $('#delete-agenda-events-button').click(function() {
        if (confirm(Lang.get('h-agenda.delete-event-confirmation'))) {
            var selectedLines = [];

            list.node.find('.list-select-line:checked').each(function() {
                selectedLines.push($(this).attr('value'));
            });

            $.ajax({
                url : app.getUri('h-agenda-remove-event'),
                method : 'DELETE',
                data : JSON.stringify({
                    events : selectedLines
                }),
                contentType : 'application/json'
            })

            .done(function() {
                app.load(app.getUri('h-agenda-index'));
            });
        }
    });
    
    $('#print-agenda-list-button').click(function() {
        var selectedLines = [];

        $(list.node()).find('.list-select-line:checked').each(function() {
            selectedLines.push($(this).attr('value'));
        });

        window.open(app.getUri('h-agenda-print') + (selectedLines.length ? '?events=' + selectedLines.join(',') : ''));
    });

    //$('#h-agenda-events-list').popover({container: 'body'});
});