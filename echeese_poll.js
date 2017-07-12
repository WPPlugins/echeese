choice_count = 2;
function poll_add_choice()
{
	choice_count++;
	new_choice = document.createElement('li');
	new_choice.innerHTML = '<input type="text" name="choice' + choice_count + '_in" size="30" value="" id="choice' + choice_count + '_in_id" autocomplete="off" />';

	poll_choices = document.getElementById('poll_choice_list');
	poll_choices.appendChild(new_choice);
}

function poll_del_choice()
{
	if(choice_count == 2)
		return;

	choice_count--;
	poll_choices = document.getElementById('poll_choice_list');
	last_choice = poll_choices.childNodes[ poll_choices.childNodes.length-1 ];
	poll_choices.removeChild(last_choice);
}

function checkField(id, field_name)
{
  MIN_FIELDS_LENGTH = 1;
  MAX_FIELDS_LENGTH = 512;

  field = document.getElementById(id);
  error = document.getElementById('error_id');

  if(field.value.length < MIN_FIELDS_LENGTH)
  {
    error.innerHTML = "Please fill the " + field_name + " field.";
    return false;
  }
  if(field.value.length > MAX_FIELDS_LENGTH)
  {
    error.innerHTML = "Please reduce the length of the " + field_name + " field, it cannot be greater than " + MAX_FIELDS_LENGTH + " characters.";
    return false;
  }
  return true;
}

function checkNewPoll()
{
  if(!checkField('title_id', 'title'))
    return false;

  for(i = 1 ; i < choice_count; i++)
  {
    numerals = ['1st', '2nd', '3rd'];
    if(i-1 < numerals.length)
        name = numerals[i-1];
    else
        name = i + 'th';
    if(! checkField('choice' + i + '_in_id', name + ' choice'))
        return false;
  }
  return true;
}
