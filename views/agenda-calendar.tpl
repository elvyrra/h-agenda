<div id="h-agenda-calendar-navigation" class="row">
    <div class="col-xs-12">
        {button icon="list-alt" label="{text key='h-agenda.calendar-back-list'}" e-click="backToList" class="btn-info pull-left"  }

        {button icon="list-alt" label="{text key='h-agenda.btn-add-event-label'}" e-click="addEvent" class="btn-success pull-left"  }

        {foreach(array('month', 'week') as $period)}
            <button e-class="view === '{{ $period }}' ? 'btn-primary' : 'btn-info'" e-click="changeView('{{ $period }}')" class="pull-right btn">
                <span class="btn-label">{text key="{'h-agenda.calendar-view-' . $period}"}</span>
            </button>
        {/foreach}
    </div>
    <div class="col-xs-12">
        {button icon="arrow-left" e-click="prev" class="pull-left"}
        <span class="navigation-title center" e-text="title"></span>
        {button icon="arrow-right" e-click="next" class="pull-right"}
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

    require(['app', 'emv', 'underscore', 'calendar', 'calendar-language'], function() {
        class myModel extends EMV {
            prev(){
                calendar.navigate('prev');
            }

            next(){
                calendar.navigate('next');
            }

            backToList(){
                app.load(app.getUri('h-agenda-index') + '?view=list');
            }

            addEvent(){
                app.dialog(app.getUri('h-agenda-edit-event', {id: 0}));
            }

            changeView(newView){
                this.view = newView;
               calendar.view(newView);
            }
        }

        const emv = new myModel({
            data : {
                view : $.cookie('h-agenda-calendar-view') || 'month',
                title : ''
            }
        });

        emv.$apply(document.getElementById('h-agenda-calendar-navigation'));

        var calendar = $('#h-agenda-calendar').calendar({
            events_source : function() {
                return {{ json_encode($events) }};
            },
            tmpl_path : '{{ $plugin->getStaticUrl() . "js/components/bootstrap-calendar/tmpls/" }}',
            language : '{{ LANGUAGE }}',
            onAfterViewLoad: function(view) {
                emv.title = this.getTitle();
            },
            view : $.cookie('h-agenda-calendar-view') || 'month',
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

        $('.cal-month-day').click(function(){
            var date = this.firstElementChild.getAttribute('data-cal-date');
            app.dialog(app.getUri('h-agenda-edit-event', {id: 0}) + "?date=" + date);
        });
    });
</script>