<?php

class Sunpop_CustomerSurvey_ViewController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
    	// "Fetch" display
        $this->loadLayout();
		
		//create button
		
		$this->_addContent(
			$this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
			'label'     => Mage::helper('catalog')->__('Add Survey'),
			'onclick'   => "setLocation('".$this->getUrl('customersurvey/*/edit')."')",
			'class'   => 'add',
			'align' => 'right'
			))
		);
	  
	  //create grid
        $this->_addContent($this->getLayout()->createBlock('customersurvey/grid'));

        $this->renderLayout();
    }	
	
		
	public function editAction()
	{
		$customersurveyId     = $this->getRequest()->getParam('id');
		$customersurveyModel  = Mage::getModel('customersurvey/survey')->load($customersurveyId);		
		
		if ($customersurveyModel->getId() || $customersurveyId == 0) {
			Mage::register('customersurvey_data', $customersurveyModel);
			$this->loadLayout();
			$this->_setActiveMenu('customersurvey/surveys');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('customersurvey/adminhtml_survey_edit'))
			->_addLeft($this->getLayout()->createBlock('customersurvey/adminhtml_survey_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customersurvey')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function resultsAction()
	{
		$customersurveyId     = $this->getRequest()->getParam('id');
		$customersurveyModel  = Mage::getModel('customersurvey/survey')->load($customersurveyId);		
		
		if ($customersurveyModel->getId() || $customersurveyId == 0) {
			Mage::register('customersurvey_data', $customersurveyModel);
			$this->loadLayout();
			$this->_setActiveMenu('customersurvey/surveys');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('customersurvey/adminhtml_survey_results'))
			->_addLeft($this->getLayout()->createBlock('customersurvey/adminhtml_survey_results_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customersurvey')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function saveAction() {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		//get all of the parameters
		$title = $this->getRequest()->getParam('Title');
		$status = $this->getRequest()->getParam('status');
		$code = $this->getRequest()->getParam('code');
		$code_title = $this->getRequest()->getParam('code_title');
		
		$questionList = $this->getRequest()->getParam('questionList');

		//sort the posted numbers of questions into an array
		$questionList = substr($questionList,0,-1);
		$questionList = substr($questionList, 1, strlen($questionList) - 1); //remove leading space
		$questionList = explode(", ", $questionList);
		
		//check to see if we have an exisiting id
		if(!$customersurveyId) {
			//if we don't, then create a new survey
			$newSurvey  = Mage::getModel('customersurvey/survey');
			$newSurvey->title = $title;
			$newSurvey->enabled = $status;
			$newSurvey->code = $code;
			$newSurvey->code_title = $code_title;
			$newSurvey->save();
			
			//set the survey id to the newly created survey's id
			$customersurveyId = $newSurvey->getId();
		}
		else {
			//if we do have an idea, then load the existing survey
			$newSurvey  = Mage::getModel('customersurvey/survey')->load($customersurveyId);
			$newSurvey->title = $title;
			$newSurvey->enabled = $status;
			$newSurvey->code = $code;
			$newSurvey->code_title = $code_title;
			$newSurvey->save();
		}
		
		//delete all questions that have to do with this ID
		$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId);
		
		$keepList = array();
		//generate a list of questions to keep
		foreach ($questionList as $questionNumber) {
			$questionOldID = $this->getRequest()->getParam('question_oldid_' . $questionNumber);
			
			if($questionOldID) {
				$keepList[] = $questionOldID;
			}
			
		}
		
		foreach($questions as $question) {
			//only delete questions that are not in the array
			if(!in_array($question->question_id, $keepList)) {
				$question->delete();
			}
		}
			
		foreach ($questionList as $questionNumber) {			
			//get the POSTed question information
			$questionTitle = $this->getRequest()->getParam('question_title_' . $questionNumber);
			$questionType = $this->getRequest()->getParam('question_type_' . $questionNumber);
			$questionSortOrder = $this->getRequest()->getParam('question_sortorder_' . $questionNumber);
			$questionOldID = $this->getRequest()->getParam('question_oldid_' . $questionNumber);
			$questionDescription = $this->getRequest()->getParam('question_description_' . $questionNumber);
			
			$totalAnswers = $this->getRequest()->getParam('question_answers_total_ids_' . $questionNumber);
			$answersString = '';
			
			for($i = 1; $i <= $totalAnswers; $i++) {
				$nextAnswer = $this->getRequest()->getParam('question_answer_' . $questionNumber . '_' . $i);
				

				if($nextAnswer) {
					if($answersString == '') {
						$answersString = $nextAnswer;
					}
					else
					{
						$answersString .= "|||" . $nextAnswer;
					}
					
				}
			}
			
			if(!$questionOldID) {
				//if we don't have an old ID, then we are creating a new question
				$newQuestion = Mage::getModel('customersurvey/questions');
	
				$newQuestion->customersurvey_id = $customersurveyId;
				$newQuestion->question = $questionTitle;
				$newQuestion->answer_type = $questionType;
				$newQuestion->sort_order = $questionSortOrder;
				$newQuestion->possible_answers = $answersString;
					
				$newQuestion->save();
			}
			else {
				//if we have an old ID, then we are updating an old question
				$oldQuestion = Mage::getModel('customersurvey/questions')->load($questionOldID);
				
				$oldQuestion->question = $questionTitle;
				$oldQuestion->answer_type = $questionType;
				$oldQuestion->sort_order = $questionSortOrder;
				$oldQuestion->possible_answers = $answersString;
				
				$oldQuestion->save();
			}

		}
		
		$this->_redirect('*/*/');
	}
	
	function deleteAction() {
		//delete the survey
		$customersurveyId = $this->getRequest()->getParam('id');
		$customersurveyModel  = Mage::getModel('customersurvey/survey')->load($customersurveyId);	
		
		$customersurveyModel->delete();
		
		//delete all questions from this survey
		$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId);
		
		foreach($questions as $question) {
			$question->delete();
		}
		
		$this->_redirect('*/*/');
	}

}