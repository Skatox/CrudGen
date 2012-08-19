function submitForm(){
	$('#operation-form').submit()
}

$().ready(function(){
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
		$('#operation-form').attr('action','?operation=delete')
		submitForm()
	})

	$('#selectedAll').on('click', function(){
		$('#results input').prop('checked', $(this).prop('checked'))
	})
})