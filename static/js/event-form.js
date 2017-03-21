/*global app, ko, Lang */

'use strict';

require(['app', 'jquery', 'lang', 'emv'], function() {

    class ModelContact extends EMV {
        
        /**
         * Constructor
         * @param  {Object} data The initial data of the element
         */
        constructor(data) {

            super({
                data : data
            });
        }

        /**
         * This method is used to add item selected into the group
         * @param   {item} contact to add to the group
         * @returns {bool}                True if the element is a child, else False
         */
        onContactChosen(contact) {
            if (contact) {
                this.addContact(contact);
                this.search = '';
            }
        }

        addContact(contact) {
            if (!contact.id) {
                return;
            }

            var id = parseInt(contact.id),
            existing = this.contacts.filter(function(c) {
                return c.id === id;
            });

            if(existing.length) {
                alert(Lang.get('h-connect.contact-already-in-group'));
                return;
            }

            this.contacts.push({
                lastName  : contact.lastName,
                firstName : contact.firstName,
                id        : id,
                label : contact.label
            });
        }

        removeContact(contact) {
            this.contacts.splice(this.contacts.indexOf(contact), 1);
        }
    }

    var form = app.forms['h-agenda-event-form'];

    const emv = new ModelContact({
        search : form.inputs.search.val(),
        contacts : JSON.parse(form.inputs.contacts.val()),
        startDate : form.inputs.startDate.val(),
        endDate : form.inputs.endDate.val(),
        startTime : form.inputs.startTime.val(),
        endTime : form.inputs.endTime.val()
    });
    /*
    emv.$watch(['startDate'], function(value, oldValue) {
        try{
            let s = Date.parse(value);
            let e = Date.parse(this.endDate);

            if(s.getTime() > e.getTime()){
                this.endDate = value;
            }
        }catch(e){
            console.log(e);
        }
    });*/

    emv.$apply(form.node.get(0));
});
