<?php

namespace Hawk\Plugins\HAgenda;

/**
 * This class describes the data HAgendaEvent behavior.
 *
 * @package HAgenda
 */
class HAgendaEvent extends Model{

    /**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = 'HAgendaEvent';

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

        'personId' => array(
            'type' => 'INT(11)',
        ),

        'userId' => array(
            'type' => 'INT(11)',
        ),

        'title' => array(
            'type' => 'VARCHAR(64)',
        ),
        
        'description' => array(
            'type' => 'TEXT',
        ),

        'place' => array(
            'type' => 'VARCHAR(64)',
        ),

        'startDate' => array(
            'type' => 'DATE',
        ),

        'endDate' => array(
            'type' => 'DATE',
        ),

        'startTime' => array(
            'type' => 'time',
        ),
        
        'stopTime' => array(
            'type' => 'time',
        ),

        'ctime' => array(
            'type' => 'INT(11)',
        ),

        'mtime' => array(
            'type' => 'INT(11)',
        ),
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
        'HAgendaEventId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'userId'
            ),
            'references' => array(
                'model' => 'User',
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
     * Get the number of day between today and the event start date
     *=
     * @return int  day left before event
     */
    public function getDayLeft() {
        $now = new \DateTime(date('Y-m-d 00:00:00'));
        $start = new \DateTime($this->startDate);

        $interval = $start->diff($now);

        if($interval->format('%R%') === '+' || $interval->format('%R%a') === '-0') {
            return 0;
        }

        return $interval->days;
    }

    /**
     * Get the day name on 3 cars of the start date of the event
     *
     * @return string
     */
    public function getDay(){
        $start = new \DateTime($this->startDate);
        $dayNumber = $start->format('N');

        return lang::get('h-agenda.day-' . $dayNumber . '-3cars');
    }

    /**
     * Get the day name of the start date of the event
     *
     * @return string
     */
    public function getDayFull(){
        $start = new \DateTime($this->startDate);
        $dayNumber = $start->format('N');

        return lang::get('h-agenda.day-' . $dayNumber . '-full');
    }
    
    /**
     * Get the day name of the end date of the event
     *
     * @return string
     */
    public function getEndDay(){
        $start = new \DateTime($this->endDate);
        $dayNumber = $start->format('N');

        return lang::get('h-agenda.day-' . $dayNumber . '-3cars');
    }

    /**
     * Get full description of the event
     *
     * @return string
     */
    public function getFullDescription(){
        $str = '<div><h3>' . $this->title . '</h3></br>';

        //$str = '<div><h4>' . lang::get('h-agenda.description-label') . '</h4>' . $this->description . '</br>';

        $str .= '<h4>' . lang::get('h-agenda.description-label') . '</h4>' . $this->description . '</br>';
        
        $contacts = HAgendaContact::getAllContactsByEventId($this->id);

        if(!empty($contacts))
            $str .= '<h4>'. lang::get('h-agenda.contacts-label') . ' </h4>';

        foreach ($contacts as $key => $contact) {
            $str .= $contact->label . '</br>';
        }

        $str .= '</div>';

        return $str;
    }

    /**
     * Get full descirption of the vent with format of the calendar
     *
     * @return string
     */
    public function getFullDescriptionCalendar(){
        $str = $this->title . ' - ' . date('H:i', strtotime($this->startTime));

        $str .= $this->getFullDescription();

        return $str;
    }

    /**
     * Check if the event is finished
     *
     * @return boolean
     */
    public function isFinished(){
        return strtotime($this->endDate) > time();
    }

    /**
     * Check if the event is in progress
     *
     * @return boolean
     */
    public function isInProgress(){
        return $this->hasStarted() && ! $this->isFinished();
    }

    /**
     * Check if the event has started
     *
     * @return boolean
     */
    public function hasStarted(){
        return strtotime($this->startDate) <= time();
    }
}