// Some general purpose JS functions for the iTop application
/**
 * Reload a truncated list
 */
aTruncatedLists = {}; // To keep track of the list being loaded, each member is an ajaxRequest object

function ReloadTruncatedList(divId, sSerializedFilter, sExtraParams)
{
	$('#'+divId).block();
	//$('#'+divId).blockUI();
	if (aTruncatedLists[divId] != undefined)
	{
		try
		{
			aAjaxRequest = aTruncatedLists[divId];
			aAjaxRequest.abort();
		}
		catch(e)
		{
			// Do nothing special, just continue
			console.log('Uh,uh, exception !');
		}
	}
	aTruncatedLists[divId] = $.post('../pages/ajax.render.php?style=list',
	   { operation: 'ajax', filter: sSerializedFilter, extra_params: sExtraParams },
	     function(data)
	     {
			 aTruncatedLists[divId] = undefined;
			 if (data.length > 0)
			 {
				 $('#'+divId).html(data);
				 $('#'+divId+' .listResults').tableHover(); // hover tables
				 $('#'+divId+' .listResults').each( function()
					{
						var table = $(this);
						var id = $(this).parent();
						aTruncatedLists[divId] = undefined;
						var checkbox = (table.find('th:first :checkbox').length > 0);
						if (checkbox)
						{
							// There is a checkbox in the first column, don't make it sortable
							table.tablesorter( { headers: { 0: {sorter: false}}, widgets: ['myZebra', 'truncatedList']} ).tablesorterPager({container: $("#pager")}); // sortable and zebra tables
						}
						else
						{
							// There is NO checkbox in the first column, all columns are considered sortable
							table.tablesorter( { widgets: ['myZebra', 'truncatedList']} ).tablesorterPager({container: $("#pager"), totalRows:97, filter: sSerializedFilter, extra_params: sExtraParams }); // sortable and zebra tables
						}
					});
				 $('#'+divId).unblock();
			 }
		}
	 );
}
/**
 * Truncate a previously expanded list !
 */
function TruncateList(divId, iLimit, sNewLabel, sLinkLabel)
{
	$('#'+divId).block();
	var iCount = 0;
	$('#'+divId+' table.listResults tr:gt('+iLimit+')').each( function(){
			$(this).remove();
	});
	$('#lbl_'+divId).html(sNewLabel);
	$('#'+divId+' table.listResults tr:last td').addClass('truncated');
	$('#'+divId+' table.listResults').addClass('truncated');
	$('#trc_'+divId).html(sLinkLabel);
	$('#'+divId+' .listResults').trigger("update"); //  Reset the cache
	$('#'+divId).unblock();
}
/**
 * Reload any block -- used for periodic auto-reload
 */ 
function ReloadBlock(divId, sStyle, sSerializedFilter, sExtraParams)
{
	$('#'+divId).block();
	//$('#'+divId).blockUI();
	$.post('../pages/ajax.render.php?style='+sStyle,
	   { operation: 'ajax', filter: sSerializedFilter, extra_params: sExtraParams },
	   function(data){
		 $('#'+divId).empty();
		 $('#'+divId).append(data);
		 $('#'+divId).removeClass('loading');
		 $('#'+divId+' .listResults').tableHover(); // hover tables
		 $('#'+divId+' .listResults').each( function()
				{
					var table = $(this);
					var id = $(this).parent();
					var checkbox = (table.find('th:first :checkbox').length > 0);
					if (checkbox)
					{
						// There is a checkbox in the first column, don't make it sortable
						table.tablesorter( { headers: { 0: {sorter: false}}, widgets: ['myZebra', 'truncatedList']} ); // sortable and zebra tables
					}
					else
					{
						// There is NO checkbox in the first column, all columns are considered sortable
						table.tablesorter( { widgets: ['myZebra', 'truncatedList']} ); // sortable and zebra tables
					}
				});
		 //$('#'+divId).unblockUI();
		}
	 );
}

/**
 * Update the display and value of a file input widget when the user picks a new file
 */ 
function UpdateFileName(id, sNewFileName)
{
	var aPath = sNewFileName.split('\\');
	var sNewFileName = aPath[aPath.length-1];

	$('#'+id).val(sNewFileName);
	$('#'+id).trigger('validate');
	$('#name_'+id).text(sNewFileName);
	return true;
}
/**
 * Reload a search form for the specified class
 */
