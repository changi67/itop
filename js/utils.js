// Some general purpose JS functions for the iTop application

//IE 8 compatibility, copied from: https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/IndexOf
if (!Array.prototype.indexOf) {
	
	if (false) // deactivated since it causes troubles: for(k in aData) => returns the indexOf function as first element on empty arrays !
	{
	Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
    	"use strict";
        if (this == null) {
        	throw new TypeError();
        }
        var t = Object(this);
        var len = t.length >>> 0;
        if (len === 0) {
        	return -1;
        }
        var n = 0;
        if (arguments.length > 1) {
        	n = Number(arguments[1]);
            if (n != n) { // shortcut for verifying if it's NaN
            	n = 0;
            } else if (n != 0 && n != Infinity && n != -Infinity) {
            	n = (n > 0 || -1) * Math.floor(Math.abs(n));
            }
        }
        if (n >= len) {
        	return -1;
        }
        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
        for (; k < len; k++) {
        	if (k in t && t[k] === searchElement) {
            	return k;
            }
        }
        return -1;
    }
	}
}
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
	aTruncatedLists[divId] = $.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php?style=list',
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
	// Check if the user is not editing the list properties right now
	var bDialogOpen = false;
	var oDataTable = $('#'+divId+' :itop-datatable');
	var bIsDataTable = false;
	if (oDataTable.length > 0)
	{
		bDialogOpen = oDataTable.datatable('IsDialogOpen');
		bIsDataTable = true;
	}
	if (!bDialogOpen)
	{
		if (bIsDataTable)
		{
			oDataTable.datatable('DoRefresh');
		}
		else
		{
			$('#'+divId).block();
			
			$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php?style='+sStyle,
			   { operation: 'ajax', filter: sSerializedFilter, extra_params: sExtraParams },
			   function(data){
				 $('#'+divId).empty();
				 $('#'+divId).append(data);
				 $('#'+divId).removeClass('loading');
				}
			 );
		}
	}
}

function SaveGroupBySortOrder(sTableId, aValues)
{
	var sDashboardId = $('#'+sTableId).closest('.dashboard_contents').attr('id');
	var sPrefKey = 'GroupBy_'+sDashboardId+'_'+sTableId;
	if (aValues.length != 0)
	{
		$sValue = JSON.stringify(aValues);
		if (GetUserPreference(sPrefKey, null) != $sValue)
		{
			SetUserPreference(sPrefKey, $sValue, true);			
		}
	}
}

function LoadGroupBySortOrder(sTableId)
{
	var sDashboardId = $('#'+sTableId).closest('.dashboard_contents').attr('id');
	var sPrefKey = 'GroupBy_'+sDashboardId+'_'+sTableId;
	var sValues = GetUserPreference(sPrefKey, null);
	if (sValues != null)
	{
		aValues = JSON.parse(sValues);
		window.setTimeout(function () { $('#'+sTableId+' table.listResults').trigger('sorton', [aValues]); }, 50);
	}
	
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
	// deprecated in jQuery 1.8 
	//var oFormEvents = $('#ds_'+divId+' form').data('events');
	var oForm = $('#ds_'+divId+' form');
	var oFormEvents = $._data(oForm[0], "events");

	// Save the submit handlers
    aSubmit = new Array();
	if ( (oFormEvents != null) && (oFormEvents.submit != undefined))
	{
		for(var index = 0; index < oFormEvents.submit.length; index++)
		{
			aSubmit [index ] = { data:oFormEvents.submit[index].data, namespace:oFormEvents.submit[index].namespace, handler:  oFormEvents.submit[index].handler};
		}
	}
	sAction =  $('#ds_'+divId+' form').attr('action');

	// Save the current values in the form
	var oMap = {};
	$('#ds_'+divId+" form :input[name!='']").each(function() {
		oMap[this.name] = this.value;
	});
	oMap.operation = 'search_form';
	oMap.className = sClassName;
	oMap.baseClass = sBaseClass;
	oMap.currentId = divId;
	oMap.action = sAction;
	
	$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php?'+sContext, oMap,
	   function(data) {
		   oDiv.empty();
		   oDiv.append(data);
		   if (aSubmit.length > 0)
		   {
			    var oForm = $('#ds_'+divId+' form'); // Form was reloaded, recompute it
				for(var index = 0; index < aSubmit.length; index++)
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
		   FixSearchFormsDisposition();
		   oDiv.unblock();
		   oDiv.parent().resize(); // Inform the parent that the form has just been (potentially) resized
	   }
	 );
}

