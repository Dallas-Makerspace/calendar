$(function() {
  /**
   * Configure Popovers
   */
  $('.calendar-popover').popover({
     html: true,
     template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  });

  /**
   * Dismiss Popovers with Outside Clicks
   */
   $('body').on('click', function (e) {
     $('[data-toggle="popover"]').each(function () {
         if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
             $(this).popover('hide');
         }
     });
 });

  /**
   * Dependent Field Displays
   */
  $('input, select').change(function() {
    var id = $(this).attr('id');
    var showDependents = false;

    if ((($(this).attr('type') === 'checkbox' || $(this).attr('type') === 'radio')) && $(this).is(':checked')) {
      showDependents = true;
    }

    if ($(this).attr('type') === 'number' && $(this).val() && parseInt($(this).val(), 10) > 0) {
      showDependents = true;
    }

    if ($(this).is('select') && $(this).val() === 'paid') {
      showDependents = true;
    }

    if (showDependents) {
      $('[data-depends-on-field=' + id + ']').each(function() {
        if ($(this).attr('data-dependent-required') === '1') {
          $(this).closest('.form-group').addClass('required');
          $(this).attr('required', true);
        }
      });
      $('[data-depends-on-field=' + id + ']').show();
    } else {
      $('[data-depends-on-field=' + id + ']').each(function() {
        if ($(this).attr('data-dependent-required') === '1') {
          $(this).closest('.form-group').removeClass('required');
          $(this).removeAttr('required');
        }
      });
      $('[data-depends-on-field=' + id + ']').hide();
    }

    if (showDependents) {
      $('[data-hidden-on-field=' + id + ']').each(function() {
        if ($(this).attr('data-dependent-required') === '1') {
          $(this).closest('.form-group').removeClass('required');
          $(this).removeAttr('required');
        }
      });
      $('[data-hidden-on-field=' + id + ']').hide();
    } else {
      $('[data-hidden-on-field=' + id + ']').each(function() {
        if ($(this).attr('data-dependent-required') === '1') {
          $(this).closest('.form-group').addClass('required');
          $(this).attr('required', true);
        }
      });
      $('[data-hidden-on-field=' + id + ']').show();
    }
  });
  $('input, select').change();

  /**
   * Calender Event Submissions
   */
  if ($('.events.add').length || $('.events.edit').length) {
    // Prerequisites require attendees to be DMS members
    $('#requires-prerequisite-id').change(function() {
      if ($(this).val()) {
        $('#members-only').attr('checked', true);
      }
    });
    $('#members-only').change(function() {
      if (!$(this).is(':checked')) {
        $('#requires-prerequisite-id').val('');
      }
    });

    // Digest configuration values from template
    var minLead = parseInt($('#config-mininum-booking-lead-time').text(), 10);
    var maxLead = parseInt($('#config-maximum-booking-lead-time').text(), 10);
    
    $('#event-start, #event-end, #event-start-2, #event-end-2, #event-start-3, #event-end-3, #event-start-4, #event-end-4, #event-start-5, #event-end-5').each(function () {        
        $(this).datetimepicker({
          useCurrent: false,
          minDate: moment().add(minLead, 'days'),
          maxDate: moment().add(maxLead, 'days'),
          date: new Date(
            Date.parse(
              $(this).val(),
              "mm/dd/yyyy hh:MM tt"
            )
          )
        });
    });
    
    $('#event-start').on('dp.change', function(e) {
        if (e.oldDate === null) {
            new Date(e.date._d.setHours(12, 00, 00));
            $(this).data('DateTimePicker').date(e.date.add(0, 'h'));
        }
    });
    $('#event-start').on('dp.change', function(e) {
      $('#event-end').data('DateTimePicker').minDate(e.date);
      $('#event-end').data('DateTimePicker').date(e.date.add(1, 'h'));
    });
    $('#event-end').on('dp.change', function(e) {
      //$('#event-start').data('DateTimePicker').maxDate(e.date);
      $('#event-start-2').data('DateTimePicker').minDate(e.date);
    });
    $('#event-start-2').on('dp.change', function(e) {
      $('#event-end').data('DateTimePicker').maxDate(e.date);
      $('#event-end-2').data('DateTimePicker').date(e.date.add(1, 'h'));
      $('#event-end-2').data('DateTimePicker').minDate(e.date);
    });
    $('#event-end-2').on('dp.change', function(e) {
      //$('#event-start-2').data('DateTimePicker').maxDate(e.date);
      $('#event-start-3').data('DateTimePicker').minDate(e.date);
    });
    $('#event-start-3').on('dp.change', function(e) {
      $('#event-end-2').data('DateTimePicker').maxDate(e.date);
      $('#event-end-3').data('DateTimePicker').date(e.date.add(1, 'h'));
      $('#event-end-3').data('DateTimePicker').minDate(e.date);
    });
    $('#event-end-3').on('dp.change', function(e) {
      //$('#event-start-3').data('DateTimePicker').maxDate(e.date);
      $('#event-start-4').data('DateTimePicker').minDate(e.date);
    });
    $('#event-start-4').on('dp.change', function(e) {
      $('#event-end-3').data('DateTimePicker').maxDate(e.date);
      $('#event-end-4').data('DateTimePicker').date(e.date.add(1, 'h'));
      $('#event-end-4').data('DateTimePicker').minDate(e.date);
    });
    $('#event-end-4').on('dp.change', function(e) {
      //$('#event-start-4').data('DateTimePicker').maxDate(e.date);
      $('#event-start-5').data('DateTimePicker').minDate(e.date);
    });
    $('#event-start-5').on('dp.change', function(e) {
      $('#event-end-4').data('DateTimePicker').maxDate(e.date);
      $('#event-end-5').data('DateTimePicker').date(e.date.add(1, 'h'));
      $('#event-end-5').data('DateTimePicker').minDate(e.date);
    });
    $('#event-end-5').on('dp.change', function(e) {
      //$('#event-start-5').data('DateTimePicker').maxDate(e.date);
    });
	
	$('.payment-type-select').change(function(e) {
		$('.event-cost, .event-eventbrite').addClass('hidden');
		$('#cost').val(0);
		$('#cost').trigger('change');
		$('#eventbrite-link').val('');
		$('#eventbrite-link').trigger('change');
		
		if ($(this).val() === 'paid') {
			$('.event-cost').removeClass('hidden');
		}
		
		if ($(this).val() === 'eventbrite') {
			$('.event-eventbrite').removeClass('hidden');
		}
	});
  }
});
