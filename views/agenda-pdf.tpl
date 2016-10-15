<table id="agenda-pdf">
    {foreach($events as $year => $months)}
        <tr class="spacer"><td colspan="4">Space</td></tr>
        <tr>
            <td colspan="4" class="text-primary year-separator alert-info">
                {{ $year }}
            </td>
        </tr>

        {foreach($months as $month => $days)}
            <tr class="spacer"><td colspan="4">Space</td></tr>
            <tr>
                <td colspan="4" class="text-primary month-separator well">
                    {{ $month }} {{ $year }}
                </td>
            </tr>

            {foreach($days as $day => $events)}
                {foreach($events as $i => $event)}
                    <tr class="event-line">
                        {if($i == 0)}
                            <td rowspan="{{ count($events) }}" class="event-day">
                                <span class="icon icon-calendar-o icon-5x text-success"></span>
                                <span class="day-number">{{ $day }}</span>
                            </td>
                        {/if}

                        <td class="event-hour">
                            {{ date('H:i', strtotime($event->event->startTime)) }}
                        </td>

                        <td class="event-data">
                            <div class="event-preview">
                                {if($event->event->getImageUrl())}
                                    <img src="{{ $event->event->getImageUrl() }}" alt="Event preview" />
                                {/if}
                            </div>

                            <div class="event-details">
                                <p class="event-title">{{ $event->event->title }}</p>

                                <p class="event-place">{{ $event->event->place }}</p>
                                <p class="event-address-1">{{ $event->event->addressLine1 }}</p>
                                <p class="event-address-2">{{ $event->event->addressLine2 }}</p>
                            </div>
                        </td>

                        <td class="event-place">
                            <b class="event-city">{{ $event->event->city }}</b>
                            <br />
                            {if($event->event->country === Country::DEFAULT_COUNTRY)}
                                ( {{ $event->event->department }} )
                            {else}
                                ( {{ Country::getNameByCode($event->event->country) }} )
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            {/foreach}
        {/foreach}
    {/foreach}
</table>