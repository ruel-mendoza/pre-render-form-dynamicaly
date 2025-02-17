<?php
//executed before the form is displayed
//We fill select dropdown and radio button with dynamic date_add
add_filter( 'gform_pre_render', 'populate_select' );

function populate_select( $form ) {

    if ( empty( $form['id'] ) ) {
        return $form;
    }

	$arrdata = [
	    'tab'   => display_current_user_team(),
	];

	$form['title'] = display_current_user_display_name() . ' Task Tracker';

	// Send the data to Google Spreadsheet via HTTP POST request 
	// Task List Script version 3
	$post_url = "https://script.google.com/macros/s/AKfycbwVlrdzAO9Z--u7LD0kkA_LITVQBDyAY3a4mmV4-ilTGBlPYhpEL55n7RnTeOGK9qhJcQ/exec";
	
	$request = new WP_Http();
	$response = $request->request($post_url, array('method' => 'GET','timeout' => 50, 'sslverify' => false, 'body' => $arrdata));

	if ($has_return_value && (bool) $response === false || is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] > 400) {
	    return;
	}
	
    $userobj = wp_get_current_user();
    $userid = isset( $userobj->ID ) ? (int) $userobj->ID : 0;

    $useropts = get_field('mp_user_options','option',true);
    $getuser  = array_values(array_filter($useropts, function($user) use ($userid) { return $user['mp_user']['ID'] == $userid; }));

	$json = json_decode($response['body']);    

	$fields_to_hide = array('Advance','Priority','Partners','Platform','Category','Complexity','Page Count','Region');
	foreach( $json as $task ) {
		if(isset($task->priority)){
			unset( $fields_to_hide[array_search( 'Priority', $fields_to_hide )] );
		}
		if(isset($task->partners)){
			unset( $fields_to_hide[array_search( 'Partners', $fields_to_hide )] );
		}
		if(isset($task->platform)){
			unset( $fields_to_hide[array_search( 'Platform', $fields_to_hide )] );
		}
		if(isset($task->category)){
			unset( $fields_to_hide[array_search( 'Category', $fields_to_hide )] );
		}
		if(isset($task->complexity)){
			unset( $fields_to_hide[array_search( 'Complexity', $fields_to_hide )] );
		}
		if(isset($task->pageCount)){
			unset( $fields_to_hide[array_search( 'Page Count', $fields_to_hide )] );
		}
		if(isset($task->region)){
			unset( $fields_to_hide[array_search( 'Region', $fields_to_hide )] );
		}		

	}	

	foreach($form['fields'] as $key=>$field) {
	    if(in_array($field['label'], $fields_to_hide)) {
	        unset($form['fields'][$key]);
	    }
	}

    foreach( $form['fields'] as &$field )  {
 
        //NOTE: replace 3 with your checkbox field id
        
        if ( $field->id == 3 ) {
        	$choicestask[] = array('text' =>'Select Task Type','value' => '');
	        foreach( $json as $task ) {
	            $choicestask[] = array( 'text' => $task->task, 'value' => $task->task );
	        }
	        $field->choices = $choicestask;
        }

        if ( $field->id == 12 ) {
        		$counter12 = 0; 
		        foreach( $json as $task ) {
		        	if(!empty($task->priority) || $task->priority <> ""){
		        		//$counter12 += 1;
		            	$choicespriority[] = array( 'text' => $task->priority, 'value' => $task->priority );
		        	}
		        }
				$field->choices = $choicespriority;		        
        }        
    	
        if ( $field->id == 24 ) {
        		$counter15 = 0; 
	        	$choicespartners[] = array('text' =>'Select Partner','value' => '');
		        foreach( $json as $task ) {
		        	if(!empty($task->partners) || $task->partners <> ""){
		        		$counter15 += 1;
		            	$choicespartners[] = array( 'text' => $task->partners, 'value' => $task->partners );
		        	}
		        }
				$field->choices = $choicespartners;		        
        }

        if ( $field->id == 16 ) {
        	//$choicespriority[] = array('text' =>'','value' => 'Select Complexity Level');
        	$counter16 = 0;
		        foreach( $json as $task ) {
		        	if(!empty($task->platform) || $task->platform <> ""){
		            	$choicesplatform[] = array( 'text' => $task->platform, 'value' => $task->platform );
		        	}
		        }
				$field->choices = $choicesplatform;		        
        }




        if ( $field->id == 17 ) {
        	//$choicespriority[] = array('text' =>'','value' => 'Select Complexity Level');
		        foreach( $json as $task ) {
		        	if(!empty($task->category) || $task->category <> ""){
		            	$choicespartners[] = array( 'text' => $task->category, 'value' => $task->category );
		        	}
		        }
				$field->choices = $choicespartners;		        
        } 
        if ( $field->id == 18 ) {
        	//$choicespriority[] = array('text' =>'','value' => 'Select Complexity Level');
		        foreach( $json as $task ) {
		        	if(!empty($task->complexity) || $task->complexity <> ""){
		            	$choicescomplexity[] = array( 'text' => $task->complexity, 'value' => $task->complexity );
		        	}
		        }
				$field->choices = $choicescomplexity;		        
        } 

        if ( $field->id == 22 ) {
        	//$choicespriority[] = array('text' =>'','value' => 'Select Complexity Level');
		        foreach( $json as $task ) {
		        	if(!empty($task->pageCount) || $task->pageCount <> ""){
		            	$choicespageCount[] = array( 'text' => $task->pageCount, 'value' => $task->pageCount );
		        	}
		        }
				$field->choices = $choicespageCount;		        
        }        

        if ( $field->id == 26 ) {
        	//$choicespriority[] = array('text' =>'','value' => 'Select Complexity Level');
		        foreach( $json as $task ) {
		        	if(!empty($task->region) || $task->region <> ""){
		            	$choicesregion[] = array( 'text' => $task->region, 'value' => $task->region );
		        	}
		        }
				$field->choices = $choicesregion;		        
        }

		if ( $field->id == 10 ) {
			$field->defaultValue = display_current_user_team();
		}

		if ( $field->id == 8 ) {
		    $field->conditionalLogic =
		        array(
		            'actionType' => 'hide',
		            'logicType' => 'all',
		            'rules' =>
		                array( array( 'fieldId' => 10, 'operator' => 'is', 'value' => display_current_user_team() ) )
		        );
		}

 
//        $field->inputs = $inputs;
 
    }

    return $form;
}
