<?php

namespace App\Controllers;

class TechtreeController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$parse = array();
		
		$Element = $this->request->getQuery('id', null, 0);

		if ($Element > 0 && isset($this->game->resource[$Element]))
		{
			$this->view->pick('techtree_element');
			$this->view->setVar('element', $Element);

			if (isset($requeriments[$Element]))
				$this->view->setVar('req', $requeriments[$Element]);

			$this->config->view->offsetSet('overviewListView', 0);
		}
		else
		{
			foreach (_getText('tech') as $Element => $ElementName)
			{
				if ($Element >= 300 && $Element < 400)
					continue;
		
				if ($Element < 600)
				{
					$pars = array();
					$pars['tt_name'] = $ElementName;
		
					if (!isset($this->game->resource[$Element]))
					{
						$parse[] = $pars;
					}
					else
					{
						if (isset($requeriments[$Element]))
						{
							$pars['required_list'] = "";
		
							foreach ($requeriments[$Element] as $ResClass => $Level)
							{
								if ($ResClass != 700)
								{
									if (isset($this->user->{$this->game->resource[$ResClass]}) && $this->user->{$this->game->resource[$ResClass]} >= $Level)
									{
										$pars['required_list'] .= "<span class=\"positive\">";
									}
									elseif (isset($this->planet->{$this->game->resource[$ResClass]}) && $this->planet->{$this->game->resource[$ResClass]} >= $Level)
									{
										$pars['required_list'] .= "<span class=\"positive\">";
									}
									else
									{
										$pars['required_list'] .= "<span class=\"negative\">";
									}
									$pars['required_list'] .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";
		
									if (isset($this->user->{$this->game->resource[$ResClass]}) && $this->user->{$this->game->resource[$ResClass]} < $Level)
									{
										$minus = $Level - $this->user->{$this->game->resource[$ResClass]};
										$pars['required_list'] .= " + <b>" . $minus . "</b>";
									}
									elseif (isset($this->planet->{$this->game->resource[$ResClass]}) && $this->planet->{$this->game->resource[$ResClass]} < $Level)
									{
										$minus = $Level - $this->planet->{$this->game->resource[$ResClass]};
										$pars['required_list'] .= " + <b>" . $minus . "</b>";
									}
								}
								else
								{
									$pars['required_list'] .= _getText('tech', $ResClass) . " (";
		
									if ($this->user->race != $Level)
										$pars['required_list'] .= "<span class=\"negative\">" . _getText('race', $Level);
									else
										$pars['required_list'] .= "<span class=\"positive\">" . _getText('race', $Level);
								}
		
								$pars['required_list'] .= ")</span><br>";
							}
						}
						else
							$pars['required_list'] = "";
		
						$pars['tt_info'] = $Element;
						$parse[] = $pars;
					}
				}
			}
		
			$this->view->pick('techtree');
		}
		
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('Tech'));
		$this->showTopPanel(false);
	}
}

?>