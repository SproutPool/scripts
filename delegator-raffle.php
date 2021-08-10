<?php

// defaults
$tickets_default = 1000;

// get raw delegator data from CSV file
$raw_delegator_data = import_delegator_data("delegator_list.csv", 1000, $tickets_default);

// adding bonus tickets based on stake amount
$final_delegator_data = add_stake_amount_bonus_tickets($raw_delegator_data);

// distribution TEST
// echo json_encode(distribution_test($final_delegator_data, 100000)); exit;

// pick winner
echo "WINNER \n";
echo json_encode(pick_winner($final_delegator_data));
echo "\n"; exit;


///////////////
// FUNCTIONS //
///////////////

function import_delegator_data ($csv_file_name, $max_file_rows, $probability_default) {
    $delegators = [];
    
    if (($handle = fopen($csv_file_name, "r")) !== FALSE) {
        while (($data_row = fgetcsv($handle, $max_file_rows, ",")) !== FALSE) {
            $num = count($data_row);
            $delegator = [];
            foreach ($data_row as $data_key => $data_column) {
                switch($data_key){
                    case 0:
                        $delegator['id'] = $data_column;
                        break;
                    case 1:
                        $delegator['address'] = $data_column;
                        break;
                    case 2:
                        $delegator['stake'] = $data_column;
                        $delegator['tickets'] = $probability_default;
                        if ($data_column != '') {
                            $delegators[] = $delegator;
                        }
                        break;
                }
            }
        }
        fclose($handle);
    }
    return $delegators;
}

function add_stake_amount_bonus_tickets ($delegators) {
    foreach ($delegators as &$delegator) {
        $bonus_tickets = $delegator['stake']/1000;
        $delegator['tickets'] += $bonus_tickets > 100 ? 100 : round($bonus_tickets);
    }
    return $delegators;
}

function get_max_delegator_tickets ($delegators) {
    $total_tickets = 0;
    foreach ($delegators as $delegator) {
        $total_tickets += $delegator['tickets'];
    }
    return $total_tickets;
}

function pick_winner ($delegators) {
    $random_number = rand(1,get_max_delegator_tickets($delegators));

    $current_ticket_num = 0;
    foreach ($delegators as $delegator) {
        $ticket_max = $current_ticket_num+$delegator['tickets'];
        if ($random_number <= $ticket_max) {
            return $delegator;
        } else {
            $current_ticket_num = $ticket_max;
        }
    }
}

function distribution_test ($delegators, $iterations) {
    $results = [];
    for ($i = 1; $i <= $iterations; $i++) {
        $winner = pick_winner($delegators);
        $results[$winner['id']]++;
    }
    return $results;
}

?>