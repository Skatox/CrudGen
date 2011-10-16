function updateSecurityTable(){
	if(document.getElementById('auth_method').selectedIndex==2){
		document.getElementById('table-row').style.display='';
		document.getElementById('pass-row').style.display='';
		document.getElementById('user-row').style.display='';
	}
	else {
		document.getElementById('table-row').style.display='none';
		document.getElementById('pass-row').style.display='none';
		document.getElementById('user-row').style.display='none';
		document.getElementById('auth_user_col').value='';
		document.getElementById('auth_pass_col').value='';
	}
}

function updateColumns(){
	document.getElementById('action-input').value='create_app';
	document.getElementById('createappform').submit();
}

function checkAllCheckboxes(aId, aChecked) {
    var collection = document.getElementById(aId).getElementsByTagName('input');
    for (var x=0; x<collection.length; x++) {
        if ((collection[x].type.toLowerCase()=='checkbox')&&(collection[x].disabled==false))
            collection[x].checked = aChecked;
    }
}

function goPreviousStep(){
    var step = document.getElementById('step');
    step.value= step.value -2;
    document.getElementById(pagesform).submit();
}