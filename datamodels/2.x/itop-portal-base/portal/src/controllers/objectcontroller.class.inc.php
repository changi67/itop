<?php

// Copyright (C) 2010-2015 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>

namespace Combodo\iTop\Portal\Controller;

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Exception;
use \FileUploadException;
use \utils;
use \Dict;
use \MetaModel;
use \DBSearch;
use \DBObjectSearch;
use \BinaryExpression;
use \FieldExpression;
use \VariableExpression;
use \DBObjectSet;
use \cmdbAbstractObject;
use \UserRights;
use \Combodo\iTop\Portal\Helper\ApplicationHelper;
use \Combodo\iTop\Portal\Helper\SecurityHelper;
use \Combodo\iTop\Portal\Helper\ContextManipulatorHelper;
use \Combodo\iTop\Portal\Form\ObjectFormManager;
use \Combodo\iTop\Renderer\Bootstrap\BsFormRenderer;

/**
 * Controller to handle basic view / edit / create of cmdbAbstractObject
 */
class ObjectController extends AbstractController
{

	const ENUM_MODE_VIEW = 'view';
	const ENUM_MODE_EDIT = 'edit';
	const ENUM_MODE_CREATE = 'create';
	const DEFAULT_COUNT_PER_PAGE_LIST = 10;

