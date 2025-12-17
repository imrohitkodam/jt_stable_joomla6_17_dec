function getCal(data)
{
	clearCalendarData();
	jQuery('#jlike-calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay,listWeek'
		},
		navLinks: true, // can click day/week names to navigate views

		weekNumbers: true,
		weekNumbersWithinDays: false,
		weekNumberCalculation: 0,

		editable: false,
		eventLimit: true, // allow "more" link when too many events
		events: data ? data : []
	})
}

function clearCalendarData()
{
	jQuery('#jlike-calendar').fullCalendar('destroy')
}