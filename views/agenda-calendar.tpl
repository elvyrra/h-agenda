
<div id="h-agenda-calendar-navigation" class="row">
    <div class="col-xs-12">
        {button icon="list-alt" label="{text key='h-agenda.calendar-back-list'}" ko-click="backToList" class="btn-info pull-left"  }

        {button icon="list-alt" label="{text key='h-agenda.btn-add-event-label'}" ko-click="addEvent" class="btn-success pull-left"  }

        {foreach(array('month', 'week') as $period)}
            <button ko-class="$data.view() === '{{ $period }}' ? 'btn-primary' : 'btn-info'" ko-click="function(){ $data.view('{{ $period }}');}" class="pull-right btn">
                <span class="btn-label">{text key="{'h-agenda.calendar-view-' . $period}"}</span>
            </button>
        {/foreach}
    </div>
    <div class="col-xs-12">
        {button icon="arrow-left" ko-click="prev" class="pull-left"}
        <span class="navigation-title center" ko-text="title"></span>
        {button icon="arrow-right" ko-click="next" class="pull-right"}
    </div>
</div>

<div class="clearfix"></div>

<div id="h-agenda-calendar">
    <span class="icon icon-2x icon-spin icon-spinner calendar-loading"></span>
</div>

<script type="text/javascript">
    require.config({
        paths : {
            underscore : "{{ $plugin->getJsUrl('components/underscore/underscore-min.js') }}",
            calendar : "{{ $plugin->getJsUrl('components/bootstrap-calendar/js/calendar.min.js') }}",
            'calendar-language' : "{{ $plugin->getJsUrl('components/bootstrap-calendar/js/language/' . LANGUAGE . '.js') }}"
        },

        shim : {
            calendar : {
                deps : ['jquery']
            },

            'calendar-language' : {
                deps : ['calendar']
            }
        }
    });
    require(['app', 'underscore', 'calendar', 'calendar-language'], function() {
        var navigation = {
            prev : function(){
                calendar.navigate('prev');
            },
            next : function(){
                calendar.navigate('next');
            },
            view : ko.observable($.cookie('h-agenda-calendar-view') || 'month'),
            title : ko.observable(''),
            backToList : function(){
                app.load(app.getUri('h-agenda-index') + '?view=list');
            },
            addEvent : function(){
                app.dialog(app.getUri('h-agenda-edit-event', {id: 0}));
            },
        };

        navigation.view.subscribe(function(view) {
            calendar.view(view);
            $.cookie('h-agenda-calendar-view', view, 365, '/');
        });

        ko.applyBindings(navigation, document.getElementById('h-agenda-calendar-navigation'));


        var calendar = $('#h-agenda-calendar').calendar({
            events_source : function() {
                return {{ json_encode($events) }};
            },
            tmpl_path : '{{ $plugin->getStaticUrl() . "js/components/bootstrap-calendar/tmpls/" }}',
            language : '{{ LANGUAGE }}',
            onAfterViewLoad: function(view) {
                navigation.title(this.getTitle());
            },
            view : navigation.view(),
            views : {
                day : {
                    enable : false
                },
                week : {
                    enable : true
                },
                month : {
                    enable : true
                },
                year : {
                    enable : true
                }

            }
        });
    });
</script>