function FixSearchFormsDisposition()
{
	// Fix search forms
	$('.SearchDrawer').each(function() {
		var colWidth = 0;
		var labelWidth = 0;
		$('label:visible', $(this)).each( function() {
			var l = $(this).parent().width() - $(this).width();
			colWidth = Math.max(l, colWidth);
			labelWidth = Math.max($(this).width(), labelWidth);
		});
		$('label:visible', $(this)).each( function() {
			if($(this).data('resized') != true)
			{
				$(this).parent().width(colWidth + labelWidth);
				$(this).width(labelWidth).css({display: 'inline-block'}).data('resized', true);					
			}
		});		
	});

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
    	ajax_request = $.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php',
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
		// In case the field is rendered as a div containing several inputs (e.g. RedundancySettings)
		$('#'+field_id+' :input').removeAttr('disabled');
	}
	else
	{
		$('#'+field_id).attr('disabled', 'disabled');
		// In case the field is rendered as a div containing several inputs (e.g. RedundancySettings)
		$('#'+field_id+' :input').attr('disabled', 'disabled');
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

function FixTableSorter(table)
{
	if (table[0].config == undefined)
	{
		// Table is not sort-able, let's fix it
		var checkbox = (table.find('th:first :checkbox').length > 0);
		if (checkbox)
		{
			// There is a checkbox in the first column, don't make it sort-able
			table.tablesorter( { headers: { 0: {sorter: false}}, widgets: ['myZebra', 'truncatedList']} ); // sort-able and zebra tables
		}
		else
		{
			// There is NO checkbox in the first column, all columns are considered sort-able
			table.tablesorter( { widgets: ['myZebra', 'truncatedList']} ); // sort-able and zebra tables
		}
	}
}

function DashletCreationDlg(sOQL)
{
	$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php', {operation: 'dashlet_creation_dlg', oql: sOQL}, function(data){
		$('body').append(data);
	});
	return false;
}

function ShortcutListDlg(sOQL, sDataTableId, sContext)
{
	var sDataTableName = 'datatable_'+sDataTableId;
	var oTableSettings = {
		oColumns: $('#'+sDataTableName).datatable('option', 'oColumns'),
		iPageSize: $('#'+sDataTableName).datatable('option', 'iPageSize')
	};
	var sTableSettings = JSON.stringify(oTableSettings);

	$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php?'+sContext, {operation: 'shortcut_list_dlg', oql: sOQL, table_settings: sTableSettings}, function(data){
		$('body').append(data);
	});
	return false;
}

function ExportListDlg(sOQL, sDataTableId, sFormat, sDlgTitle)
{
	var aFields = [];
	if (sDataTableId != '')
	{
		var sDataTableName = 'datatable_'+sDataTableId;
		var oColumns = $('#'+sDataTableName).datatable('option', 'oColumns');
		for(var j in oColumns)
		{
			for(var k in oColumns[j])
			{
				if (oColumns[j][k].checked)
				{
					var sCode = oColumns[j][k].code;
					if (sCode == '_key_')
					{
						sCode = 'id';
					}
					aFields.push(j+'.'+sCode);
				}
			}
		}
	}
	
	var oParams = {
			interactive: 1,
			mode: 'dialog',
			expression: sOQL,
			suggested_fields: aFields.join(','),
			dialog_title: sDlgTitle
	};
	
	if (sFormat !== null)
	{
		oParams.format = sFormat;
	}
	
	$.post(GetAbsoluteUrlAppRoot()+'webservices/export-v2.php', oParams, function(data) {
		$('body').append(data);
	});
	return false;
}

function ExportToggleFormat(sFormat)
{
	$('.form_part').hide();
	for(k in window.aFormParts[sFormat])
	{
		$('#form_part_'+window.aFormParts[sFormat][k]).show().trigger('form-part-activate');
	}	 
}
		
function ExportStartExport()
{
	var oParams = {};
	$('.form_part:visible :input').each(function() {
		if (this.name != '')
		{
			if ((this.type == 'radio') || (this.type == 'checkbox'))
			{
				if (this.checked)
				{
					oParams[this.name] = $(this).val();
				}
			}
			else
			{
				oParams[this.name] = $(this).val();
			}
		}
	});
	$(':itop-tabularfieldsselector:visible').tabularfieldsselector('close_all_tooltips');
	$('#export-form').hide();
	$('#export-feedback').show();
	oParams.operation = 'export_build';
	oParams.format = $('#export-form :input[name=format]').val();
	var sQueryMode = $(':input[name=query_mode]:checked').val();
	if($(':input[name=query_mode]:checked').length > 0)
	{
		if (sQueryMode == 'oql')
		{
			oParams.expression = $('#export-form :input[name=expression]').val();
		}
		else
		{	
			oParams.query = $('#export-form :input[name=query]').val();
		}
	}
	else
	{
		oParams.expression = $('#export-form :input[name=expression]').val();
		oParams.query = $('#export-form :input[name=query]').val();		
	}
	$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php', oParams, function(data) {
		ExportRun(data);
	}, 'json');
}
		
