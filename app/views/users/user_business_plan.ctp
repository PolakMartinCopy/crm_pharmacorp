<h1>Plán obchodních jednání - <?php echo $user['User']['full_name']?></h1>
<?php
	echo $this->Form->create('BusinessSession', array('url' => array('controller' => 'users', 'action' => 'business_plan', $user['User']['id'])));
	echo $this->Form->input('BusinessSession.date', array('label' => false, 'type' => 'text', 'div' => false));
	echo $this->Form->end();
?>

<script type="text/javascript" src="/plugins/fullcalendar/fullcalendar.js"></script>
<script type="text/javascript">
$(function() {
	var dates = $('#BusinessSessionDate').datepicker({
		numberOfMonths: 1,
	    beforeShow: function() {
	        setTimeout(function(){
	            $('.ui-datepicker').css('z-index', 100);
	        }, 0);
	    },
	    onSelect: function(selectedDate) {
		    $('#BusinessSessionUserBusinessPlanForm').submit();
	    }
	});
	
	var data = <?php echo $events?>;
	var events = [];
	$.each(data, function(index, item) {
		var event = {
			id: item.id,
		    title:  item.title,
		    start: new Date(item.start_year, item.start_month, item.start_day, item.start_hour, item.start_min),
		    end: new Date(item.end_year, item.end_month, item.end_day, item.end_hour, item.end_min),
		    allDay: item.all_day
		};
		events.push(event);
	});
	
	var date = new Date(<?php echo $year?>, <?php echo $month?>, <?php echo $day?>);
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next',
			center: 'title',
			right: ''
		},
		date: d,
		month: m,
		year: y,
		defaultView: 'agendaDay',
		monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
		monthNamesShort: ['Led', 'Úno', 'Bře', 'Dub', 'Kvě', 'Čer', 'Čvc', 'Srp', 'Zář', 'Říj', 'Lis', 'Pro'],
		dayNames: ['Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'],
		dayNamesShort: ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'],
		timeFormat: 'H:mm', // uppercase H for 24-hour clock
		axisFormat: 'H:mm', // uppercase H for 24-hour clock
		allDaySlot: false,
		allDayText: false,
		minTime: 6,
		maxTime: 20,
		events: events,
		eventClick: function(event) {
			if (event.id) {
	            window.open('/user/business_sessions/view/' + event.id);
	            return false;
	        }
		}
	});

	$('.fc-button-prev').click(function(e){
		var prevDate = new Date();
		prevDate.setDate(date.getDate()-1);
		var day = prevDate.getDate();
		var monthIndex = prevDate.getMonth();
		monthIndex = monthIndex + 1;
		var month = monthIndex.toString();
		if (month.length == 1) {
			month = '0' + month;
		}
		var year = prevDate.getFullYear();
		prevDate = day + '.' + month + '.' + year;
		$('#BusinessSessionDate').val(prevDate);
		$('#BusinessSessionUserBusinessPlanForm').submit();
	});

	$('.fc-button-next').click(function(e) {
		var nextDate = new Date();
		nextDate.setDate(date.getDate()+1);

		var day = nextDate.getDate();
		var monthIndex = nextDate.getMonth();
		monthIndex = monthIndex + 1;
		var month = monthIndex.toString();
		if (month.length == 1) {
			month = '0' + month;
		}
		var year = nextDate.getFullYear();
		nextDate = day + '.' + month + '.' + year;
		$('#BusinessSessionDate').val(nextDate);
		$('#BusinessSessionUserBusinessPlanForm').submit();
	});
});
</script>

<div id="calendar"></div>