	/**
	 * Displays an cmdbAbstractObject if the connected user is allowed to.
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sObjectClass (Class must be instance of cmdbAbstractObject)
	 * @param string $sObjectId
	 * @return Response
	 */
	public function ViewAction(Request $oRequest, Application $oApp, $sObjectClass, $sObjectId)
	{
		// Checking parameters
		if ($sObjectClass === '' || $sObjectId === '')
		{
			$oApp->abort(500, Dict::Format('UI:Error:2ParametersMissing', 'class', 'id'));
		}

		// Checking security layers
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $sObjectClass, $sObjectId))
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Retrieving object
		$oObject = MetaModel::GetObject($sObjectClass, $sObjectId, false /* MustBeFound */);
		if ($oObject === null)
		{
			// We should never be there as the secuirty helper makes sure that the object exists, but just in case.
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		$aData = array('sMode' => 'view');
		$aData['form'] = $this->HandleForm($oRequest, $oApp, $aData['sMode'], $sObjectClass, $sObjectId);
		$aData['form']['title'] = Dict::Format('Brick:Portal:Object:Form:View:Title', MetaModel::GetName($sObjectClass), $oObject->GetName());

		// Preparing response
		if ($oRequest->isXmlHttpRequest())
		{
			// We have to check whether the 'operation' parameter is defined or not in order to know if the form is required via ajax (to be displayed as a modal dialog) or if it's a lifecycle call from a existing form.
			if ($oRequest->request->get('operation') === null)
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				$oResponse = $oApp->json($aData);
			}
		}
		else
		{
			// Adding brick if it was passed
			$sBrickId = $oRequest->get('sBrickId');
			if ($sBrickId !== null)
			{
				$oBrick = ApplicationHelper::GetLoadedBrickFromId($oApp, $sBrickId);
				if ($oBrick !== null)
				{
					$aData['oBrick'] = $oBrick;
				}
			}
			$aData['sPageTitle'] = $aData['form']['title'];
			$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
		}

		return $oResponse;
	}

	public function EditAction(Request $oRequest, Application $oApp, $sObjectClass, $sObjectId)
	{
		// Checking parameters
		if ($sObjectClass === '' || $sObjectId === '')
		{
			$oApp->abort(500, Dict::Format('UI:Error:2ParametersMissing', 'class', 'id'));
		}
		
		// Checking security layers
		// Warning : This is a dirty quick fix to allow editing its own contact information
		$bAllowWrite = ($sObjectClass === 'Person' && $sObjectId == UserRights::GetContactId());
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_MODIFY, $sObjectClass, $sObjectId) && !$bAllowWrite)
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Retrieving object
		$oObject = MetaModel::GetObject($sObjectClass, $sObjectId, false /* MustBeFound */);
		if ($oObject === null)
		{
			// We should never be there as the secuirty helper makes sure that the object exists, but just in case.
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		$aData = array('sMode' => 'edit');
		$aData['form'] = $this->HandleForm($oRequest, $oApp, $aData['sMode'], $sObjectClass, $sObjectId);
		$aData['form']['title'] = Dict::Format('Brick:Portal:Object:Form:Edit:Title', MetaModel::GetName($sObjectClass), $aData['form']['object_name']);

		// Preparing response
		if ($oRequest->isXmlHttpRequest())
		{
			// We have to check whether the 'operation' parameter is defined or not in order to know if the form is required via ajax (to be displayed as a modal dialog) or if it's a lifecycle call from a existing form.
			if ($oRequest->request->get('operation') === null)
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				$oResponse = $oApp->json($aData);
			}
		}
		else
		{
			// Adding brick if it was passed
			$sBrickId = $oRequest->get('sBrickId');
			if ($sBrickId !== null)
			{
				$oBrick = ApplicationHelper::GetLoadedBrickFromId($oApp, $sBrickId);
				if ($oBrick !== null)
				{
					$aData['oBrick'] = $oBrick;
				}
			}
			$aData['sPageTitle'] = $aData['form']['title'];
			$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
		}

		return $oResponse;
	}

	/**
	 * Creates an cmdbAbstractObject of the $sObjectClass
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sObjectClass
	 * @return Response
	 */
	public function CreateAction(Request $oRequest, Application $oApp, $sObjectClass)
	{
		// Checking security layers
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_CREATE, $sObjectClass))
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		$aData = array('sMode' => 'create');
		$aData['form'] = $this->HandleForm($oRequest, $oApp, $aData['sMode'], $sObjectClass);
		$aData['form']['title'] = Dict::Format('Brick:Portal:Object:Form:Create:Title', MetaModel::GetName($sObjectClass));

		// Preparing response
		if ($oRequest->isXmlHttpRequest())
		{
			// We have to check whether the 'operation' parameter is defined or not in order to know if the form is required via ajax (to be displayed as a modal dialog) or if it's a lifecycle call from a existing form.
			if ($oRequest->request->get('operation') === null)
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				$oResponse = $oApp->json($aData);
			}
		}
		else
		{
			// Adding brick if it was passed
			$sBrickId = $oRequest->get('sBrickId');
			if ($sBrickId !== null)
			{
				$oBrick = ApplicationHelper::GetLoadedBrickFromId($oApp, $sBrickId);
				if ($oBrick !== null)
				{
					$aData['oBrick'] = $oBrick;
				}
			}
			$aData['sPageTitle'] = $aData['form']['title'];
			$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
		}

		return $oResponse;
	}

	/**
	 * Creates an cmdbAbstractObject of a class determined by the method encoded in $sEncodedMethodName.
	 * This method use an origin DBObject in order to determine the created cmdbAbstractObject.
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sObjectClass Class of the origin object
	 * @param string $sObjectId ID of the origin object
	 * @param string $sEncodedMethodName Base64 encoded factory method name
	 * @return Response
	 */
	public function CreateFromFactoryAction(Request $oRequest, Application $oApp, $sObjectClass, $sObjectId, $sEncodedMethodName)
	{
		$sMethodName = base64_decode($sEncodedMethodName);

		// Checking that the factory method is valid
		if (!is_callable($sMethodName))
		{
			$oApp->abort(500, 'Invalid factory method "' . $sMethodName . '" used when creating an object');
		}
		
		// Retrieving origin object
		$oOriginObject = MetaModel::GetObject($sObjectClass, $sObjectId);
		
		// Retrieving target object (We check if the method is a simple function or if it's part of a class in which case only static function are supported)
		if (!strpos($sMethodName, '::'))
		{
			$sTargetObject = $sMethodName($oOriginObject);
		}
		else
		{
			$aMethodNameParts = explode('::', $sMethodName);
			$sTargetObject = $aMethodNameParts[0]::$aMethodNameParts[1]($oOriginObject);
		}

		// Preparing redirection
		// - Route
		$aRouteParams = array(
			'sObjectClass' => get_class($sTargetObject)
		);
		$sRedirectRoute = $oApp['url_generator']->generate('p_object_create', $aRouteParams);
		// - Request
		$oSubRequest = Request::create($sRedirectRoute, 'GET', $oRequest->query->all(), $oRequest->cookies->all(), array(), $oRequest->server->all());

		return $oApp->handle($oSubRequest, HttpKernelInterface::SUB_REQUEST, true);
	}

	/**
	 * Applies a stimulus $sStimulus on an cmdbAbstractObject
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sObjectClass
	 * @param string $sObjectId
	 * @param string $sStimulusCode
	 * @return Response
	 */
	public function ApplyStimulusAction(Request $oRequest, Application $oApp, $sObjectClass, $sObjectId, $sStimulusCode)
	{
		// Checking parameters
		if ($sObjectClass === '' || $sObjectId === '' || $sStimulusCode === '')
		{
			$oApp->abort(500, Dict::Format('UI:Error:3ParametersMissing', 'class', 'id', 'stimulus'));
		}

		// Checking security layers
		// TODO : This should call the stimulus check in the security helper
//		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_MODIFY, $sObjectClass, $sObjectId))
//		{
//			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
//		}
		
		// Retrieving object
		$oObject = MetaModel::GetObject($sObjectClass, $sObjectId, false /* MustBeFound */);
		if ($oObject === null)
		{
			// We should never be there as the secuirty helper makes sure that the object exists, but just in case.
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Preparing a dedicated form for the stimulus application
		$aFormProperties = array(
			'id' => 'apply-stimulus',
			'type' => 'static',
			'fields' => array(),
			'layout' => null
		);
		// Checking which fields need to be prompt
		$aTransitions = MetaModel::EnumTransitions($sObjectClass, $oObject->GetState());
		$aTargetStates = MetaModel::EnumStates($sObjectClass);
		$aTargetState = $aTargetStates[$aTransitions[$sStimulusCode]['target_state']];
		$aExpectedAttributes = $aTargetState['attribute_list'];
		foreach ($aExpectedAttributes as $sAttCode => $iFlags)
		{
			if (($iFlags & (OPT_ATT_MUSTCHANGE | OPT_ATT_MUSTPROMPT)) ||
				(($iFlags & OPT_ATT_MANDATORY) && ($oObject->Get($sAttCode) == '')))
			{
				$aFormProperties['fields'][$sAttCode] = array();
				// Settings flags for the field
				if ($iFlags & OPT_ATT_MUSTCHANGE)
					$aFormProperties['fields'][$sAttCode]['must_change'] = true;
				if ($iFlags & OPT_ATT_MUSTPROMPT)
					$aFormProperties['fields'][$sAttCode]['must_prompt'] = true;
				if (($iFlags & OPT_ATT_MANDATORY) && ($oObject->Get($sAttCode) == ''))
					$aFormProperties['fields'][$sAttCode]['mandatory'] = true;
			}
		}
		// Adding target_state to current_values
		$oRequest->request->set('apply_stimulus', array('code' => $sStimulusCode));

		$aData = array('sMode' => 'apply_stimulus');
		$aData['form'] = $this->HandleForm($oRequest, $oApp, $aData['sMode'], $sObjectClass, $sObjectId, $aFormProperties);
		$aData['form']['title'] = Dict::Format('Brick:Portal:Object:Form:Stimulus:Title');
		$aData['form']['validation']['redirection'] = array(
			'url' => $oApp['url_generator']->generate('p_object_edit', array('sObjectClass' => $sObjectClass, 'sObjectId' => $sObjectId))
		);

		// Preparing response
		if ($oRequest->isXmlHttpRequest())
		{
			// We have to check whether the 'operation' parameter is defined or not in order to know if the form is required via ajax (to be displayed as a modal dialog) or if it's a lifecycle call from a existing form.
			if ($oRequest->request->get('operation') === null)
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				$oResponse = $oApp->json($aData);
			}
		}
		else
		{
			$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
		}

		return $oResponse;
	}

	public static function HandleForm(Request $oRequest, Application $oApp, $sMode, $sObjectClass, $sObjectId = null, $aFormProperties = null)
	{
		$aFormData = array();
		$oRequestParams = $oRequest->request;
		$sOperation = $oRequestParams->get('operation');
		$bModal = ($oRequest->isXmlHttpRequest() && ($oRequest->request->get('operation') === null) );

		// - Retrieve form properties
		if ($aFormProperties === null)
		{
			$aFormProperties = ApplicationHelper::GetLoadedFormFromClass($oApp, $sObjectClass, $sMode);
		}

		// - Create and
		if ($sOperation === null)
		{
			// Retrieving action rules
			//
			// Note : The action rules must be a base64-encoded JSON object, this is just so users are tempted to changes values.
			// But it would not be a security issue as it only presets values in the form.
			$sActionRulesToken = $oRequest->get('ar_token');
			$aActionRules = ($sActionRulesToken !== null) ? ContextManipulatorHelper::DecodeRulesToken($sActionRulesToken) : array();

			// Preparing object
			if ($sObjectId === null)
			{
				// Create new UserRequest
				$oObject = MetaModel::NewObject($sObjectClass);

				// Retrieve action rules information to auto-fill the form if available
				// Preparing object
				$oApp['context_manipulator']->PrepareObject($aActionRules, $oObject);
			}
			else
			{
				$oObject = MetaModel::GetObject($sObjectClass, $sObjectId);
			}

			// Preparing transitions only if we are currently going through one
			$aFormData['buttons'] = array(
				'transitions' => array()
			);
			if ($sMode !== 'apply_stimulus')
			{
				$oSetToCheckRights = DBObjectSet::FromObject($oObject);
				$aStimuli = Metamodel::EnumStimuli($sObjectClass);
				foreach ($oObject->EnumTransitions() as $sStimulusCode => $aTransitionDef)
				{
					$iActionAllowed = (get_class($aStimuli[$sStimulusCode]) == 'StimulusUserAction') ? UserRights::IsStimulusAllowed($sObjectClass, $sStimulusCode, $oSetToCheckRights) : UR_ALLOWED_NO;
					// Careful, $iAction is an integer whereas UR_ALLOWED_YES is a boolean, therefore we can't use a '===' operator.
					if ($iActionAllowed == UR_ALLOWED_YES)
					{
						$aFormData['buttons']['transitions'][$sStimulusCode] = $aStimuli[$sStimulusCode]->GetLabel();
					}
				}
			}
			// Preparing callback urls
			$aCallbackUrls = $oApp['context_manipulator']->GetCallbackUrls($oApp, $aActionRules, $oObject, $bModal);
			$aFormData['submit_callback'] = $aCallbackUrls['submit'];
			$aFormData['cancel_callback'] = $aCallbackUrls['cancel'];

			// Preparing renderer
			// Note : We might need to distinguish form & renderer endpoints
			if (in_array($sMode, array('create', 'edit', 'view')))
			{
				$sFormEndpoint = $oApp['url_generator']->generate('p_object_' . $sMode, array('sObjectClass' => $sObjectClass, 'sObjectId' => $sObjectId));
			}
			else
			{
				$sFormEndpoint = $_SERVER['REQUEST_URI'];
			}
			$oFormRenderer = new BsFormRenderer();
			$oFormRenderer->SetEndpoint($sFormEndpoint);

			$oFormManager = new ObjectFormManager();
			$oFormManager->SetApplication($oApp)
				->SetObject($oObject)
				->SetMode($sMode)
				->SetActionRulesToken($sActionRulesToken)
				->SetRenderer($oFormRenderer)
				->SetFormProperties($aFormProperties)
				->Build();
			
			// Check the number of editable fields
			$aFormData['editable_fields_count'] = $oFormManager->GetForm()->GetEditableFieldCount();
		}
		else
		{
			// Update / Submit / Cancel
			$sFormManagerClass = $oRequestParams->get('formmanager_class');
			$sFormManagerData = $oRequestParams->get('formmanager_data');
			if ($sFormManagerClass === null || $sFormManagerData === null)
			{
				$oApp->abort(500, 'Parameters formmanager_class and formmanager_data must be defined.');
			}

			$oFormManager = $sFormManagerClass::FromJSON($sFormManagerData);
			$oFormManager->SetApplication($oApp);
			
			// Applying action rules if present
			if (($oFormManager->GetActionRulesToken() !== null) && ($oFormManager->GetActionRulesToken() !== ''))
			{
				$aActionRules = ContextManipulatorHelper::DecodeRulesToken($oFormManager->GetActionRulesToken());
				$oObj = $oFormManager->GetObject();
				$oApp['context_manipulator']->PrepareObject($aActionRules, $oObj);
				$oFormManager->SetObject($oObj);
			}
			
			switch ($sOperation)
			{
				case 'submit':
					// Applying modification to object
					$aFormData['validation'] = $oFormManager->OnSubmit(array('currentValues' => $oRequestParams->get('current_values'), 'attachmentIds' => $oRequest->get('attachment_ids'), 'formProperties' => $aFormProperties, 'applyStimulus' => $oRequestParams->get('apply_stimulus')));
					if ($aFormData['validation']['valid'] === true)
					{
						// Note : We don't use $sObjectId there as it can be null if we are creating a new one. Instead we use the id from the created object once it has been seralized
						// Check if stimulus has to be applied
						$sStimulusCode = ($oRequestParams->get('stimulus_code') !== null && $oRequestParams->get('stimulus_code') !== '') ? $oRequestParams->get('stimulus_code') : null;
						if ($sStimulusCode !== null)
						{
							$aFormData['validation']['redirection'] = array(
								'url' => $oApp['url_generator']->generate('p_object_apply_stimulus', array('sObjectClass' => $sObjectClass, 'sObjectId' => $oFormManager->GetObject()->GetKey(), 'sStimulusCode' => $sStimulusCode)),
								'ajax' => true
							);
						}
						// Otherwise, we show the object if there is no default
						else
						{
//							$aFormData['validation']['redirection'] = array(
//								'alternative_url' => $oApp['url_generator']->generate('p_object_edit', array('sObjectClass' => $sObjectClass, 'sObjectId' => $oFormManager->GetObject()->GetKey()))
//							);
						}
					}
					break;

				case 'update':
					$oFormManager->OnUpdate(array('currentValues' => $oRequestParams->get('current_values'), 'formProperties' => $aFormProperties));
					break;

				case 'cancel':
					$oFormManager->OnCancel();
					break;
			}
		}
		
		// Preparing field_set data
		$aFieldSetData = array(
			//'fields_list' => $oFormManager->GetRenderer()->Render(), // GLA : This should be done just after in the if statement.
			'fields_impacts' => $oFormManager->GetForm()->GetFieldsImpacts(),
			'form_path' => $oFormManager->GetForm()->GetId()
		);

		// Preparing fields list regarding the operation
		if ($sOperation === 'update')
		{
			$aRequestedFields = $oRequestParams->get('requested_fields');
			$sFormPath = $oRequestParams->get('form_path');

			// Checking if the update was on a subform, if so we need to make the rendering for that part only
			if ($sFormPath !== null && $sFormPath !== $oFormManager->GetForm()->GetId())
			{
				$oSubForm = $oFormManager->GetForm()->FindSubForm($sFormPath);
				$oSubFormRenderer = new BsFormRenderer($oSubForm);
				$oSubFormRenderer->SetEndpoint($oFormManager->GetRenderer()->GetEndpoint());
				$aFormData['updated_fields'] = $oSubFormRenderer->Render($aRequestedFields);
			}
			else
			{
				$aFormData['updated_fields'] = $oFormManager->GetRenderer()->Render($aRequestedFields);
			}
		}
		else
		{
			$aFieldSetData['fields_list'] = $oFormManager->GetRenderer()->Render();
		}

		// Preparing form data
		$aFormData['id'] = $oFormManager->GetForm()->GetId();
		$aFormData['transaction_id'] = $oFormManager->GetForm()->GetTransactionId();
		$aFormData['formmanager_class'] = $oFormManager->GetClass();
		$aFormData['formmanager_data'] = $oFormManager->ToJSON();
		$aFormData['renderer'] = $oFormManager->GetRenderer();
		$aFormData['object_name'] = $oFormManager->GetObject()->GetName();
		$aFormData['fieldset'] = $aFieldSetData;

		return $aFormData;
	}

	/**
	 * Handles the autocomplete search
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sTargetAttCode Attribute code of the host object pointing to the Object class to search
	 * @param string $sHostObjectClass Class name of the host object
	 * @param string $sHostObjectId Id of the host object
	 * @return Response
	 */
	public function SearchAutocompleteAction(Request $oRequest, Application $oApp, $sTargetAttCode, $sHostObjectClass, $sHostObjectId = null)
	{
		$aData = array(
			'results' => array(
				'count' => 0,
				'items' => array()
			)
		);

		// Parsing parameters from request payload
		parse_str($oRequest->getContent(), $aRequestContent);

		// Checking parameters
		if (!isset($aRequestContent['sQuery']))
		{
			$oApp->abort(500, Dict::Format('UI:Error:ParameterMissing', 'sQuery'));
		}

		// Retrieving parameters
		$sQuery = $aRequestContent['sQuery'];

		// Checking security layers
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $sHostObjectClass, $sHostObjectId))
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Retrieving host object for future DBSearch parameters
		if ($sHostObjectId !== null)
		{
			$oHostObject = MetaModel::GetObject($sHostObjectClass, $sHostObjectId);
		}
		else
		{
			$oHostObject = MetaModel::NewObject($sHostObjectClass);
		}

		// Building search query
		// - Retrieving target object class from attcode
		$oTargetAttDef = MetaModel::GetAttributeDef($sHostObjectClass, $sTargetAttCode);
		$sTargetObjectClass = $oTargetAttDef->GetTargetClass();
		// - Base query from meta model
		$oSearch = DBSearch::FromOQL($oTargetAttDef->GetValuesDef()->GetFilterExpression());
		// - Adding query condition
		$oSearch->AddConditionExpression(new BinaryExpression(new FieldExpression('friendlyname', $oSearch->GetClassAlias()), 'LIKE', new VariableExpression('ac_query')));
		// - Intersecting with scope constraints
		$oSearch->Intersect($oApp['scope_validator']->GetScopeFilterForProfiles(UserRights::ListProfiles(), $sTargetObjectClass, UR_ACTION_READ));

		// Retrieving results
		// - Preparing object set
		$oSet = new DBObjectSet($oSearch, array(), array('this' => $oHostObject, 'ac_query' => '%' . $sQuery . '%'));
		$oSet->OptimizeColumnLoad(array($oSearch->GetClassAlias() => array('friendlyname')));
		// Note : This limit is also used in the field renderer by typeahead to determine how many suggestions to display
		$oSet->SetLimit($oTargetAttDef->GetMaximumComboLength()); // TODO : Is this the right limit value ? We might want to use another parameter
		// - Retrieving objects
		while ($oItem = $oSet->Fetch())
		{
			$aData['results']['items'][] = array('id' => $oItem->GetKey(), 'name' => $oItem->GetName());
			$aData['results']['count'] ++;
		}

		// Preparing response
		if ($oRequest->isXmlHttpRequest())
		{
			$oResponse = $oApp->json($aData);
		}
		else
		{
			$oResponse = $oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		return $oResponse;
	}

	/**
	 * Handles the regular (table) search from an attribute
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sTargetAttCode Attribute code of the host object pointing to the Object class to search
	 * @param string $sHostObjectClass Class name of the host object
	 * @param string $sHostObjectId Id of the host object
	 * @return Response
	 */
	public function SearchFromAttributeAction(Request $oRequest, Application $oApp, $sTargetAttCode, $sHostObjectClass, $sHostObjectId = null)
	{
		$aData = array(
			'sMode' => 'search_regular',
			'sTargetAttCode' => $sTargetAttCode,
			'sHostObjectClass' => $sHostObjectClass,
			'sHostObjectId' => $sHostObjectId
		);

		// Checking security layers
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $sHostObjectClass, $sHostObjectId))
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Retrieving host object for future DBSearch parameters
		if ($sHostObjectId !== null)
		{
			$oHostObject = MetaModel::GetObject($sHostObjectClass, $sHostObjectId);
		}
		else
		{
			$oHostObject = MetaModel::NewObject($sHostObjectClass);
		}

		// Retrieving request parameters
		$iPageNumber = ($oRequest->get('iPageNumber') !== null) ? $oRequest->get('iPageNumber') : 1;
		$iCountPerPage = ($oRequest->get('iCountPerPage') !== null) ? $oRequest->get('iCountPerPage') : static::DEFAULT_COUNT_PER_PAGE_LIST;
		$bInitalPass = ($oRequest->get('draw') === null) ? true : false;
		$sQuery = $oRequest->get('sSearchValue');
		$sFormPath = $oRequest->get('sFormPath');
		$sFieldId = $oRequest->get('sFieldId');

		// Building search query
		// - Retrieving target object class from attcode
		$oTargetAttDef = MetaModel::GetAttributeDef($sHostObjectClass, $sTargetAttCode);
		if ($oTargetAttDef->IsExternalKey())
		{
			$sTargetObjectClass = $oTargetAttDef->GetTargetClass();
		}
		elseif ($oTargetAttDef->IsLinkSet())
		{
			if (!$oTargetAttDef->IsIndirect())
			{
				$sTargetObjectClass = $oTargetAttDef->GetLinkedClass();
			}
			else
			{
				$oRemoteAttDef = MetaModel::GetAttributeDef($oTargetAttDef->GetLinkedClass(), $oTargetAttDef->GetExtKeyToRemote());
				$sTargetObjectClass = $oRemoteAttDef->GetTargetClass();
			}
		}
		else
		{
			throw new Exception('Search from attribute can only apply on AttributeExternalKey or AttributeLinkedSet objects, ' . get_class($oTargetAttDef) . ' given.');
		}

		// - Retrieving class attribute list
		$aAttCodes = MetaModel::FlattenZList(MetaModel::GetZListItems($sTargetObjectClass, 'list'));
		// - Adding friendlyname attribute to the list is not already in it
		$sTitleAttCode = MetaModel::GetFriendlyNameAttributeCode($sTargetObjectClass);
		if (!in_array($sTitleAttCode, $aAttCodes))
		{
			$aAttCodes = array_merge(array($sTitleAttCode), $aAttCodes);
		}

		// - Retrieving scope search
		$oScopeSearch = $oApp['scope_validator']->GetScopeFilterForProfiles(UserRights::ListProfiles(), $sTargetObjectClass, UR_ACTION_READ);
		if ($oScopeSearch === null)
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// - Base query from meta model
		if ($oTargetAttDef->IsExternalKey())
		{
			$oSearch = DBSearch::FromOQL($oTargetAttDef->GetValuesDef()->GetFilterExpression());
		}
		elseif ($oTargetAttDef->IsLinkSet())
		{
			$oSearch = $oScopeSearch;
		}

		// - Adding query condition
		$aInternalParams = array('this' => $oHostObject);
		if ($sQuery !== null)
		{
			$oFullExpr = null;
			for ($i = 0; $i < count($aAttCodes); $i++)
			{
				// Checking if the current attcode is an external key in order to search on the friendlyname
				$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $aAttCodes[$i]);
				$sAttCode = (!$oAttDef->IsExternalKey()) ? $aAttCodes[$i] : $aAttCodes[$i] . '_friendlyname';
				// Building expression for the current attcode
				$oBinExpr = new BinaryExpression(new FieldExpression($sAttCode, $oSearch->GetClassAlias()), 'LIKE', new VariableExpression('re_query'));
				// Adding expression to the full expression (all attcodes)
				if ($i === 0)
				{
					$oFullExpr = $oBinExpr;
				}
				else
				{
					$oFullExpr = new BinaryExpression($oFullExpr, 'OR', $oBinExpr);
				}
			}
			// Adding full expression to the search object
			$oSearch->AddConditionExpression($oFullExpr);
			$aInternalParams['re_query'] = '%' . $sQuery . '%';
		}

		// - Intersecting with scope constraints
		$oSearch->Intersect($oScopeSearch);

		// Retrieving results
		// - Preparing object set
		$oSet = new DBObjectSet($oSearch, array(), $aInternalParams);
		$oSet->OptimizeColumnLoad(array($oSearch->GetClassAlias() => $aAttCodes));
		$oSet->SetLimit($iCountPerPage, $iCountPerPage * ($iPageNumber - 1));
		// - Retrieving columns properties
		$aColumnProperties = array();
		foreach ($aAttCodes as $sAttCode)
		{
			$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $sAttCode);
			$aColumnProperties[$sAttCode] = array(
				'title' => $oAttDef->GetLabel()
			);
		}
		// - Retrieving objects
		$aItems = array();
		while ($oItem = $oSet->Fetch())
		{
			$aItemProperties = array(
				'id' => $oItem->GetKey(),
				'name' => $oItem->GetName(),
				'attributes' => array()
			);

			foreach ($aAttCodes as $sAttCode)
			{
				if ($sAttCode !== 'id')
				{
					$aAttProperties = array(
						'att_code' => $sAttCode
					);

					$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $sAttCode);
					if ($oAttDef->IsExternalKey())
					{
						$aAttProperties['value'] = $oItem->Get($sAttCode . '_friendlyname');
						// Checking if we can view the object
						if ((SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $oAttDef->GetTargetClass(), $oItem->Get($sAttCode))))
						{
							$aAttProperties['url'] = $oApp['url_generator']->generate('p_object_view', array('sObjectClass' => $oAttDef->GetTargetClass(), 'sObjectId' => $oItem->GetKey()));
						}
					}
					else
					{
						$aAttProperties['value'] = $oAttDef->GetValueLabel($oItem->Get($sAttCode));
					}

					$aItemProperties['attributes'][$sAttCode] = $aAttProperties;
				}
			}

			$aItems[] = $aItemProperties;
		}
		
		// Preparing response
		if ($bInitalPass)
		{
			$aData = $aData + array(
				'form' => array(
					'id' => 'object_search_form_' . time(),
					'title' => Dict::Format('Brick:Portal:Object:Search:Regular:Title', $oTargetAttDef->GetLabel(), MetaModel::GetName($sTargetObjectClass))
				),
				'aColumnProperties' => json_encode($aColumnProperties),
				'aResults' => array(
					'aItems' => json_encode($aItems),
					'iCount' => count($aItems)
				),
				'bMultipleSelect' => $oTargetAttDef->IsLinkSet(),
				'aSource' => array(
					'sFormPath' => $sFormPath,
					'sFieldId' => $sFieldId
				)
			);

			if ($oRequest->isXmlHttpRequest())
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				//$oResponse = $oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
			}
		}
		else
		{
			$aData = $aData + array(
				'levelsProperties' => $aColumnProperties,
				'data' => $aItems,
				'recordsTotal' => $oSet->Count(),
				'recordsFiltered' => $oSet->Count()
			);

			$oResponse = $oApp->json($aData);
		}

		return $oResponse;
	}

	/**
	 * Handles the hierarchical search from an attribute
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @param string $sTargetAttCode Attribute code of the host object pointing to the Object class to search
	 * @param string $sHostObjectClass Class name of the host object
	 * @param string $sHostObjectId Id of the host object
	 * @return Response
	 */
	public function SearchHierarchyAction(Request $oRequest, Application $oApp, $sTargetAttCode, $sHostObjectClass, $sHostObjectId = null)
	{
		$aData = array(
			'sMode' => 'search_hierarchy',
			'sTargetAttCode' => $sTargetAttCode,
			'sHostObjectClass' => $sHostObjectClass,
			'sHostObjectId' => $sHostObjectId
		);

		// Checking security layers
		if (!SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $sHostObjectClass, $sHostObjectId))
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// Retrieving host object for future DBSearch parameters
		if ($sHostObjectId !== null)
		{
			$oHostObject = MetaModel::GetObject($sHostObjectClass, $sHostObjectId);
		}
		else
		{
			$oHostObject = MetaModel::NewObject($sHostObjectClass);
		}

		// Retrieving request parameters
		$bInitalPass = ($oRequest->get('draw') === null) ? true : false;
		$sQuery = $oRequest->get('sSearchValue'); // Note : Not used yet
		$sFormPath = $oRequest->get('sFormPath');
		$sFieldId = $oRequest->get('sFieldId');

		// Building search query
		// - Retrieving target object class from attcode
		$oTargetAttDef = MetaModel::GetAttributeDef($sHostObjectClass, $sTargetAttCode);
		if ($oTargetAttDef->IsExternalKey())
		{
			$sTargetObjectClass = $oTargetAttDef->GetTargetClass();
		}
		elseif ($oTargetAttDef->IsLinkSet())
		{
			if (!$oTargetAttDef->IsIndirect())
			{
				$sTargetObjectClass = $oTargetAttDef->GetLinkedClass();
			}
			else
			{
				$oRemoteAttDef = MetaModel::GetAttributeDef($oTargetAttDef->GetLinkedClass(), $oTargetAttDef->GetExtKeyToRemote());
				$sTargetObjectClass = $oRemoteAttDef->GetTargetClass();
			}
		}
		else
		{
			throw new Exception('Search by hierarchy can only apply on AttributeExternalKey or AttributeLinkedSet objects, ' . get_class($oTargetAttDef) . ' given.');
		}

