<?php
// Copyright (C) 2010 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/**
 * Implementation of iTop SOAP services
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

require_once(APPROOT.'/webservices/webservices.class.inc.php');


class BasicServices extends WebServicesBase
{
	static protected function GetWSDLFilePath()
	{
		return APPROOT.'/webservices/itop.wsdl.tpl';
	}

	/**
	 * Get the server version (TODO: get it dynamically, where ?)
	 *	 
	 * @return WebServiceResult
	 */
	static public function GetVersion()
	{
		if (ITOP_REVISION == '$WCREV$')
		{
			$sVersionString = ITOP_VERSION.' [dev]';
		}
		else
		{
			// This is a build made from SVN, let display the full information
			$sVersionString = ITOP_VERSION."-".ITOP_REVISION." ".ITOP_BUILD_DATE;
		}

		return $sVersionString;
	}

	public function CreateIncidentTicket($sLogin, $sPassword, $sTitle, $sDescription, $oCallerDesc, $oCustomerDesc, $oServiceDesc, $oServiceSubcategoryDesc, $sProduct, $oWorkgroupDesc, $aSOAPImpactedCIs, $sImpact, $sUrgency)
	{
		if (!UserRights::CheckCredentials($sLogin, $sPassword))
		{
			$oRes = new WebServiceResultFailedLogin($sLogin);
			$this->LogUsage(__FUNCTION__, $oRes);

			return $oRes->ToSoapStructure();
		}
		UserRights::Login($sLogin);

		$aCallerDesc = self::SoapStructToExternalKeySearch($oCallerDesc);
		$aCustomerDesc = self::SoapStructToExternalKeySearch($oCustomerDesc);
		$aServiceDesc = self::SoapStructToExternalKeySearch($oServiceDesc);
		$aServiceSubcategoryDesc = self::SoapStructToExternalKeySearch($oServiceSubcategoryDesc);
		$aWorkgroupDesc = self::SoapStructToExternalKeySearch($oWorkgroupDesc);

		$aImpactedCIs = array();
		if (is_null($aSOAPImpactedCIs)) $aSOAPImpactedCIs = array();
		foreach($aSOAPImpactedCIs as $oImpactedCIs)
		{
			$aImpactedCIs[] = self::SoapStructToLinkCreationSpec($oImpactedCIs);
		}

		$oRes = $this->_CreateIncidentTicket
		(
			$sTitle,
			$sDescription,
			$aCallerDesc,
			$aCustomerDesc,
			$aServiceDesc,
			$aServiceSubcategoryDesc,
			$sProduct,
			$aWorkgroupDesc,
			$aImpactedCIs,
			$sImpact,
			$sUrgency
		);
		return $oRes->ToSoapStructure();
	}

	/**
	 * Create an incident ticket from a monitoring system
	 * Some CIs might be specified (by their name/IP)
	 *	 
	 * @param string sTitle
	 * @param string sDescription
	 * @param array aCallerDesc
	 * @param array aCustomerDesc
	 * @param array aServiceDesc
	 * @param array aServiceSubcategoryDesc
	 * @param string sProduct
	 * @param array aWorkgroupDesc
	 * @param array aImpactedCIs
	 * @param string sImpact
	 * @param string sUrgency
	 *
	 * @return WebServiceResult
	 */
	protected function _CreateIncidentTicket($sTitle, $sDescription, $aCallerDesc, $aCustomerDesc, $aServiceDesc, $aServiceSubcategoryDesc, $sProduct, $aWorkgroupDesc, $aImpactedCIs, $sImpact, $sUrgency)
	{

		$oRes = new WebServiceResult();

		try
		{
			$oMyChange = MetaModel::NewObject("CMDBChange");
			$oMyChange->Set("date", time());
			$oMyChange->Set("userinfo", "Administrator");
			$iChangeId = $oMyChange->DBInsertNoReload();
	
			$oNewTicket = MetaModel::NewObject('Incident');
			$this->MyObjectSetScalar('title', 'title', $sTitle, $oNewTicket, $oRes);
			$this->MyObjectSetScalar('description', 'description', $sDescription, $oNewTicket, $oRes);

			$this->MyObjectSetExternalKey('org_id', 'customer', $aCustomerDesc, $oNewTicket, $oRes);
			$this->MyObjectSetExternalKey('caller_id', 'caller', $aCallerDesc, $oNewTicket, $oRes);
	
			$this->MyObjectSetExternalKey('service_id', 'service', $aServiceDesc, $oNewTicket, $oRes);
			$this->MyObjectSetExternalKey('servicesubcategory_id', 'servicesubcategory', $aServiceSubcategoryDesc, $oNewTicket, $oRes);
			$this->MyObjectSetScalar('product', 'product', $sProduct, $oNewTicket, $oRes);

			$this->MyObjectSetExternalKey('workgroup_id', 'workgroup', $aWorkgroupDesc, $oNewTicket, $oRes);


			$aDevicesNotFound = $this->AddLinkedObjects('ci_list', 'impacted_cis', 'FunctionalCI', $aImpactedCIs, $oNewTicket, $oRes);
			if (count($aDevicesNotFound) > 0)
			{
				$this->MyObjectSetScalar('description', 'n/a', $sDescription.' - Related CIs: '.implode(', ', $aDevicesNotFound), $oNewTicket, $oRes);
			}
			else
			{
				$this->MyObjectSetScalar('description', 'n/a', $sDescription, $oNewTicket, $oRes);
			}

			$this->MyObjectSetScalar('impact', 'impact', $sImpact, $oNewTicket, $oRes);
			$this->MyObjectSetScalar('urgency', 'urgency', $sUrgency, $oNewTicket, $oRes);

			$this->MyObjectInsert($oNewTicket, 'created', $oMyChange, $oRes);
		}
		catch (CoreException $e)
		{
			$oRes->LogError($e->getMessage());
		}
		catch (Exception $e)
		{
			$oRes->LogError($e->getMessage());
		}

		$this->LogUsage(__FUNCTION__, $oRes);
		return $oRes;
	}

	/**
	 * Given an OQL, returns a set of objects (several objects could be on the same row)
	 *	 
	 * @param string sOQL
	 */	 
	public function SearchObjects($sLogin, $sPassword, $sOQL)
	{
		if (!UserRights::CheckCredentials($sLogin, $sPassword))
		{
			$oRes = new WebServiceResultFailedLogin($sLogin);
			$this->LogUsage(__FUNCTION__, $oRes);

			return $oRes->ToSoapStructure();
		}
		UserRights::Login($sLogin);

		$oRes = $this->_SearchObjects($sOQL);
		return $oRes->ToSoapStructure();
	}

	protected function _SearchObjects($sOQL)
	{
		$oRes = new WebServiceResult();
		try
		{
			$oSearch = DBObjectSearch::FromOQL($sOQL);
			$oSet = new DBObjectSet($oSearch);
			$aData = $oSet->ToArrayOfValues();
			foreach($aData as $iRow => $aRow)
			{
				$oRes->AddResultRow("row_$iRow", $aRow);
			}
		}
		catch (CoreException $e)
		{
			$oRes->LogError($e->getMessage());
		}
		catch (Exception $e)
		{
			$oRes->LogError($e->getMessage());
		}

		$this->LogUsage(__FUNCTION__, $oRes);
		return $oRes;
	}
}
?>
