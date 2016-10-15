<?php

/**
 * AgendaController.php
 */

namespace Hawk\Plugins\HAgenda;

class AgendaController extends Controller{

    /**
     * Display the user agenda
     */
    public function index(){
        if(App::request()->getParams('view')) {
            App::session()->getUser()->setOption($this->_plugin . '.agenda-view', App::request()->getParams('view'));
        }

        if(App::session()->getUser()->getOptions($this->_plugin . '.agenda-view') === 'calendar') {
            $view = $this->calendarView();
        }
        else{
            $view = $this->listView();
        }

        return $view;
    }

    /**
     * Display in list
     */
    public function listView(){
        $list = new ItemList(array(
            'id' => 'h-agenda-events-list',
            'model' => 'HAgendaEvent',
            'reference' => 'id',
            'action' => App::router()->getUri('h-agenda-index'),
            'filter' => array(
                'userId = :userId AND startDate >= CURDATE()',
                array(
                    'userId' => App::session()->getUser()->id,
                ),
            ),
            'sorts' => array(
                'startDate' => DB::SORT_ASC,
                'startTime' => DB::SORT_ASC
            ),
            'controls' => array(
                /*
                'print' => array(
                    'icon' => 'print',
                    'id' => 'print-agenda-list-button'
                ),
                */

                'switch' => array(
                    'icon' => 'calendar',
                    'class' => 'btn-info',
                    'title' => Lang::get($this->_plugin . '.calendar-view-btn'),
                    'label' => Lang::get($this->_plugin . '.calendar-view-btn'),
                    'onclick' => 'app.load(app.getUri("h-agenda-index") + "?view=calendar")',
                ),

                'plus' => array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.btn-add-event-label'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('h-agenda-edit-event', array('id' => 0)),
                    'target' => 'dialog'
                ),

                'delete' => array(
                    'icon' => 'trash',
                    'class' => 'btn-danger',
                    'id' => 'delete-agenda-events-button',
                    'label' => Lang::get('main.delete-button'),
                    'ko-enable' => '!selection.none()',
                ),

            ),
            'resultTpl' => $this->getPlugin()->getView('agenda-events-list.tpl'),
            'selectableLines' => true,
            'fields' => array(

                'actions' => array(
                    'independant' => true,
                    'search' => false,
                    'sort' => false,
                    'display' => function($value, $field, $event){

                        return Icon::make(array(
                            'icon' => 'wrench',
                            'size' => 'lg',
                            'href' => App::router()->getUri('h-agenda-edit-event', array('id' => $event->id)),
                            'title' => Lang::get($this->_plugin . '.list-edit-event'),
                            'target' => 'dialog'
                        ));
                    }
                ),

                'year' => array(
                    'hidden' => true,
                    'field' => 'startDate',
                    'display' => function($value){
                        return date('Y', strtotime($value));
                    }
                ),

                'startDate' => array(
                    'label' => Lang::get($this->_plugin . '.events-list-startDate-label'),
                    'display' => function($value, $field, $event) {
                        return lang::get($this->_plugin . '.events-list-startDate-content', array(
                            'day' => $event->getDayFull(),
                            'date' => date(Lang::get('main.date-format'), strtotime($value)),
                            'time' => date('H:i', strtotime($event->startTime))
                        ));
                    },
                    'search'  => array(
                        'type'       => 'date',
                    ),
                ),

                'startTime' => array(
                    'hidden' => true
                ),

                'title' => array(
                    'label' => Lang::get($this->_plugin . '.events-list-title-label'),
                    'sort' => false,
                    'display' => function($value, $field, $event) {
                        return '<a class="pointer" data-html="true" data-placement="right" data-toggle="popover" title="' . $value . '" data-content="' . $event->getFullDescription() . '">' . $value . '</a>';
                    },
                ),

                'description' => array(
                    'field' => 'CONCAT(LEFT(description, 80), IF(LENGTH(description) < 77, "", "..."))',
                    'hidden' => true,
                ),

                'place' => array(
                    'label' => Lang::get($this->_plugin . '.events-list-place-label'),
                    'search' => false,
                ),
            )
        ));