function ExportRun(data)
{
	switch(data.code)
	{
		case 'run':
		// Continue
		$('.export-progress-bar').progressbar({value: data.percentage });
		$('.export-message').html(data.message); 
		oParams = {};
		oParams.token = data.token;
		var sDataState = $('#export-form').attr('data-state');
		if (sDataState == 'cancelled')
		{
			oParams.operation = 'export_cancel';
		}
		else
		{
			oParams.operation = 'export_build';
		}
		
		$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php', oParams, function(data) {
			ExportRun(data);
		},
		'json');
		break;

		case 'done':
		$('#export-btn').hide();
		sMessage = '<a href="'+GetAbsoluteUrlAppRoot()+'pages/ajax.render.php?operation=export_download&token='+data.token+'" target="_blank">'+data.message+'</a>'; 
		$('.export-message').html(sMessage);
		$('.export-progress-bar').hide();
		$('#export-btn').hide();
		$('#export-form').attr('data-state', 'done');
		if(data.text_result != undefined)
		{
			if (data.mime_type == 'text/html')
			{
				$('#export_content').parent().html(data.text_result);
				$('#export_text_result').show();
				$('#export_text_result .listResults').tableHover();
				$('#export_text_result .listResults').tablesorter( { widgets: ['myZebra']} );
			}
			else
			{
				if ($('#export_text_result').closest('ui-dialog').length == 0)
				{
					// not inside a dialog box, adjust the height... approximately
					var jPane = $('#export_text_result').closest('.ui-layout-content');
					var iTotalHeight = jPane.height();
					jPane.children(':visible').each(function() {
						if ($(this).attr('id') != '')
						{
							iTotalHeight -= $(this).height();
						}
					});
					$('#export_content').height(iTotalHeight - 80);
				}
				$('#export_content').val(data.text_result);
				$('#export_text_result').show();
			}
		}
		$('#export-dlg-submit').button('option', 'label', Dict.S('UI:Button:Done')).button('enable');
		break;
		
		case 'error':
		$('#export-form').attr('data-state', 'error');
		$('.export-progress-bar').progressbar({value: data.percentage });
		$('.export-message').html(data.message); 
		$('#export-dlg-submit').button('option', 'label', Dict.S('UI:Button:Done')).button('enable');
		$('#export-btn').hide();
		default:
	}
}
		
function ExportInitButton(sSelector)
{
	$(sSelector).on('click', function() {
		var sDataState = $('#export-form').attr('data-state');
		switch(sDataState)
		{
			case 'not-yet-started':
			$('.form_part:visible').each(function() {
		 		$('#export-form').data('validation_messages', []);
				var ret = $(this).trigger('validate');
			});
			var aMessages = $('#export-form').data('validation_messages');
		
			if(aMessages.length > 0)
			{
				alert(aMessages.join(''));
				return;
			}
			if ($(this).hasClass('ui-button'))
			{
				$(this).button('option', 'label', Dict.S('UI:Button:Cancel'));
			}
			else
			{
				$(this).html(Dict.S('UI:Button:Cancel'));
			}
			$('#export-form').attr('data-state', 'running');
			ExportStartExport();
			break;
		
			case 'running':
			if ($(this).hasClass('ui-button'))
			{
				$(this).button('disable');
			}
			else
			{
				$(this).attr('disabled', 'disabled');
			}
			$('#export-form').attr('data-state', 'cancelled');
			break;
		
			case 'done':
			case 'error':
			$('#interactive_export_dlg').dialog('close');			
			break;
		
			default:
			// Do nothing
		}
	});
}

function DisplayHistory(sSelector, sFilter, iCount, iStart)
{
	$(sSelector).block();
	var oParams = { operation: 'history_from_filter', filter: sFilter, start: iStart, count: iCount };
	$.post(GetAbsoluteUrlAppRoot()+'pages/ajax.render.php', oParams, function(data) {
			$(sSelector).html(data).unblock();
		}
	);
}