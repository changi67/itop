<?php

// Copyright (C) 2010-2016 Combodo SARL
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

namespace Combodo\iTop\Renderer;

use \Combodo\iTop\Form\Field\Field;

/**
 * Description of FieldRenderer
 *
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
abstract class FieldRenderer
{
	protected $oField;
	protected $sEndpoint;

	/**
	 * Default constructor
	 *
	 * @param \Combodo\iTop\Form\Field\Field $oField
	 */
	public function __construct(Field $oField)
	{
		$this->oField = $oField;
	}

	/**
	 *
	 * @return string
	 */
	public function GetEndpoint()
	{
		return $this->sEndpoint;
	}

	/**
	 *
	 * @param string $sEndpoint
	 */
	public function SetEndpoint($sEndpoint)
	{
		$this->sEndpoint = $sEndpoint;
	}

	/**
	 * Renders a Field as a RenderingOutput
	 *
	 * @return \Combodo\iTop\Renderer\RenderingOutput
	 */
	abstract public function Render();
}
