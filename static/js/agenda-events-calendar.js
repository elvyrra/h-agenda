
/*global app, ko, Lang */

'use strict';

require(['app'], function() {
    $('.cal-cell').click(function(){
    	var date = this.firstElementChild.firstElementChild.getAttribute('data-cal-date');
    	app.dialog(app.getUri('h-agenda-edit-event', {id: 0}) + "?date=" + date);
    });
})();