        if(!$list->isRefreshing()) {
            $this->addCss($this->getPlugin()->getCssUrl('agenda-list.less'));
            $this->addJavaScript($this->getPlugin()->getJsUrl('agenda-events-list.js'));
            $this->addKeysToJavaScript($this->_plugin . '.delete-event-confirmation');

             return NoSidebarTab::make(array(
                'title' => Lang::get($this->_plugin . '.list-title'),
                'page' => array(
                    'content' => $list->display()
                ),
                'icon' => $this->getPlugin()->getFaviconUrl() 
            ));
        }
        else{
            return $list->display();
        }
    }

    /**
     * Edit an event
     */
    public function editEvent(){
        $contacts = array();
        $date = null;

        if($this->id){
            $contacts = HAgendaContact::getAllContactsByEventId($this->id);
        }
        else{
            // Check if tab is specified in parameter
            if(App::request()->getParams('date')) {
                $date = App::request()->getParams('date');
            }
        }

        $pluginHConnectExist = Plugin::existAndIsActive('h-connect');

        $param = array(
            'id' => 'h-agenda-event-form',
            'model' => 'HAgendaEvent',
            'reference' => array('id' => $this->id),
            'fieldsets' => array(
                'general' => array(

                    new HiddenInput(array(
                        'name' => 'userId',
                        'value' => App::session()->getUser()->id,
                    )),

                    new HiddenInput(array(
                        'name' => 'mtime',
                        'value' => time(),
                    )),

                    $this->id ? NULL : new HiddenInput(array(
                        'name' => 'ctime',
                        'value' => time(),
                    )),

                    new TextInput(array(
                        'name' => 'title',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.title-label'),
                    )),

                    new TextInput(array(
                        'name' => 'description',
                        'label' => Lang::get($this->_plugin . '.description-label'),
                        'attributes' => array(
                            'style' => "width: 300px;",
                        )
                    )),

                    new TextInput(array(
                        'name' => 'place',
                        'label' => Lang::get($this->_plugin . '.place-label'),
                         'attributes' => array(
                            'style' => "width: 300px;",
                        )
                    )),

                    new DatetimeInput(array(
                        "name" => "startDate",
                        'required' => true,
                        "label" => Lang::get($this->_plugin . '.startDate-label'),
                        'attributes' => array(
                            'ko-value' => 'startDate',
                        ),
                        'value' => $date ? $date : NULL,
                    )),

                    new TimeInput(array(
                        "name" => "startTime",
                        'required' => true,
                        'attributes' => array(
                            'ko-value' => 'startTime',
                        ),
                        "nl" => false,
                    )),

                    new DatetimeInput(array(
                        "name" => "endDate",   
                        "label" => Lang::get($this->_plugin . '.endDate-label'),                    
                        'attributes' => array(
                            'ko-value' => 'endDate',
                        ),
                        'value' => $date ? $date : NULL,
                    )),
          
                    new TimeInput(array(
                        "name" => "endTime",
                        "nl" => false,
                        'attributes' => array(
                            'ko-value' => 'endTime',
                        ),
                    )),
                ),

                'contacts' => $pluginHConnectExist ? array(
                    new TextInput(array(
                        "name" => "search",
                        "independant" => true,
                        "label" => Lang::get($this->_plugin . '.event-form-search-label'),
                        "placeholder" => Lang::get($this->_plugin . '.event-form-search-placeholder'),
                        'attributes' => array(
                            'autocomplete' => 'off',
                            'ko-value' => 'search',
                            'style' => "width: 300px;",
                            'ko-autocomplete' => '{source : contactAutocompleteSource, change : onContactChosen}'
                        ),
                    )),

                    new HiddenInput(array(
                        "independant" => true,
                        'name' => 'contacts',
                        'default' => json_encode($contacts, JSON_NUMERIC_CHECK),
                        'attributes' => array(
                            'ko-value' => 'ko.toJSON(contacts)'
                        ),
                    )),

                    new HtmlInput(array(
                        "name" => "contact-list-name",
                        "value" => View::make(Plugin::current()->getView('list-contacts.tpl'), array(
                            'contacts' => $contacts
                        )),
                    )),
                ) : array(),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => ! $this->id
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close");'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close");app.load(app.getUri("h-agenda-index"));',
        );

        $form = new Form($param);

        $form->autocomplete = false;

        if(!$form->submitted()){
            if($pluginHConnectExist){
                $this->addJavascript($this->getPlugin()->getJsUrl('event-form.js'));
                $this->addKeysToJavaScript($this->_plugin . '.contact-already-in-event');
            }
            else{
                $this->addJavascript($this->getPlugin()->getJsUrl('event-form-without-contact.js'));
            }

            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'page' => $form,
                'title' => Lang::get($this->_plugin . '.agenda-form-title'),
            ));
        }
        else{

            if($form->submitted() === "delete"){
                if($pluginHConnectExist){
                    HAgendaContact::deleteByExemple(new DBExample(array(
                        'eventId' => $id,
                    )));
                }

                return $form->delete();
            }
            elseif($form->check()){
                try {
                    $eventId = $form->register(Form::NO_EXIT);

                    if($pluginHConnectExist){
                        $data = json_decode($form->getData('contacts'), true);

                        HAgendaContact::deleteContacts($eventId);

                        HAgendaContact::saveContacts($eventId, $data);
                    }

                    return $form->response(Form::STATUS_SUCCESS);
                }
                catch (Exception $e) {
                    return $form->response(Form::STATUS_ERROR);
                }
            }
        }
    }

    /**
     * Display the agenda in a calendar
     */
    public function calendarView(){
        // workaround to copy the full directory components to static directory
        $staticDir = $this->getPlugin()->getPublicStaticDir() . 'js/components/';

        if(!is_dir($staticDir)) {
            App::fs()->copy($this->getPlugin()->getStaticDir() . 'js/components/', dirname($staticDir));
        }

        $this->addJavascript($this->getPlugin()->getJsUrl('agenda-events-calendar.js'));
        $this->addCss($this->getPlugin()->getJsUrl('components/bootstrap-calendar/css/calendar.min.css'));
        $this->addCss($this->getPlugin()->getCssUrl('agenda-calendar.less'));

        $events = HAgendaEvent::getListByExample(new DBExample(array(
            'userId' => App::session()->getUser()->id,
            'startDate' => array(
                '$gte' => date('Y-m-d')
            )
        )));

        $data = array_map(function($event) {
            return array(
                'id' => $event->id,
                'title' => $event->getFullDescriptionCalendar(),
                'url' => '', //App::router()->getUri('h-agenda-edit-event-calendar', array('id' => $event->id)),
                'class' => 'event-important',
                'start' => strtotime($event->startDate . ' ' . $event->startTime) * 1000,
                'end' => strtotime($event->endDate . ' ' . $event->stopTime) * 1000,
            );
        }, $events);
        
        $viewCalendar = View::make($this->getPlugin()->getView('agenda-calendar.tpl'), array(
            'events' => $data,
            'plugin' => $this->getPlugin()
        ));

        return NoSidebarTab::make(array(
            'title' => Lang::get($this->_plugin . '.list-title'),
            'page' => array(
                'content' => $viewCalendar
            ),
            'icon' => $this->getPlugin()->getFaviconUrl() 
        ));
    }

    /**
     * Remove events
     */
    public function removeEvent() {
        try {
            $events = App::request()->getBody('events');

            foreach ($events as $id) {

                $event = HAgendaEvent::getById($id);

                if($event->userId == App::session()->getUser()->id)
                    $event->delete();
                else
                    App::response()->setStatus(500);            
            }
        }
        catch(\Exception $e) {
            App::response()->setStatus(500); 
        }
    }

    /**
     * Print the agenda on a PDF
     */
    public function pdf() {
        if(App::request()->getParams('events')) {
            $filter = new DBExample(array(
                'userId' => App::session()->getUser()->id,
                'id' => array(
                    '$in' => explode(',', App::request()->getParams('events'))
                )
            ));
        }
        else {
            $filter = new DBExample(array(
                'userId' => App::session()->getUser()->id,
                'startDate' => array(
                    '$gte' => date('Y-m-d')
                )
            ));
        }

        // Get the agenda events
        $listEvents = HAgendaEvent::getListByExample($filter, 'id', array(), array('startDate' => DB::SORT_ASC));
        $events = array();

        // Organize each event by year, month, day
        foreach($listEvents as $event) {
            $event->year = date('Y', strtotime($event->startDate));
            $event->month = Lang::get('main.month-' . date('m', strtotime($event->startDate)));
            $event->day = date('d', strtotime($event->startDate));
        }

        // Class event by yrea, month and day

        foreach($listEvents as $event) {
            if(empty($events[$event->year])) {
                $events[$event->year] = array();
            }

            if(empty($events[$event->year][$event->month])) {
                $events[$event->year][$event->month] = array();
            }

            if(empty($events[$event->year][$event->month][$event->day])) {
                $events[$event->year][$event->month][$event->day] = array();
            }

            $events[$event->year][$event->month][$event->day][] = $event;
        }

        $view = View::make($this->getPlugin()->getView('agenda-pdf.tpl'), array(
            'events' => $events
        ));

        $dom = \phpQuery::newDocument($view);
        $dom->find('script')->remove();
        $dom->find('*:first')
            ->before('<link rel="stylesheet" href="' . $this->getPlugin()->getCssUrl('agenda-pdf.less') . '" />');

        $view = $dom->htmlOuter();
        $pdf = new PDF(
            Lang::get($this->_plugin . '.pdf-title'),
            $view,
            array(
                'orientation' => 'Portrait', //Portrait  //Landscape
            )
        );

        $pdf->display('h-agenda.pdf');
        //$pdf->download('h-agenda.pdf');
    }
}