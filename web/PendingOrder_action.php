<?php

//visitor_action.php
include_once('./include/debug.php');
include_once('sms.php');

$shipment_system = new sms();

if(isset($_POST["action"]))
{
	//#1
	if($_POST["action"] == 'fetch')
	{
		//console_log("action : fetch");
		$order_column = array('
		visitor_table.visitor_name', 
		'visitor_table.visitor_meet_person_name', 
		'visitor_table.visitor_department', 
		'visitor_table.visitor_enter_time', 
		'visitor_table.visitor_out_time', 
		'visitor_table.visitor_status'
		);

		$output = array();

		$main_query = "
		SELECT * FROM tgs_shipment ";
		/*INNER JOIN admin_table 
		ON admin_table.admin_id = visitor_table.visitor_enter_by 
		";*/

		/*if(!$shipment_system->is_master_user())
		{
			$main_query .= "
			WHERE visitor_table.visitor_enter_by = '".$_SESSION["admin_id"]."' 
			";

			if($_POST["from_date"] != '')
			{
				$search_query = "
				AND DATE(visitor_table.visitor_enter_time) BETWEEN '".$_POST["from_date"]."' AND  '".$_POST["to_date"]."' AND ( 
				";
			}
			else
			{
				$search_query = " AND ( ";	
			}
			
		}
		else
		{
			if($_POST["from_date"] != '')
			{
				$search_query = "WHERE DATE(visitor_table.visitor_enter_time) BETWEEN '".$_POST["from_date"]."' AND  '".$_POST["to_date"]."' AND ( ";
			}
			else
			{
				$search_query = "WHERE ";	
			}
		}
		

		if(isset($_POST["search"]["value"]))
		{
			
			$search_query .= 'visitor_table.visitor_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR visitor_table.visitor_meet_person_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR visitor_table.visitor_department LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR visitor_table.visitor_enter_time LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR visitor_table.visitor_out_time LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR visitor_table.visitor_status LIKE "%'.$_POST["search"]["value"].'%" ';
			
			if($shipment_system->is_master_user())
			{
				$search_query .= 'OR admin_table.admin_name LIKE "%'.$_POST["search"]["value"].'%" ';
				if($_POST["from_date"] != '')
				{
					$search_query .= ') ';
				}
			}
			else
			{
				$search_query .= ') ';
			}
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY visitor_table.visitor_id DESC ';
		}*/

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$shipment_system->query = $main_query;// . $search_query . $order_query;

		$shipment_system->execute();

		$filtered_rows = $shipment_system->row_count();

		//$shipment_system->query .= $limit_query;

		$result = $shipment_system->get_result();

		//$shipment_system->query = $main_query;

		//$shipment_system->execute();

		//$total_rows = $shipment_system->row_count();

		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();
			//$sub_array[] = html_entity_decode($row["visitor_name"]);
			//$sub_array[] = html_entity_decode($row["visitor_meet_person_name"]);
			/*$sub_array[] = $row["visitor_department"];
			$sub_array[] = $row["visitor_enter_time"];
			$sub_array[] = $row["visitor_out_time"];
			$status = '';
			if($row["visitor_status"] == 'In')
			{
				$status = '<span class="badge badge-success">In Premises</span>';
			}
			else
			{
				$status = '<span class="badge badge-danger">Leave</span>';
			}
			$sub_array[] = $status;
			if($shipment_system->is_master_user())
			{
				$sub_array[] = $row["admin_name"];
			}*/
			$sub_array[] = '
			<div align="center">
			<button type="button" name="view_button" class="btn btn-primary btn-sm view_button" data-id=""><i class="fas fa-eye"></i></button>
			&nbsp;
			<button type="button" name="edit_button" class="btn btn-warning btn-sm edit_button" data-id=""><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-sm delete_button" data-id=""><i class="fas fa-times"></i></button>
			</div>
			';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			//"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
			
		echo json_encode($output);

	}
	
	if($_POST["action"] == 'fetch_shipment')
	{
		//console_log("action : fetch");
		$order_column = array(
		'tgs_shipment.SHIPMENT_NUM', 
		'tgs_shipment.CUS_ID_SENDER', 
		'tgs_shipment.CUS_ID_RECEIVER', 
		'tgs_shipment.SHIPMENT_DESCRIPTION', 
		'tgs_shipment.SHIPMENT_ESTIMATED_COST', 
		'tgs_shipment.SHIPMENT_ACTUAL_COST', 
		'tgs_shipment.SHIPMENT_SOURCE', 
		'tgs_shipment.SHIPMENT_DESTINATION', 
		'tgs_shipment.SHIPMENT_ORDER_DAY', 
		'tgs_shipment.SHIPMENT_CONFIRMATION_PRIORITY', 
		'tgs_shipment.SHIPMENT_STATUS', 
		'tgs_shipment.SHIPMENT_START_DATE', 
		'tgs_shipment.SHIPMENT_END_DATE',
		'tgs_customer.cus_fname'
		);

		$output = array();

		$main_query = "
		SELECT *, t1.cus_fname cus1, t2.cus_fname  cus2
		FROM tgs_shipment
		JOIN tgs_customer t1 ON t1.cus_id = tgs_shipment.CUS_ID_SENDER 
		JOIN tgs_customer t2 ON t2.cus_id = tgs_shipment.CUS_ID_RECEIVER 
		WHERE tgs_shipment.SHIPMENT_STATUS = 'Pending' 
		";

		if($_POST["from_date"] != '')
		{
			$search_query = " AND DATE(tgs_shipment.SHIPMENT_ORDER_DAY) BETWEEN '".$_POST["from_date"]."' AND  '".$_POST["to_date"]."' AND ( ";
		}
		else
		{
			$search_query = "AND ";	
		}
		
		

		if(isset($_POST["search"]["value"]))
		{
			
			$search_query .= ' tgs_shipment.SHIPMENT_SOURCE LIKE "%'.$_POST["search"]["value"].'%" ';
			//$search_query .= 'OR tgs_shipment.visitor_meet_person_name LIKE "%'.$_POST["search"]["value"].'%" ';
			//$search_query .= 'OR tgs_shipment.visitor_department LIKE "%'.$_POST["search"]["value"].'%" ';
			//$search_query .= 'OR tgs_shipment.visitor_enter_time LIKE "%'.$_POST["search"]["value"].'%" ';
			//$search_query .= 'OR tgs_shipment.visitor_out_time LIKE "%'.$_POST["search"]["value"].'%" ';
			//$search_query .= 'OR tgs_shipment.visitor_status LIKE "%'.$_POST["search"]["value"].'%" ';
			if($shipment_system->is_master_user())
			{
				//$search_query .= ' tgs_shipment.SHIPMENT_SOURCE LIKE "%'.$_POST["search"]["value"].'%" ';
				if($_POST["from_date"] != '')
				{
					$search_query .= ') ';
				}
			}
			else
			{
				$search_query .= ') ';
			}
			
		}



		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY tgs_shipment.SHIPMENT_NUM ASC ';
		}

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$shipment_system->query = $main_query . $search_query . $order_query;

		$shipment_system->execute();

		$filtered_rows = $shipment_system->row_count();

		$shipment_system->query .= $limit_query;

		$result = $shipment_system->get_result();

		$shipment_system->query = $main_query;

		$shipment_system->execute();

		$total_rows = $shipment_system->row_count();

		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();
			$sub_array[] = $row["SHIPMENT_NUM"];
			$sub_array[] = html_entity_decode($row["cus1"]);
			$sub_array[] = html_entity_decode($row["cus2"]);
			//$sub_array[] = html_entity_decode($row["SHIPMENT_DESCRIPTION"]);
			$sub_array[] = $row["SHIPMENT_ESTIMATED_COST"];
			$sub_array[] = $row["SHIPMENT_ACTUAL_COST"];
			$sub_array[] = html_entity_decode($row["SHIPMENT_SOURCE"]);
			$sub_array[] = html_entity_decode($row["SHIPMENT_DESTINATION"]);
			$sub_array[] = $row["SHIPMENT_ORDER_DAY"];
			$sub_array[] = $row["SHIPMENT_CONFIRMATION_PRIORITY"];
			$status = '';
			if($row["SHIPMENT_STATUS"] == 'Pending')
			{
				$status = '<span class="badge badge-success">Pending</span>';
			}
			else
			{
				$status = '<span class="badge badge-danger">Done</span>';
			}
			$sub_array[] = $status;
			
			$sub_array[] = $row["SHIPMENT_START_DATE"];
			$sub_array[] = $row["SHIPMENT_END_DATE"];

			$sub_array[] = '
			<div align="center" class="btn-group">
			<button type="button" name="view_button" class="btn btn-primary btn-xs view_button" data-id="'.$row["SHIPMENT_NUM"].'"><i class="fas fa-eye"></i></button>
			&nbsp;
			<button type="button" name="edit_button" class="btn btn-warning btn-xs edit_button" data-id="'.$row["SHIPMENT_NUM"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-xs delete_button" data-id="'.$row["SHIPMENT_NUM"].'"><i class="fas fa-times"></i></button>
			</div>
			';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
			
		echo json_encode($output);

	}
	
	//#2
	if($_POST["action"] == 'Add')
	{
		//console_log("action : Add");
		$data = array(
			':visitor_name'			=>	$shipment_system->clean_input($_POST["visitor_name"]),
			':visitor_email'		=>	$_POST["visitor_email"],
			':visitor_mobile_no'	=>	$_POST["visitor_mobile_no"],
			':visitor_address'		=>	$shipment_system->clean_input($_POST["visitor_address"]),
			':visitor_meet_person_name' =>	$_POST["visitor_meet_person_name"],
			':visitor_department'	=>	$_POST["visitor_department"],
			':visitor_reason_to_meet' =>	$shipment_system->clean_input($_POST["visitor_reason_to_meet"]),
			':visitor_enter_time'	=>	$shipment_system->get_datetime(),
			':visitor_outing_remark'=>	'',
			':visitor_out_time'		=>	'',
			':visitor_status'		=>	'In',
			':visitor_enter_by'		=>	$_SESSION["admin_id"]
		);

		$shipment_system->query = "
		INSERT INTO visitor_table 
		(visitor_name, visitor_email, visitor_mobile_no, visitor_address, visitor_meet_person_name, visitor_department, visitor_reason_to_meet, visitor_enter_time, visitor_outing_remark, visitor_out_time, visitor_status, visitor_enter_by) 
		VALUES (:visitor_name, :visitor_email, :visitor_mobile_no, :visitor_address, :visitor_meet_person_name, :visitor_department, :visitor_reason_to_meet, :visitor_enter_time, :visitor_outing_remark, :visitor_out_time, :visitor_status, :visitor_enter_by)
			";

		$shipment_system->execute($data);

		echo '<div class="alert alert-success">Department Added</div>';
	}

	//#3
	if($_POST["action"] == 'fetch_single')
	{
		//console_log("action : fetch_single");
		/* $shipment_system->query = "
		SELECT * FROM tgs_shipment 
		WHERE visitor_id = '".$_POST["shipment_number"]."'
		"; */
		
		$shipment_system->query = "
		SELECT *, t1.*
		FROM tgs_shipment
		JOIN tgs_customer t1 ON t1.cus_id = tgs_shipment.CUS_ID_SENDER 
		JOIN tgs_customer t2 ON t2.cus_id = tgs_shipment.CUS_ID_RECEIVER
		WHERE SHIPMENT_NUM = '".$_POST["shipment_number"]."'
		";
		
		$result = $shipment_system->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['shipment_sender_name'] = $row['cus_fname'];
			$data['shipment_sender_email'] = $row['cus_email'];
			$data['shipment_sender_mobile_no'] = $row['cus_phone'];
			$data['shipment_sender_address_detail'] = $row['SHIPMENT_SOURCE'];
			
			$data['shipment_receiver_name'] = $row['cus_fname'];
			$data['shipment_receiver_email'] = $row['cus_email'];
			$data['shipment_receiver_mobile_no'] = $row['cus_phone'];
			$data['shipment_receiver_address'] = $row['SHIPMENT_DESTINATION'];
			
			$data['shipment_pakage_type_detail'] = $row['SHIPMENT_PACKAGE_TYPE'];
			//$data['visitor_department'] = $row['visitor_department'];
			//$data['visitor_reason_to_meet'] = $row['visitor_reason_to_meet'];
			$data['shipment_pakage_weight'] = $row['SHIPMENT_PACKAGE_WEIGHT'];
			$data['shipment_pakage_lenght'] = $row['SHIPMENT_PACKAGE_LENGTH'];
			$data['shipment_pakage_width'] = $row['SHIPMENT_PACKAGE_WIDTH'];
			$data['shipment_pakage_height'] = $row['SHIPMENT_PACKAGE_HEIGHT'];
			$data['shipment_pakage_quantity'] = $row['SHIPMENT_PACKAGE_QUANTITY'];
			$data['shipment_pakage_day_of_dispatch'] = $row['SHIPMENT_ORDER_DAY'];
			$data['shipment_pakage_day_of_arrival'] = $row['SHIPMENT_END_DATE'];
			$data['shipment_pakage_priority'] = $row['SHIPMENT_CONFIRMATION_PRIORITY'];

		}

		echo json_encode($data);
	}

	//#4
	if($_POST["action"] == 'Edit')
	{
		//console_log("action : Edit");
		$data = array(
			':visitor_name'			=>	$shipment_system->clean_input($_POST["visitor_name"]),
			':visitor_email'		=>	$_POST["visitor_email"],
			':visitor_mobile_no'	=>	$_POST["visitor_mobile_no"],
			':visitor_address'		=>	$shipment_system->clean_input($_POST["visitor_address"]),
			':visitor_meet_person_name' =>	$_POST["visitor_meet_person_name"],
			':visitor_department'	=>	$_POST["visitor_department"],
			':visitor_reason_to_meet' =>	$shipment_system->clean_input($_POST["visitor_reason_to_meet"]),
		);

		$shipment_system->query = "
		UPDATE visitor_table 
		SET visitor_name = :visitor_name, 
		visitor_email = :visitor_email, 
		visitor_mobile_no = :visitor_mobile_no, 
		visitor_address = :visitor_address, 
		visitor_meet_person_name = :visitor_meet_person_name, 
		visitor_department = :visitor_department, 
		visitor_reason_to_meet = :visitor_reason_to_meet 
		WHERE visitor_id = '".$_POST['hidden_id']."'
		";

		$shipment_system->execute($data);

		echo '<div class="alert alert-success">Visitor Details Updated</div>';
	}

	//#5
	if($_POST["action"] == 'delete')
	{
		//console_log("action : delete");
		$shipment_system->query = "
		DELETE FROM tgs_shipment 
		WHERE SHIPMENT_NUM = '".$_POST["id"]."'
		";

		$shipment_system->execute();

		echo '<div class="alert alert-success">Shipment Deleted</div>';
	}

	//#6
	if($_POST["action"] == 'update_shipment_status')
	{
		//console_log("action : update_outing_detail");
		$data = array(
			':SHIPMENT_STATUS'			=>	'Confirmed'
		);

		$shipment_system->query = "
		UPDATE tgs_shipment 
		SET SHIPMENT_STATUS = :SHIPMENT_STATUS 
		WHERE SHIPMENT_NUM = '".$_POST["hidden_shipment_number"]."'
		";

		$shipment_system->execute($data);

		echo '<div class="alert alert-success">Details Updated</div>';
	}
}

?>