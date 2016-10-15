<?php

namespace Hawk\Plugins\HAgenda;

/**
 * This class describes the data HAgendaContact behavior.
 *
 * @package HAgenda
 */
class HAgendaContact extends Model{

	/**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = 'HAgendaContact';

	/**
     * Primary Column for the table
     *
     * @var string
     */	
	protected static $primaryColumn = "id";

    /**
     * The table fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),

        'eventId' => array(
            'type' => 'INT(11)',
        ),

        'contactId' => array(
            'type' => 'INT(11)',
        ),
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
        'eventId' => array(
            'type' => 'index',
            'fields' => array(
                'eventId'
            ),
        ),

        'contactId' => array(
            'type' => 'index',
            'fields' => array(
                'contactId'
            ),
        ),

        'agendaEventContact' => array(
            'type' => 'unique',
            'fields' => array(
            	'eventId',
                'contactId'
            ),
        ),

        'HAgendaEventId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'eventId'
            ),
            'references' => array(
                'model' => 'HAgendaEvent',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),

 		'HAgendaContactId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'contactId'
            ),
            'references' => array(
                'model' => '\Hawk\Plugins\HConnect\HContact',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),
    );

    /**
     * Constructor
     */
    public function __construct($data = array()){
        parent::__construct($data);
    }

	/**
     * Get all contacts of the event
     *
     * @param int  $eventId  The event id 
     *
     * @return array Contact's list
     */
    public static function getAllContactsByEventId($eventId){
    	$list = array();
		$contacts = array();

		if(Plugin::existAndIsActive('h-connect')){
			$listContacts = self::getListByExample(new DBExample(array(
				'eventId' => $eventId,
			)));

			// Get each contactId from each groupContact
			foreach ($listContacts as $c) {
				array_push($list, $c->contactId);
			}

			if(!empty($list))
				// Get all contacts link to this groupId
				$contacts = \Hawk\Plugins\HConnect\HContact::getListByExample(
					new DBExample(
						array(
							'id' => array(
								'$in' => $list
							)	
						) 
					)
				);
		}

		return $contacts;
    }

    /**
     * Delete all contacts of the event
     *
     * @param int  $eventId    The event id 
     */
	public static function deleteContacts($eventId){
		// start to delete all from groupId
		self::deleteByExample(new DBExample(array(
			'eventId' => $eventId,
		)));
	}

	/**
     * Save all contacts for the event
     *
     * @param int  $eventId    The event id 
     *
     * @param array  $contacts  Contact's list to save
     */
	public static function saveContacts($eventId, $contacts){

        foreach($contacts as $contact){
        	self::add(array(
				'eventId' => $eventId,
				'contactId' => $contact['id'],
			));
        }
	}
}