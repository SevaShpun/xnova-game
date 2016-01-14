<?php

use App\Lang;
use App\Missions\Mission;
use App\UpdateStatistics;

class UpdateTask extends ApplicationTask
{
	public function onlineAction ()
	{
		$online = $this->db->fetchColumn("SELECT COUNT(*) as `online` FROM game_users WHERE `onlinetime` > '" . (time() - $this->config->game->onlinetime * 60) . "'");

		$this->game->updateConfig('users_online', $online);

		echo $online." users online\n";
	}

	public function statAction ()
	{
		$start = microtime(true);

		$statUpdate = new UpdateStatistics();

		$statUpdate->inactiveUsers();
		$statUpdate->deleteUsers();
		$statUpdate->clearOldStats();
		$statUpdate->update();
		$statUpdate->addToLog();
		$statUpdate->clearGame();
		$statUpdate->buildRecordsCache();

		$end = microtime(true);

		echo "stats updated in ".($end - $start)." sec\n";
	}

	public function fleetAction ()
	{
		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 3)
				die('Server too busy. Please try again later.');
		}

		define('MAX_RUNS', 12);
		define('TIME_LIMIT', 60);

		$missionObjPattern = array
		(
			1	=> 'MissionCaseAttack',
			2   => 'MissionCaseACS',
			3   => 'MissionCaseTransport',
			4   => 'MissionCaseStay',
			5   => 'MissionCaseStayAlly',
			6   => 'MissionCaseSpy',
			7   => 'MissionCaseColonisation',
			8   => 'MissionCaseRecycling',
			9   => 'MissionCaseDestruction',
			10  => 'MissionCaseCreateBase',
			15  => 'MissionCaseExpedition',
			20  => 'MissionCaseRak'
		);

		Lang::includeLang("fleet_engine");

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS)
		{
			if (function_exists('sys_getloadavg'))
			{
				$load = sys_getloadavg();

				if ($load[0] > 3)
					die('Server too busy. Please try again later.');
			}

			$_fleets = array_merge
			(
				$this->db->extractResult($this->db->query("SELECT * FROM game_fleets WHERE (`fleet_start_time` <= '" . time() . "' AND `fleet_mess` = '0') LIMIT 3")),
				$this->db->extractResult($this->db->query("SELECT * FROM game_fleets WHERE (`fleet_end_stay` <= '" . time() . "' AND `fleet_mess` != '1' AND `fleet_end_stay` != '0') LIMIT 3")),
				$this->db->extractResult($this->db->query("SELECT * FROM game_fleets WHERE (`fleet_end_time` < '" . time() . "' AND `fleet_mess` != '0') LIMIT 3"))
			);

			uasort($_fleets, function($a, $b)
			{
				return ($a['fleet_time'] <= $b['fleet_time'] ? -1 : 1);
			});

			if (count($_fleets) > 0)
			{
				foreach ($_fleets AS $fleetRow)
				{
					if (!isset($missionObjPattern[$fleetRow['fleet_mission']]))
					{
						$this->db->delete('game_fleets', 'fleet_id = ?', [$fleetRow['fleet_id']]);

						continue;
					}

					$missionName = $missionObjPattern[$fleetRow['fleet_mission']];

					$missionName = 'App\Missions\\'.$missionName;

					/**
					 * @var $mission Mission
					 */
					$mission = new $missionName($fleetRow);

					if ($fleetRow['fleet_mess'] == 0 && $fleetRow['fleet_start_time'] <= time())
					{
						$mission->TargetEvent();
					}

					if ($fleetRow['fleet_mess'] == 3 && $fleetRow['fleet_end_stay'] <= time())
					{
						$mission->EndStayEvent();
					}

					if ($fleetRow['fleet_mess'] == 1 && $fleetRow['fleet_end_time'] <= time())
					{
						$mission->ReturnEvent();
					}

					unset($mission);
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all fleet updated\n";
	}
}

?>