<?php

require_once __DIR__ . '/../classes/Clan.php';

function clan() {
	global $system;
	global $player;
	global $self_link;

	if(!$player->clan) {
		return false;
	}

	$page = 'HQ';
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	}

	// Check start mission
	if(!empty($_GET['start_mission'])) {
		$mission_id = $_GET['start_mission'];
		try {
            if($player->clan->startMission($player, $mission_id)) {
                require("missions.php");
                runActiveMission();
                return true;
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}		
	}

    // Challenge stuff
	if($player->rank_num >= 3 && $page == 'challenge') {
		if($_GET['challenge']) {
			$challenge_position = $_GET['challenge'];

			try {
                $challenge_succeeded = $player->clan->challengeForOffice($player, $challenge_position);
                if($challenge_succeeded) {
                    $page = 'controls';
                }
			} catch (Exception $e) {
				$system->message($e->getMessage());
				$page = 'HQ';
			}
		}
	}

	// Office controls
	if($player->clan_office && $page == 'controls') {
		if(!empty($_POST['resign'])) {
            $office = $player->clan_office;
			if(!empty($_POST['confirm_resign'])) {
                $player->clan->resignOffice($player);

				$page = 'HQ';
			}
			else {
				echo "<table class='table'>
					<tr><th>Resign Office</th></tr>
					<tr><td style='text-align:center;'>
						Are you sure you want to resign as Clan {$player->clan->name} " . Clan::$office_labels[$player->clan_office] . "?
						<br />
						<form action='$self_link&page=controls' method='post'>
							<input type='hidden' name='resign' value='1' />
							<input type='hidden' name='confirm_resign' value='1' />
							<button type='submit'>Resign</button>
						</form>
					</td></tr>
				</table>";
			}
		}
		else if(!empty($_POST['motto'])) {
			$motto = $system->clean($_POST['motto']);
			try {
				$player->clan->setMotto($motto);
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		else if(!empty($_POST['logo'])) {
			$logo_url = $system->clean($_POST['logo']);
			try {
                $player->clan->setLogoUrl($logo_url);
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		else if(!empty($_POST['boost'])) {
			$new_boost = $system->clean($_POST['boost']);
			try {
                $player->clan->setBoost($new_boost);
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		else if(!empty($_POST['info'])) {
			$info = $system->clean($_POST['info']);
			try {
                $player->clan->setInfo($info);
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
	}

    // Load officers
    $officers = $player->clan->fetchOfficers();

    // Load members
    $members = [];
    if($page == 'members') {
        // Pagination
        $users_per_page = 10;
        $min = 0;
        $prev = 0;
        $next = 0;
        if(isset($_GET['min'])) {
            $min = (int)$_GET['min'];
        }

        if($min > 0) {
            $prev = $min - $users_per_page;
            if($prev < 0) {
                $prev = 0;
            }
        }
        $result = $system->query("SELECT COUNT(`user_id`) as `count` FROM `users` WHERE `clan_id`='{$player->clan->id}'");
        $total_members = $system->db_fetch($result)['count'];

        if($min + $users_per_page < $total_members) {
            $next = $min + $users_per_page;
        }

        $result = $system->query(
            "SELECT `user_id`, `user_name`, `rank`, `level`, `exp`, `last_active` FROM `users` WHERE `clan_id`='{$player->clan->id}' 
                    ORDER BY `rank` DESC, `exp` DESC LIMIT $min, $users_per_page"
        );

        while($row = $system->db_fetch($result)) {
            $members[$row['user_id']] = new ClanMemberDto(
                id: $row['user_id'],
                name: $row['user_name'],
                rank_num: $row['rank'],
                level: $row['level'],
                exp: $row['exp'],
                last_active: $row['last_active']
            );
        }
    }

    $clan = $player->clan;
    $RANK_NAMES = RankManager::fetchNames($system);
    $max_mission_rank = Mission::maxMissionRank($player->rank_num);
    $missions = $player->clan->getClanMissions($player->rank_num);

    $active_mission = $player->mission_id ? new Mission($player->mission_id, $player) : null;

    $can_challenge = [
        Clan::OFFICE_LEADER => $player->rank_num >= 4 && $player->clan_office != Clan::OFFICE_LEADER,
        Clan::OFFICE_ELDER_1 => $player->rank_num >= 3 && $player->clan_office != Clan::OFFICE_ELDER_1,
        Clan::OFFICE_ELDER_2 => $player->rank_num >= 3 && $player->clan_office != Clan::OFFICE_ELDER_2,
    ];

    $min_leader_last_active = time() - Clan::LEADER_MAX_INACTIVITY;
    $min_elder_last_active = time() - Clan::ELDER_MAX_INACTIVITY;

    $can_claim = [
        Clan::OFFICE_LEADER => $can_challenge[Clan::OFFICE_LEADER] && (
            !isset($officers[Clan::OFFICE_LEADER]) || $officers[Clan::OFFICE_LEADER]->last_active < $min_leader_last_active
        ),
        Clan::OFFICE_ELDER_1 => $can_challenge[Clan::OFFICE_ELDER_1] && (
            !isset($officers[Clan::OFFICE_ELDER_1]) || $officers[Clan::OFFICE_ELDER_1]->last_active < $min_elder_last_active
        ),
        Clan::OFFICE_ELDER_2 => $can_challenge[Clan::OFFICE_ELDER_2] && (
            !isset($officers[Clan::OFFICE_ELDER_2]) || $officers[Clan::OFFICE_ELDER_2]->last_active < $min_elder_last_active
        ),
    ];

    $system->printMessage();

    require 'templates/clan.php';
}