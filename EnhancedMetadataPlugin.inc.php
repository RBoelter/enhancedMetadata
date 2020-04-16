<?php
import('lib.pkp.classes.plugins.GenericPlugin');


class EnhancedMetadataPlugin extends GenericPlugin
{

	/* unused at the moment */
	var $json = [
		"name" => "enhTest",
		"type" => "text",
		"title" => ["de_DE" => "test_de", "en_EN" => "test_en"],
		"description" => ["de_DE" => "test_desc_de", "en_EN" => "test_desc_en"],
	];

	/**
	 * @return string plugin name
	 */
	public function getDisplayName()
	{
		return __('plugins.generic.enhancedMetadata.title');
	}

	/**
	 * @return string plugin description
	 */
	public function getDescription()
	{
		return __('plugins.generic.enhancedMetadata.desc');
	}


	public function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			// Add metadata fields to submission
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFormDisplay'));
			HookRegistry::register('supplementaryfilemetadataform::display', array($this, 'metadataFormDisplay'));
			// Hook for initData
			HookRegistry::register('submissionsubmitstep3form::initdata', array($this, 'submissionMetadataInitData'));
			HookRegistry::register('issueentrysubmissionreviewform::initdata', array($this, 'submissionMetadataInitData'));
			HookRegistry::register('quicksubmitform::initdata', array($this, 'submissionMetadataInitData'));
			HookRegistry::register('supplementaryfilemetadataform::initdata', array($this, 'submissionMetadataInitData'));
			// Hook for readUserVars
			HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('issueentrysubmissionreviewform::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('quicksubmitform::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('supplementaryfilemetadataform::readuservars', array($this, 'addUserVars'));
			// Hook for save into forms
			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('issueentrysubmissionreviewform::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('quicksubmitform::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('supplementaryfilemetadataform::execute', array($this, 'submissionMetadataExecute'));

			// Hook for save into db
			HookRegistry::register('articledao::getLocaleFieldNames', array($this, 'addLocaleFieldNames'));
			HookRegistry::register('supplementaryfiledaodelegate::getLocaleFieldNames', array($this, 'addLocaleFieldNames'));
		}
		return $success;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function metadataFormDisplay($hookName, $params)
	{
		$form =& $params[0];
		if (!is_array($form) && get_class($form) == 'SupplementaryFileMetadataForm') {
			$request = PKPApplication::getRequest();
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->registerFilter("output", array($this, 'supplementaryMetadataFilter'));
		} else {
			$smarty =& $params[1];
			$output =& $params[2];
			$output .= $smarty->fetch($this->getTemplateResource('submissionMetaData.tpl'));
		}
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function submissionMetadataInitData($hookName, $params)
	{
		$form =& $params[0];
		$article = null;
		error_log("#################################### submissionMetadataInitData " . get_class($form));
		if (get_class($form) == 'SubmissionSubmitStep3Form') {
			$article = $form->submission;
		} elseif (get_class($form) == 'IssueEntrySubmissionReviewForm') {
			$article = $form->getSubmission();
		} elseif (get_class($form) == 'QuickSubmitForm') {
			$article = $form->submission;
		} else if (get_class($form) == 'SupplementaryFileMetadataForm') {
			$form->setData('enhTest2', '11');
		} else if (get_class($form) == 'SubmissionFilesMetadataForm') {
			$form->setData('enhTest2', '12');
		}
		if ($article)
			$form->setData('enhTest', $article->getData('enhTest'));
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function addUserVars($hookName, $params)
	{
		$form =& $params[0];
		$userVars =& $params[1];
		error_log("#################################### addUserVars " . get_class($form));
		if (get_class($form) == 'SubmissionSubmitStep3Form' ||
			get_class($form) == 'IssueEntrySubmissionReviewForm' ||
			get_class($form) == 'QuickSubmitForm') {
			$userVars[] = 'enhTest';
		} else if (get_class($form) == 'SupplementaryFileMetadataForm') {
			error_log("#################################### addUserVars " . get_class($form));
			$userVars[] = 'enhTest2';
		}
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function submissionMetadataExecute($hookName, $params)
	{
		$form =& $params[0];
		$article = null;
		$submissionFile = null;
		error_log("#################################### submissionMetadataExecute " . get_class($form));
		switch (get_class($form)) {
			case 'SubmissionSubmitStep3Form':
			case 'QuickSubmitForm':
				$article = $form->submission;
				break;
			case 'IssueEntrySubmissionReviewForm':
				$article = $form->getSubmission();
				break;
			case 'SubmissionFilesMetadataForm':
				$submissionFile = $form->getSubmissionFile();

				break;
		}
		if ($article != null)
			$article->setData('enhTest', $form->getData('enhTest'));
		if ($submissionFile != null)
			$submissionFile->setData('enhTest2', $form->getData('enhTest2'));
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function addLocaleFieldNames($hookName, $params)
	{
		$form =& $params[0];
		error_log("#################################### addLocaleFieldNames " . get_class($form));
		if (get_class($form) == 'ArticleDAO') {
			$fields =& $params[1];
			$fields[] = 'enhTest';
		}
		return false;
	}

	function supplementaryMetadataFilter($output, $templateMgr)
	{
		if (preg_match('/<fieldset\s*id="\s*fileMetaData\s*"\s*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			error_log("--- supplementaryMetadataFilter --- " . $offset . " - " . $match);
			$newOutput = substr($output, 0, $offset + strlen($match));
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('supplementaryMetaData.tpl'));
			$newOutput .= substr($output, $offset + strlen($match));
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'supplementaryMetadataFilter'));
		}
		return $output;
	}


}