//		// - Retrieving class attribute list
//		$aAttCodes = MetaModel::FlattenZList(MetaModel::GetZListItems($sTargetObjectClass, 'list'));
//		// - Adding friendlyname attribute to the list is not already in it
//		$sTitleAttrCode = MetaModel::GetFriendlyNameAttributeCode($sTargetObjectClass);
//		if (!in_array($sTitleAttrCode, $aAttCodes))
//		{
//			$aAttCodes = array_merge(array($sTitleAttrCode), $aAttCodes);
//		}
		// - Retrieving scope search
		$oScopeSearch = $oApp['scope_validator']->GetScopeFilterForProfiles(UserRights::ListProfiles(), $sTargetObjectClass, UR_ACTION_READ);
		if ($oScopeSearch === null)
		{
			$oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
		}

		// - Base query from meta model
		if ($oTargetAttDef->IsExternalKey())
		{
			$oSearch = DBSearch::FromOQL($oTargetAttDef->GetValuesDef()->GetFilterExpression());
		}
//		elseif ($oTargetAttDef->IsLinkSet())
		else
		{
			$oSearch = $oScopeSearch;
		}

//		// - Adding query condition
		$aInternalParams = array('this' => $oHostObject);
//		if ($sQuery !== null)
//		{
//			for ($i = 0; $i < count($aAttCodes); $i++)
//			{
//				// Checking if the current attcode is an external key in order to search on the friendlyname
//				$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $aAttCodes[$i]);
//				$sAttCode = (!$oAttDef->IsExternalKey()) ? $aAttCodes[$i] : $aAttCodes[$i] . '_friendlyname';
//				// Building expression for the current attcode
//				$oBinExpr = new BinaryExpression(new FieldExpression($sAttCode, $oSearch->GetClassAlias()), 'LIKE', new VariableExpression('re_query'));
//				// Adding expression to the full expression (all attcodes)
//				if ($i === 0)
//				{
//					$oFullExpr = $oBinExpr;
//				}
//				else
//				{
//					$oFullExpr = new BinaryExpression($oFullExpr, 'OR', $oBinExpr);
//				}
//			}
//			// Adding full expression to the search object
//			$oSearch->AddConditionExpression($oFullExpr);
//			$aInternalParams['re_query'] = '%' . $sQuery . '%';
//		}
		// - Intersecting with scope constraints
		$oSearch->Intersect($oScopeSearch);

		// Retrieving results
		// - Preparing object set
		$oSet = new DBObjectSet($oSearch, array(), $aInternalParams);
		$oSet->OptimizeColumnLoad(array($oSearch->GetClassAlias() => array('friendlyname')));
