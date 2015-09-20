<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Results_Tab_General extends Mage_Adminhtml_Block_Widget
{

	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customersurvey/general_results.phtml');
    }
	
	public function isReadOnly() {
		return false;	
	}
	
	public function getSurvey() {
		$customersurveyId = $this->getRequest()->getParam('id');

		$survey  = Mage::getModel('customersurvey/survey')->load($customersurveyId);

		return $survey;	
	}
	
	public function getSurveyID() {
		$customersurveyId = $this->getRequest()->getParam('id');

		return $customersurveyId;	
	}
	
	public function getMyQuestions() {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		if($customersurveyId) {
			$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId);
			$questions = $questions->addAttributeToSort('sort_order', 'ASC');
		}
		else
		{
			$questions = array();	
		}

		return $questions;
	}
	
	public function getMyResponses() {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		if($customersurveyId) {
			//get the list of responses
			$groupedResponses = Mage::getModel('customersurvey/results')->getCollection();

			$groupedResponses->getSelect()->where("main_table.customersurvey_id = " . $customersurveyId)->group('main_table.input_time')->limit(6, (($this->myPage() - 1) * 6));
			
			//now for each response in the list, get the group of answers
			
			$responsesArray = array();
			
			foreach($groupedResponses as $groupedResponse) {
				$responses = Mage::getModel('customersurvey/results')->getCollection();

				$responses->getSelect()->where("main_table.customersurvey_id = " . $customersurveyId . " and main_table.input_time = '" . $groupedResponse->input_time . "'");

				$innerResponses = array();

				foreach($responses as $response) {
					$innerResponses[$response->question_id] = $response->answer;
				}
				
				$responsesArray[] = $innerResponses;
			}
		}
		else
		{
			$responsesArray = array();	
		}

	/*
		return format
	
		Array
			(
				[0] => Array
					(
						[125] => Neutral
						[126] => Yes
						[127] => Dissatisfied
					)
			
				[1] => Array
					(
						[127] => Satisfied
						[126] => Yes
						[125] => Very satisfied
					)
			
			)
	*/
	
		return $responsesArray;
	}

	public function myID() {
		return $this->getRequest()->getParam('id');	
	}
	
	public function myPage() {
		$currentPage = intval($this->getRequest()->getParam('page'));
		
		if($currentPage == '') {
			$currentPage = 1;	
		}

		return $currentPage;
	}
	
	public function totalResults() {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		$groupedResponses = Mage::getModel('customersurvey/results')->getCollection();

		$groupedResponses->getSelect()->where("main_table.customersurvey_id = " . $customersurveyId)->group('main_table.input_time');
		
		$i = 0;
		
		foreach($groupedResponses as $response) {
			$i++;
		}

		return $i;
	}
	
	
}