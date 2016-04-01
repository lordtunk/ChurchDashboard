<?php
    session_start();
    include("func.php");
    include("follow_ups.php");
    $params = isset($_POST['params']) ? json_decode($_POST['params']) : null;
    $email = $_POST['email'];
    $f = new Func();
    $followUps = new FollowUps();
    $dict = array();

    function isDate($txtDate, $allowBlank) {
        if($txtDate == "")
            return $allowBlank;
        $dt = date_parse($txtDate);
        if($dt === FALSE) return FALSE;
            return checkdate($dt['month'], $dt['day'], $dt['year']);
    }

    if($params != NULL && (!isDate($params->fromDate, true) || !isDate($params->toDate, true))) {
        $dict['success'] = FALSE;
        $f->logMessage('Invalid report parameters');
    } else {
        if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            $dict['success'] = FALSE;
            $dict['error'] = 1;
            $f->logMessage('Session information missing');
        } else {
            $session_id = $_SESSION['session_id'];
            $user_id = $_SESSION['user_id'];


            try {
                $dict['success'] = $f->isLoggedIn($user_id, $session_id);
            } catch (Exception $e) {
                $dict['success'] = FALSE;
                $f->logMessage($e->getMessage());
            }
            if(!$dict['success'])
                $dict['error'] = 1;
        }
    }
    if($dict['success'] == TRUE) {
        try {
			$body = "";
			if($params->fromDate != "" && $params->toDate != "") {
				$body .= "Summary includes communication cards received between {$params->fromDate} and {$params->toDate}";
			} else if($params->fromDate != "") {
				$body .= "Summary includes communication cards received after {$params->fromDate}";
			} else if($params->toDate != "") {
				$body .= "Summary includes communication cards received before {$params->toDate}";
			}
			$people = array();
			$signedUpForBaptism = "";
			$interestedInGkidz = "";
			$interestedInNext= "";
			$interestedInGgroups = "";
			$interestedInGteams = "";
			$interestedInJoining = "";
			$wouldLikeVisit = "";
			$noAgent = "";
			$commitmentChrist = "";
			$recommitmentChrist = "";
			$commitmentTithe = "";
			$commitmentMinistry = "";
			$firstTimeVisitors = "";
			
            $results = $followUps->getFollowUpReport($params, true);
			$borderWidth = "1px";
			$borderColor = "black";
			$style = "border: $borderWidth solid $borderColor";
			$body .= "<table style=\"$style\"><thead><th style=\"$style\">Name</th><th style=\"$style\">Phone</th><th style=\"$style\"></th></thead><tbody>";
            foreach($results as $key => $row) {
				$person = $row;
				$name = getDisplayName($person);
				$phone = $person['primary_phone'];
				$display = "$name $phone";
				$body .= "<tr><td style=\"$style\">$name</td><td style=\"$style\">$phone</td><td style=\"$style\"><ul>";
                if($params->signed_up_for_baptism && $row['commitment_baptism'] === 'true') {
					$signedUpForBaptism .= getListItem($display);
					$body .= getListItem("Signed up for baptism");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->signed_up_for_baptism && $row['info_gkids']) {
					$interestedInGkidz .= getListItem($display);
					$body .= getListItem("Interested in gKidz");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->interested_in_next && $row['info_next']) {
					$interestedInNext .= getListItem($display);
					$body .= getListItem("Interested in Next");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->interested_in_ggroups && $row['info_ggroups']) {
					$interestedInGgroups .= getListItem($display);
					$body .= getListItem("Interested in gGroups");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->interested_in_gteams && $row['info_gteams']) {
					$interestedInGteams .= getListItem($display);
					$body .= getListItem("Interested in gTeams");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->interested_in_joining && $row['info_member']) {
					$interestedInJoining .= getListItem($display);
					$body .= getListItem("Interested in joining GC");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->would_like_visit && $row['info_visit']) {
					$wouldLikeVisit .= getListItem($display);
					$body .= getListItem("Would like a visit from a GC Pastor");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				// if($params->no_agent && $row['assigned_agent'] == 0) {
					// $noAgent .= getListItem($display);
					// $body .= getListItem("Not assigned an agent");
					// if(in_array($person['id'], $people) == FALSE) {
						// array_push($people, $person['id']);
					// }
				// }
				if($params->signed_up_for_baptism && $row['commitment_christ']) {
					$commitmentChrist .= getListItem($display);
					$body .= getListItem("Committing life to Christ");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->recommitment_christ && $row['recommitment_christ']) {
					$recommitmentChrist .= getListItem($display);
					$body .= getListItem("Recommitting life to Christ");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->commitment_tithe && $row['commitment_tithe']) {
					$commitmentTithe .= getListItem($display);
					$body .= getListItem("Committing to tithe");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->commitment_ministry && $row['commitment_ministry']) {
					$commitmentMinistry .= getListItem($display);
					$body .= getListItem("Committing to serving in ministry");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				if($params->attendance_frequency && $row['attendance_frequency'] == 1) {
					$firstTimeVisitors .= getListItem($display);
					$body .= getListItem("First time visitor");
					if(in_array($person['id'], $people) == FALSE) {
						array_push($people, $person['id']);
					}
				}
				$body .= "</ul></td></tr>";
            }
			$body .= "</tbody></table>";
			
			if($firstTimeVisitors !== "") {
				$body .= getGroupText("First time visitor", $firstTimeVisitors);
			}
			if($wouldLikeVisit !== "") {
				$body .= getGroupText("Would like a visit from a GC Pastor", $wouldLikeVisit);
			}
			if($commitmentChrist !== "") {
				$body .= getGroupText("Committing life to Christ", $commitmentChrist);
			}
			if($interestedInNext !== "") {
				$body .= getGroupText("Interested in Next", $interestedInNext);
			}
			if($signedUpForBaptism !== "") {
				$body .= getGroupText("Signed up for baptism", $signedUpForBaptism);
			}
			if($interestedInJoining !== "") {
				$body .= getGroupText("Interested in joining GC", $interestedInJoining);
			}
			if($interestedInGkidz !== "") {
				$body .= getGroupText("Interested in gKidz", $interestedInGkidz);
			}
			if($interestedInGgroups !== "") {
				$body .= getGroupText("Interested in gGroups", $interestedInGgroups);
			}
			if($interestedInGteams !== "") {
				$body .= getGroupText("Interested in gTeams", $interestedInGteams);
			}
			if($commitmentTithe !== "") {
				$body .= getGroupText("Committing to tithe", $commitmentTithe);
			}
			if($commitmentMinistry !== "") {
				$body .= getGroupText("Committing to serving in ministry", $commitmentMinistry);
			}
			if($noAgent !== "") {
				$body .= getGroupText("Not assigned an agent", $noAgent);
			}
			if($recommitmentChrist !== "") {
				$body .= getGroupText("Recommitting life to Christ", $recommitmentChrist);
			}
			if(count($people) != count($results)) {
				$f->logMessage("Count people: ".count($people)." Count results: ".count($results));
				$body .= "<h3>Others</h3><ul>";
				foreach($results as $key => $row) {
					if(in_array($row['id'], $people) == FALSE) {
						$body .= getListItem(getPersonDisplay($row));
					}
				}
				$body .= "</ul>";
			}
			$f->sendEmail($email, "Follow Up Summary", $body);
            $dict['success'] = true;
        } catch (Exception $e) {
          $dict['success'] = false;
          $f->logMessage($e);
        }
    }

    echo json_encode($dict);
	
	function getDisplayName($person, $prefix="") {
		if($person === null) return '';

		if($person[$prefix.'last_name'] && $person[$prefix.'first_name']) {
		  return $person[$prefix.'first_name'] . " " . $person[$prefix.'last_name'];
		} else if($person[$prefix.'first_name']) {
		  return $person[$prefix.'first_name'];
		} else if($person[$prefix.'last_name']) {
		  return $person[$prefix.'last_name'];
		}
		return $person[$prefix.'description'];
	}
	
	function getPersonDisplay($person) {
		return getDisplayName($person)." ".$person['primary_phone'];
	}
	
	function getListItem($text) {
		return "<li>$text</li>";
	}
	
	function getGroupText($header, $text) {
		return "<p><h3>$header</h3><ul>$text</ul></p>";
	}
	
	function getSummaryOptions($params) {
		$summary = "";
		if($params->fromDate != "" && $params->toDate != "") {
			$summary .= getListItem("Communication cards received between {$params->fromDate} and {$params->toDate}");
		} else if($params->fromDate != "") {
			$summary .= getListItem("Communication cards received after {$params->fromDate}");
		} else if($params->toDate != "") {
			$summary .= getListItem("Communication cards received before {$params->toDate}");
		}
		if($params->signed_up_for_baptism) {
			$summary .= getListItem("Signed up for baptism");
		}
		if($params->interested_in_gkids) {
			$summary .= getListItem("Interested in gKidz");
		}
		if($params->interested_in_next) {
			$summary .= getListItem("Interested in Next");
		}
		if($params->interested_in_ggroups) {
			$summary .= getListItem("Interested in gGroups");
		}
		if($params->interested_in_gteams) {
			$summary .= getListItem("Interested in gTeams");
		}
		if($params->interested_in_joining) {
			$summary .= getListItem("Interested in joining GC");
		}
		if($params->would_like_visit) {
			$summary .= getListItem("Would like a visit from a GC Pastor");
		}
		if($params->no_agent) {
			$summary .= getListItem("Not assigned an agent");
		}
		if($params->commitment_christ) {
			$summary .= getListItem("Committing life to Christ");
		}
		if($params->recommitment_christ) {
			$summary .= getListItem("Recommitting life to Christ");
		}
		if($params->commitment_tithe) {
			$summary .= getListItem("Committing to tithe");
		}
		if($params->commitment_ministry) {
			$summary .= getListItem("Committing to serving in ministry");
		}
		
		return "Summary includes the following:<ul>$summary</ul>";
	}
?>