//		$oSet->SetLimit($iCountPerPage, $iCountPerPage * ($iPageNumber - 1));
//		// - Retrieving columns properties
//		$aColumnProperties = array();
//		foreach ($aAttCodes as $sAttCode)
//		{
//			$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $sAttCode);
//			$aColumnProperties[$sAttCode] = array(
//				'title' => $oAttDef->GetLabel()
//			);
//		}
		// - Retrieving objects
		$aItems = array();
		while ($oItem = $oSet->Fetch())
		{
			$aItemProperties = array(
				'id' => $oItem->GetKey(),
				'name' => $oItem->GetName(),
				'attributes' => array()
			);

//			foreach ($aAttCodes as $sAttCode)
//			{
//				if ($sAttCode !== 'id')
//				{
//					$aAttProperties = array(
//						'att_code' => $sAttCode
//					);
//
//					$oAttDef = MetaModel::GetAttributeDef($sTargetObjectClass, $sAttCode);
//					if ($oAttDef->IsExternalKey())
//					{
//						$aAttProperties['value'] = $oItem->Get($sAttCode . '_friendlyname');
//						// Checking if we can view the object
//						if ((SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $oAttDef->GetTargetClass(), $oItem->Get($sAttCode))))
//						{
//							$aAttProperties['url'] = $oApp['url_generator']->generate('p_object_view', array('sObjectClass' => $oAttDef->GetTargetClass(), 'sObjectId' => $oItem->GetKey()));
//						}
//					}
//					else
//					{
//						$aAttProperties['value'] = $oAttDef->GetValueLabel($oItem->Get($sAttCode));
//					}
//
//					$aItemProperties['attributes'][$sAttCode] = $aAttProperties;
//				}
//			}

			$aItems[] = $aItemProperties;
		}

		// Preparing response
		if ($bInitalPass)
		{
			$aData = $aData + array(
				'form' => array(
					'id' => 'object_search_form_' . time(),
					'title' => Dict::Format('Brick:Portal:Object:Search:Hierarchy:Title', $oTargetAttDef->GetLabel(), MetaModel::GetName($sTargetObjectClass))
				),
				'aResults' => array(
					'aItems' => json_encode($aItems),
					'iCount' => count($aItems)
				),
				'aSource' => array(
					'sFormPath' => $sFormPath,
					'sFieldId' => $sFieldId
				)
			);

			if ($oRequest->isXmlHttpRequest())
			{
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/modal.html.twig', $aData);
			}
			else
			{
				//$oResponse = $oApp->abort(404, Dict::S('UI:ObjectDoesNotExist'));
				$oResponse = $oApp['twig']->render('itop-portal-base/portal/src/views/bricks/object/layout.html.twig', $aData);
			}
		}
		else
		{
			$aData = $aData + array(
				'levelsProperties' => $aColumnProperties,
				'data' => $aItems
			);

			$oResponse = $oApp->json($aData);
		}

		return $oResponse;
	}

	/**
	 * Handles attachment add/remove on an object
	 *
	 * Note : This is inspired from itop-attachment/ajax.attachment.php
	 * 
	 * @param Request $oRequest
	 * @param Application $oApp
	 */
	public function AttachmentAction(Request $oRequest, Application $oApp, $sOperation = null)
	{
		$aData = array(
			'att_id' => 0,
			'preview' => false,
			'msg' => ''
		);

		// Retrieving sOperation from request only if it wasn't forced (determined by the route)
		if ($sOperation === null)
		{
			$sOperation = $oRequest->get('operation');
		}
		switch ($sOperation)
		{
			case 'add':
				$sFieldName = $oRequest->get('field_name');
				$sObjectClass = $oRequest->get('object_class');
				$sTempId = $oRequest->get('temp_id');

				if (($sObjectClass === null) || ($sTempId === null))
				{
					$aData['error'] = Dict::Format('UI:Error:2ParametersMissing', 'object_class', 'temp_id');
				}
				else
				{
					try
					{
						$oDocument = utils::ReadPostedDocument($sFieldName);
						$oAttachment = MetaModel::NewObject('Attachment');
						$oAttachment->Set('expire', time() + 3600); // one hour...
						$oAttachment->Set('temp_id', $sTempId);
						$oAttachment->Set('item_class', $sObjectClass);
						$oAttachment->SetDefaultOrgId();
						$oAttachment->Set('contents', $oDocument);
						$iAttId = $oAttachment->DBInsert();

						$aData['msg'] = htmlentities($oDocument->GetFileName(), ENT_QUOTES, 'UTF-8');
						// TODO : Change icon location when itop-attachment is refactored
						//$aData['icon'] = utils::GetAbsoluteUrlAppRoot() . AttachmentPlugIn::GetFileIcon($oDoc->GetFileName());
						$aData['icon'] = utils::GetAbsoluteUrlAppRoot() . 'env-' . utils::GetCurrentEnvironment() . '/itop-attachments/icons/image.png';
						$aData['att_id'] = $iAttId;
						$aData['preview'] = $oDocument->IsPreviewAvailable() ? 'true' : 'false';
					}
					catch (FileUploadException $e)
					{
						$aData['error'] = $e->GetMessage();
					}
				}

				$oResponse = $oApp->json($aData);
				break;

			case 'download':
				$sAttachmentId = $oRequest->get('sAttachmentId');
				$sAttachmentUrl = utils::GetAbsoluteUrlAppRoot() . ATTACHMENT_DOWNLOAD_URL . $sAttachmentId;

				$oResponse = new RedirectResponse($sAttachmentUrl);
				break;

			default:
				$oApp->abort(403);
				break;
		}

		return $oResponse;
	}

	/**
	 * Returns a json response containing an array of objects informations.
	 *
	 * The service must be given 3 parameters :
	 * - sObjectClass : The class of objects to retrieve information from
	 * - aObjectIds : An array of object ids
	 * - aObjectAttCodes : An array of attribute codes to retrieve
	 *
	 * @param Request $oRequest
	 * @param Application $oApp
	 * @return Response
	 */
	public function GetInformationsAsJsonAction(Request $oRequest, Application $oApp)
	{
		$aData = array();

		// Retrieving parameters
		$sObjectClass = $oRequest->Get('sObjectClass');
		$aObjectIds = $oRequest->Get('aObjectIds');
		$aObjectAttCodes = $oRequest->Get('aObjectAttCodes');
		if ($sObjectClass === null || $aObjectIds === null || $aObjectAttCodes === null)
		{
			$oApp->abort(500, 'Invalid request data, some informations are missing');
		}

		// Checking that id is in the AttCodes
		if (!in_array('id', $aObjectAttCodes))
		{
			$aObjectAttCodes = array_merge(array('id'), $aObjectAttCodes);
		}

		// Retrieving attributes definitions
		$aAttDefs = array();
		foreach ($aObjectAttCodes as $sObjectAttCode)
		{
			if ($sObjectAttCode === 'id')
				continue;

			$aAttDefs[$sObjectAttCode] = MetaModel::GetAttributeDef($sObjectClass, $sObjectAttCode);
		}
		
		// Building the search
		$oSearch = DBObjectSearch::FromOQL("SELECT " . $sObjectClass . " WHERE id IN ('" . implode("','", $aObjectIds) . "')");
		$oSet = new DBObjectSet($oSearch);
		$oSet->OptimizeColumnLoad($aObjectAttCodes);

		// Retrieving objects
		while ($oObject = $oSet->Fetch())
		{
			$aObjectData = array(
				'id' => $oObject->GetKey(),
				'attributes' => array()
			);

			foreach ($aAttDefs as $oAttDef)
			{
				$aAttData = array(
					'att_code' => $oAttDef->GetCode()
				);

				if ($oAttDef->IsExternalKey())
				{
					$aAttData['value'] = $oObject->Get($oAttDef->GetCode() . '_friendlyname');
					if (SecurityHelper::IsActionAllowed($oApp, UR_ACTION_READ, $oAttDef->GetTargetClass()))
					{
						$aAttData['url'] = $oApp['url_generator']->generate('p_object_view', array('sObjectClass' => $oAttDef->GetTargetClass(), 'sObjectId' => $oObject->Get($oAttDef->GetCode())));
					}
				}
				elseif ($oAttDef->IsLinkSet())
				{
					// We skip it
					continue;
				}
				else
				{
					$aAttData['value'] = $oAttDef->GetValueLabel($oObject->Get($oAttDef->GetCode()));
				}

				$aObjectData['attributes'][$oAttDef->GetCode()] = $aAttData;
			}

			$aData['items'][] = $aObjectData;
		}

		return $oApp->json($aData);
	}

}

?>