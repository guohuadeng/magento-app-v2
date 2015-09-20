<?php
class Sunpop_CustomerSurvey_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {		
		$this->loadLayout(); 
		
		$this->renderLayout();
    }
	
	public function viewAction()
    {		
		$this->loadLayout(); 
		
		$this->renderLayout();
    }
	
	public function completeAction()
    {		
		$this->loadLayout(); 
		
		$this->renderLayout();
    }
	
	public function saveAction() {
		//first get the survey id that we are looking at.
		$surveyID = $this->getRequest()->getParam('survey_number');
		
		if($surveyID) {	
			//get each question that is in this survey
			$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $surveyID);

			foreach($questions as $question) {
				$result = $this->getRequest()->getParam('question' . $question->question_id);
				
				if($result != '') {
					$NewResult = Mage::getModel('customersurvey/results');

					$NewResult->customersurvey_id = $surveyID;
					$NewResult->question_id = $question->question_id;
					$NewResult->answer = (string)$result;

					$NewResult->save();
				}
			}		 
		}

		$this->_redirect('*/*/complete/', array('id' => $surveyID));
	}
}