function ReloadSearchForm(divId, sClassName, sBaseClass, sContext)
{
    var oDiv = $('#ds_'+divId);
	oDiv.block();
	var oFormEvents = $('#ds_'+divId+' form').data('events');

	// Save the submit handlers
    aSubmit = new Array();
	if ( (oFormEvents != null) && (oFormEvents.submit != undefined))
	{
		for(index = 0; index < oFormEvents.submit.length; index++)
		{
			aSubmit [index ] = { data:oFormEvents.submit[index].data, namespace:oFormEvents.submit[index].namespace, handler:  oFormEvents.submit[index].handler};
		}
	}
	sAction =  $('#ds_'+divId+' form').attr('action');

	$.post('../pages/ajax.render.php?'+sContext,
	   { operation: 'search_form', className: sClassName, baseClass: sBaseClass, currentId: divId, action: sAction },
	   function(data) {
		   oDiv.empty();
		   oDiv.append(data);
		   if (aSubmit.length > 0)
		   {
			    var oForm = $('#ds_'+divId+' form'); // Form was reloaded, recompute it
				for(index = 0; index < aSubmit.length; index++)
				{
					// Restore the previously bound submit handlers
					if (aSubmit[index].data != undefined)
					{
						oForm.bind('submit.'+aSubmit[index].namespace, aSubmit[index].data, aSubmit[index].handler)
					}
					else
					{
						oForm.bind('submit.'+aSubmit[index].namespace, aSubmit[index].handler)
					}
				}
		   }
		   oDiv.unblock();
		   oDiv.parent().resize(); // Inform the parent that the form has just been (potentially) resized
	   }
	 );
}

/**
 * Stores - in a persistent way - user specific preferences
 * depends on a global variable oUserPreferences created/filled by the iTopWebPage
 * that acts as a local -write through- cache
 */
function SetUserPreference(sPreferenceCode, sPrefValue, bPersistent)
{
	sPreviousValue = undefined;
	try
	{
		sPreviousValue = oUserPreferences[sPreferenceCode];
	}
	catch(err)
	{
		sPreviousValue = undefined;
	}
    oUserPreferences[sPreferenceCode] = sPrefValue;
    if (bPersistent && (sPrefValue != sPreviousValue))
    {
    	ajax_request = $.post('../pages/ajax.render.php',
    						  { operation: 'set_pref', code: sPreferenceCode, value: sPrefValue} ); // Make it persistent
    }
}

/**
 * Get user specific preferences
 * depends on a global variable oUserPreferences created/filled by the iTopWebPage
 * that acts as a local -write through- cache
 */
function GetUserPreference(sPreferenceCode, sDefaultValue)
{
	var value = sDefaultValue;
	if ( oUserPreferences[sPreferenceCode] != undefined)
	{
		value = oUserPreferences[sPreferenceCode];
	}
	return value;
}

/**
 * Check/uncheck a whole list of checkboxes
 */
function CheckAll(sSelector, bValue)
{
	var value = bValue;
	$(sSelector).each( function() {
		if (this.checked != value)
		{	
			this.checked = value;
			$(this).trigger('change');
		}
	});
}


/**
 * Toggle (enabled/disabled) the specified field of a form
 */
function ToogleField(value, field_id)
{
	if (value)
	{
		$('#'+field_id).removeAttr('disabled');
	}
	else
	{
		$('#'+field_id).attr('disabled', 'disabled');
	}
	$('#'+field_id).trigger('update');
	$('#'+field_id).trigger('validate');
}

/**
 * For the fields that cannot be visually disabled, they can be blocked
 * @return
 */
function BlockField(field_id, bBlocked)
{
	if (bBlocked)
	{
		$('#'+field_id).block({ message: ' ** disabled ** '});
	}
	else
	{
		$('#'+field_id).unblock();
	}
}

/**
 * Updates (enables/disables) a "duration" field
 */
function ToggleDurationField(field_id)
{
	// Toggle all the subfields that compose the "duration" input
	aSubFields = new Array('d', 'h', 'm', 's');
	
	if ($('#'+field_id).attr('disabled'))
	{
		for(var i=0; i<aSubFields.length; i++)
		{
			$('#'+field_id+'_'+aSubFields[i]).attr('disabled', 'disabled');
		}
	}
	else
	{
		for(var i=0; i<aSubFields.length; i++)
		{
			$('#'+field_id+'_'+aSubFields[i]).removeAttr('disabled');
		}
	}
}

/**
 * PropagateCheckBox
 */
function PropagateCheckBox(bCurrValue, aFieldsList, bCheck)
{
	if (bCurrValue == bCheck)
	{
		for(var i=0;i<aFieldsList.length;i++)
		{
			$('#enable_'+aFieldsList[i]).attr('checked', bCheck);
			ToogleField(bCheck, aFieldsList[i]);
		}
	}
}