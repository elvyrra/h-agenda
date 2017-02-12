
/*global app, ko, Lang */

'use strict';

require(['app', 'jquery'], function() {
    $('.cal-month-day').click(function(){
    	var date = this.firstElementChild.getAttribute('data-cal-date');
    	app.dialog(app.getUri('h-agenda-edit-event', {id: 0}) + "?date=" + date);
    });
})();




