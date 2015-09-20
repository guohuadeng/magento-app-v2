<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Results_Tab_Graphical extends Mage_Adminhtml_Block_Widget
{
	var $myResponses;
	
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customersurvey/graphical.phtml');
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
			$results  = Mage::getModel('customersurvey/results')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId);
		}
		else
		{
			$results = array();	
		}
	
		$this->myResponses = $results;

		return $results;
	}
	
	public function groupSimiliarResponsesReturnPercents($id) {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		$resultArray = array();
		
		$answerArray = Mage::getModel('customersurvey/results')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId)->addFieldToFilter('question_id', str_replace(" ", "", $id));

		$runningCount = 0;
	
		//sort them out into different keys for different results
		foreach($answerArray as $answers) {
			 if(array_key_exists($answers->answer, $resultArray)) {
				 $resultArray[$answers->answer] = intval($resultArray[$answers->answer]) + 1;
			 }
			 else {
				  $resultArray[$answers->answer] = 1;
			 }
			 
			 $runningCount++;
		}
		
		//make the keys percentages
		foreach(array_keys($resultArray) as $key) {
			$resultArray[$key] = (intval($resultArray[$key]) / $runningCount) * 100;
		}
		
		return $resultArray;
	}
		
	public function groupSimiliarResponses($id) {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		$resultArray = array();
		
		$answerArray = Mage::getModel('customersurvey/results')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId)->addFieldToFilter('question_id', str_replace(" ", "", $id));

		$runningCount = 0;
	
		//sort them out into different keys for different results
		foreach($answerArray as $answers) {
			 if(array_key_exists($answers->answer, $resultArray)) {
				 $resultArray[$answers->answer] = intval($resultArray[$answers->answer]) + 1;
			 }
			 else {
				  $resultArray[$answers->answer] = 1;
			 }
			 
			 $runningCount++;
		}

		return $resultArray;
	}

}