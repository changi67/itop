//iTop Search form criteria
;
$(function()
{
	// the widget definition, where 'itop' is the namespace,
	// 'search_form_criteria' the widget name
	$.widget( 'itop.search_form_criteria',
	{
		// default options
		options:
		{
			// Default values for the criteria
			'ref': '',
			'operator': '=',
			'values': [],
			'oql': '',
			'is_removable': true,

			'field': {
				'label': '',
			},

			// Available operators. They can be extended or restricted by derivated widgets (see this._initOperators() for more informations)
			'available_operators': {
				'=': {
					'label': Dict.S('UI:Search:Criteria:Operator:Default:Equals'),
					'code': 'equals',
					'rank': 10,
				},
				'empty': {
					'label': Dict.S('UI:Search:Criteria:Operator:Default:Empty'),
					'code': 'empty',
					'rank': 90,
				},
				'not_empty': {
					'label': Dict.S('UI:Search:Criteria:Operator:Default:NotEmpty'),
					'code': 'not_empty',
					'rank': 100,
				},
			},

			'init_opened': false,
			'is_modified': false, // TODO: change this on value change and remove oql property value
		},

		// Operators
		operators: {},

		// Form handler
		handler: null,
   
		// the constructor
		_create: function()
		{
			var me = this;
			
			this.element.addClass('search_form_criteria');

			// Init operators
			this._initOperators();

			// Link search form handler
			this.handler = this.element.closest('.search_form_handler');

			// Bind events
			this._bindEvents();

			this._prepareElement();
		},
		// called when created, and later when changing options
		_refresh: function()
		{

		},
		// events bound via _bind are removed automatically
		// revert other modifications here
		_destroy: function()
		{
			this.element.removeClass('search_form_criteria');
		},
		// _setOptions is called with a hash of all options that are changing
		// always refresh when changing options
		_setOptions: function()
		{
			this._superApply(arguments);
		},
		// _setOption is called for each individual option that is changing
		_setOption: function( key, value )
		{
			this._super( key, value );
		},


		// Protected methods
		// - Init operators by cleaning up available operators and ordering them.
		//   Note: A null operator or an operator with a rank "false" will be removed.
		_initOperators: function()
		{
			// Reset oprators
			this.operators = {};

			// Temp array to sort operators
			var aSortable = [];
			for(var sOpIdx in this.options.available_operators)
			{
				var oOp = this.options.available_operators[sOpIdx];

				// Some operator can be disabled by the derivated widget, so we check it.
				if(oOp !== null && oOp.rank !== false)
				{
					aSortable.push([sOpIdx, oOp.rank]);
				}
			}

			// Sort the array
			aSortable.sort(function(a, b){
				return a[1] - b[1];
			})

			// Populate this.operators
			for(var iIdx in aSortable)
			{
				var sOpIdx = aSortable[iIdx][0];
				this.operators[sOpIdx] = this.options.available_operators[sOpIdx];
			}
		},
		// - Bind external events
		_bindEvents: function()
		{
			var me = this;

			// Get criteria data
			this.element.bind('itop.search.criteria.get_data', function(oEvent, oData){
				return me._onGetData(oData);
			});

			// Get/SetCurrentValues callbacks handler
			this.element.bind('itop.search.criteria.get_current_values itop.search.criteria.set_current_values', function(oEvent, oData){
				oEvent.stopPropagation();

				var callback = me.options[oEvent.type+'_callback'];

				if(typeof callback === 'string')
				{
					return me[callback](oEvent, oData);
				}
				else if(typeof callback === 'function')
				{
					return callback(me, oEvent, oData);
				}
				else
				{
					console.log('search form criteria: callback type must be a function or a existing function name of the widget');
					return false;
				}
			});
		},
		_apply: function()
		{
			this._trace('TODO: Apply button (call selected operator callback)');
			// Find active operator
			var oActiveOpElem = this.element.find('.sfc_op_radio:checked').closest('.sfc_fg_operator');
			if(oActiveOpElem.length === 0)
			{
				this._trace('Could not apply new value as there seems to be no active operator.');
				return false;
			}

			// Get value from operator (polymorphic method)
			var sCallback = '_get' + this._toCamelCase(oActiveOpElem.attr('data-operator-code')) + 'OperatorValues';
			if(this[sCallback] === undefined)
			{
				this._trace('Callback ' + sCallback + ' is undefined, using _getOperatorValues instead.');
				sCallback = '_getOperatorValues';
			}
			var aValues = this[sCallback](oActiveOpElem);

			// Update widget
			this.options.operator = oActiveOpElem.find('.sfc_op_radio').val();
			this.options.values = aValues;
			this._setTitle();

			// Trigger event to handler
			this.handler.triggerHandler('itop.search.criteria.value_changed');
		},
		_remove: function()
		{
			this.element.remove();
			this.handler.triggerHandler('itop.search.criteria.removed');
		},


		// Event callbacks
		// - Internal events
		_onButtonApply: function()
		{
			this._apply();
		},
		_onButtonCancel: function()
		{
			this._trace('TODO: Cancel button');
		},
		_onButtonMore: function()
		{
			this.element.find('.sfc_form_group').addClass('advanced');
		},
		_onButtonLess: function()
		{
			this.element.find('.sfc_form_group').removeClass('advanced');
		},
		// - External events
		_onGetData: function(oData)
		{
			var oCriteriaData = {
				'ref': this.options.ref,
				'operator': this.options.operator,
				'values': this.options.values,
				'is_removable': this.options.is_removable,
				'oql': this.options.oql,

				// Field data
				'class': this.options.field.class,
				'class_alias': this.options.field.class_alias,
				'code': this.options.field.code,
				'widget': this.options.field.widget,
			};
			return oCriteriaData;
		},


		// DOM element helpers
		// - Prepare element DOM structure
		_prepareElement: function()
		{
			var me = this;

			// Prepare base DOM structure
			this.element
				.append('<div class="sfc_title"></div>')
				.append('<div class="sfc_form_group"><div class="sfc_fg_operators"></div><div class="sfc_fg_buttons"></div></div>')
				.append('<span class="sfc_toggle"><a class="fa fa-caret-down" href="#"></a></span>');

			// Bind events
			// - Toggler
			this.element.find('.sfc_toggle, .sfc_title').on('click', function(){
				// First memorize if current criteria is close
				var bOpen = !me.element.find('.sfc_toggle').hasClass('opened');
				// Then close every criterion
				me.handler.find('.sfc_form_group, .sfc_toggle').removeClass('opened');
				// Finally open current criteria if necessary
				if(bOpen === true)
				{
					me.element.find('.sfc_form_group, .sfc_toggle').toggleClass('opened');
				}
			});

			// Removable / locked decoration
			if(this.options.is_removable === true)
			{
				this.element.append('<span class="sfc_close"><a class="fa fa-times" href="#"></a></span>');
				this.element.find('.sfc_close').on('click', function(){
					me._remove();
				});
			}
			else
			{
				this.element.append('<div class="sfc_locked"><span class="fa fa-lock"></span></div>');
			}

			// Form group
			this._prepareOperators();
			this._prepareButtons();

			// Fill criteria
			// - Title
			this._setTitle();

			// Init opened to improve UX (toggle & focus in main operator's input)
			if(this.options.init_opened === true)
			{
				this.element.find('.sfc_toggle').trigger('click');
				this.element.find('.sfc_fg_operator:first .sfc_op_content input:first').trigger('click').trigger('focus');
			}
		},
		// - Prepare the available operators for the criteria
		//   Meant for overloading.
		_prepareOperators: function()
		{
			for(var sOpIdx in this.operators)
			{
				var oOp = this.operators[sOpIdx];
				var sMethod = '_prepare' + this._toCamelCase(oOp.code) + 'Operator';

				// Create DOM element from template
				var oOpElem = $(this._getOperatorTemplate()).uniqueId();

				// Prepare operator's base elements
				this._prepareOperator(oOpElem, sOpIdx, oOp);

				// Prepare operator's specific elements
				if(this[sMethod] !== undefined)
				{
					this[sMethod](oOpElem, sOpIdx, oOp);
				}
				else
				{
					this._prepareDefaultOperator(oOpElem, sOpIdx, oOp);
				}

				// Append to form group
				oOpElem.appendTo(this.element.find('.sfc_fg_operators'));
			}
		},
		// - Prepare the buttons (DOM and events) for a criteria
		_prepareButtons: function()
		{
			var me = this;

			// DOM elements
			this.element.find('.sfc_fg_buttons')
				.append('<button type="button" name="apply" class="sfc_fg_button sfc_fg_apply">' + Dict.S('UI:Button:Apply') + '</button>')
				.append('<button type="button" name="cancel" class="sfc_fg_button sfc_fg_cancel">' + Dict.S('UI:Button:Cancel') + '</button>')
				.append('<button type="button" name="more" class="sfc_fg_button sfc_fg_more">' + Dict.S('UI:Button:More') + '<span class="fa fa-angle-double-down"></span></button>')
				.append('<button type="button" name="less" class="sfc_fg_button sfc_fg_less">' + Dict.S('UI:Button:Less') + '<span class="fa fa-angle-double-up"></span></button>');

			// Events
			this.element.find('.sfc_fg_button').on('click', function(oEvent){
				oEvent.preventDefault();
				oEvent.stopPropagation();

				var sCallback = '_onButton' + me._toCamelCase($(this).attr('name'));
				me[sCallback]();
			});
		},
		// - Set the title element
		_setTitle: function(sTitle)
		{
			if(sTitle === undefined)
			{
				// TODO: Make nice label
				sTitle = this.options.field.label + ' ' + this.operators[this.options.operator].label + ' ' + this._getValuesAsText();
			}
			this.element.find('.sfc_title').text(sTitle);
		},
		// - Return a HTML template for operators
		_getOperatorTemplate: function()
		{
			return '<div class="sfc_fg_operator"><label><input type="radio" class="sfc_op_radio" name="operator" /><span class="sfc_op_name"></span><span class="sfc_op_content"></span></label></div>';
		},

		// Operators helpers
		_prepareOperator: function(oOpElem, sOpIdx, oOp)
		{
			var sInputId = oOp.code + '_' + oOpElem.attr('id');

			// Set radio
			oOpElem.find('.sfc_op_radio').val(sOpIdx);
			oOpElem.find('.sfc_op_radio').attr('id', sInputId);

			// Set label
			oOpElem.find('.sfc_op_name').text(oOp.label);
			oOpElem.find('> label').attr('for', sInputId);

			// Set helper classes
			oOpElem.addClass('sfc_fg_operator_' + oOp.code)
				.attr('data-operator-code', oOp.code);

			// Bind events
			// - Check radio button on click
			oOpElem.on('click', function(){
				oOpElem.find('.sfc_op_radio').prop('checked', true);
			});
		},
		_prepareDefaultOperator: function(oOpElem, sOpIdx, oOp)
		{
			var me = this;

			// DOM element
			var oOpContentElem = $('<input type="text" />');
			oOpContentElem.val(this._getValuesAsText());

			// Events
			// - Focus input on click (radio, label, ...)
			oOpElem.on('click', ':not(input[type="text"])', function(){
				oOpContentElem.focus();
			});
			// - Apply on "enter" key hit
			oOpContentElem.on('keyup', function(oEvent){
				// Check operator's radio if not already
				if(oOpElem.find('.sfc_op_radio').prop('checked') === false)
				{
					oOpElem.find('.sfc_op_radio').prop('checked', true)
				}

				// Apply if enter key
				if(oEvent.key === 'Enter')
				{
					me._apply();
				}
			});

			oOpElem.find('.sfc_op_content').append(oOpContentElem);
		},
		_prepareEmptyOperator: function(oOpElem, sOpIdx, oOp)
		{
			// Do nothing as only the label is necessary
		},
		_prepareNotEmptyOperator: function(oOpElem, sOpIdx, oOp)
		{
			// Do nothing as only the label is necessary
		},
		// - Fallback for operators without a specific callback
		_getOperatorValues: function(oOpElem)
		{
			var aValues = [];

			oOpElem.find('.sfc_op_content input').each(function(){
				var sValue = $(this).val();
				aValues.push({value: sValue, label: sValue});
			});

			return aValues;
		},


		// Values helpers
		// - Convert values to a standard string
		_getValuesAsText: function()
		{
			var aValues = [];
			for(var iValueIdx in this.options.values)
			{
				aValues.push(this.options.values[iValueIdx].label);
			}

			return aValues.join(', ');
		},
		// - Make an OQL expression from the criteria values and operator
		_makeOQLExpression: function()
		{
			var aValues = [];
			var sOQL = '';

			for(var iValueIdx in this.options.values)
			{
				aValues.push( '\'' + this.options.values[iValueIdx].value + '\'' );
			}
			sOQL += '(`' + this.options.ref + '`) ' + this.options.operator + ' ' + aValues.join(', ') + ')';

			return sOQL;
		},


		// Global helpers
		// - Converts a snake_case string to CamelCase
		_toCamelCase: function(sString)
		{
			var aParts = sString.split('_');

			for(var i in aParts)
			{
				aParts[i] = aParts[i].charAt(0).toUpperCase() + aParts[i].substr(1);
			}

			return aParts.join('');
		},


		// Debug helpers
		// - Show a trace in the javascript console
		_trace: function(sMessage, oData)
		{
			if(window.console)
			{
				if(oData !== undefined)
				{
					console.log('Search form criteria: ' + sMessage, oData);
				}
				else
				{
					console.log('Search form criteria: ' + sMessage);
				}
			}
		},
		// - Show current options
		showOptions: function()
		{
			this._trace('Options', this.options);
		}
	});
});
