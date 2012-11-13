function submitForm(){
	$('#operation-form').submit()
}

function hasItemsSelected(){
	var selected = false
	$('#results input[type="checkbox"]').each(function(){
		if($(this).prop('checked')){
			selected = true
		}
	})
	return selected
}

$().ready(function(){
	var crudgenDelTxt = $('#strconfirmdelete').val();

	$('.date').datepicker({ dateFormat: 'yy-mm-dd' });
	
	$('.offset').on('change', function(){12
		submitForm()
	})

	$('.limit-wrapper select').on('change', function(){
		$('.offset').val(0)
		submitForm()
	})

	$('a.pagination').on('click', function(){
		$('.offset').val($(this).attr('rel'))
		submitForm()	
	})

	$('#results th a').on('click', function(){
		var order = $('#order').val() == 'ASC' ? 'DESC' : 'ASC' 
		console.log(order);
		$('#order').val(order)
		$('#column_order').val($(this).attr('rel'))
		submitForm()	
	})

	$('div.errorMsg , div.message').on('click', function(){
		$(this).fadeOut('normal',function(){
			$(this).remove()
		})
	})

	$('.actions-wrapper .deleteButton').on('click', function(e){
		e.preventDefault()

		if(hasItemsSelected()){
			if(confirm(crudgenDelTxt)){
				$('#operation-form').attr('action','?operation=delete')
				submitForm()
			}			
		} else {
			alert($('#noselected').val())
		}

	})

	$('.actions .deleteButton').on('click', function(e){
		e.preventDefault()
		if(confirm(crudgenDelTxt)){
			window.location = $(this).attr('href');
		}
	})

	$('#selectedAll').on('click', function(){
		$('#results input').prop('checked', $(this).prop('checked'))
	})

	$('#operation-form').validate();

	$('.actions-wrapper .updateButton').on('click', function(e){
		e.preventDefault();
		$('#operation-form').attr('action', $(this).attr('href') );
		$('#operation-form').submit();
	});
})