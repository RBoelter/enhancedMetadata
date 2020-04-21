<?php
import('lib.pkp.classes.plugins.GenericPlugin');

/**
 * Class EnhancedMetadataPlugin
 *
 * To make this work in 3.1.2.x add parent::execute(); in execute function(L137) of \lib\pkp\controllers\wizard\fileUpload\form\SubmissionFilesMetadataForm.inc.php
 */
class EnhancedMetadataPlugin extends GenericPlugin
{

	/* unused at the moment */
	var $json = [
		[
			"type" => "radio",
			"title" =>
				[
					"de_DE" => "radio_test",
					"en_US" => "radio_test"
				],
			"description" =>
				[
					"de_DE" => "radio_test_desc_de",
					"en_US" => "radio_test_desc_en"
				],
			"fields" => [
				[
					"name" => "enhRadio",
					"desc" => ["de_DE" => "radio description icon de", "en_US" => "radio description icon en"],
					"value" => "one",
				],
				[
					"name" => "enhRadio",
					"desc" => ["de_DE" => "radio description icon 2 de", "en_US" => "radio description 2 icon en"],
					"value" => "two",
					"selected" => true
				]
			],
		],
		[
			"type" => "text",
			"required" => true,
			"title" => [
				"de_DE" => "Beispiel Überschrift",
				"en_US" => "Example headline"
			],
			"description" => [
				"de_DE" => "Beispiel Beschreibung",
				"en_US" => "Example description"
			],
			"fields" => [
				[
					"name" => "enhText",
				]
			],
			"condition" =>
				[
					"item" => "enhRadio",
					"value" => 'two'
				]
		],
		[
			"type" => "checkbox",
			"title" =>
				[
					"de_DE" => "bool_test",
					"en_US" => "bool_test"
				],
			"description" =>
				[
					"de_DE" => "bool_test_desc_de",
					"en_US" => "bool_test_desc_en"
				],
			"fields" => [
				[
					"id" => "enhBool",
					"name" => "enhBool",
					"desc" => ["de_DE" => "bool description icon de", "en_US" => "bool description icon en"],
					"value" => 1,
					"required" => false,
				],
				[
					"id" => "enhBool2",
					"name" => "enhBool2",
					"desc" => ["de_DE" => "bool description icon 2 de", "en_US" => "bool description icon 2 en"],
					"value" => 1,
					"required" => false,
				]
			],
			"condition" =>
				[
					"item" => "enhRadio",
					"value" => 'two'
				]
		],
		[
			"type" => "textarea",
			"wysiwyg" => true,
			"required" => false,
			"title" => [
				"de_DE" => "Beispiel Überschrift 2",
				"en_US" => "Example headline 2"
			],
			"description" => [
				"de_DE" => "Beispiel Beschreibung 2",
				"en_US" => "Example description 2"
			],
			"fields" => [
				[
					"name" => "enhText2",
				]
			],
			"condition" =>
				[
					"item" => "enhBool",
					"value" => 1
				]
		]
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
			/*HookRegistry::register('supplementaryfilemetadataform::initdata', array($this, 'submissionMetadataInitData'));*/
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
			$submissionFile = $form->getSubmissionFile();
			if ($submissionFile)
				$form->setData('enhTest2', $submissionFile->getData('enhTest2'));
			$templateMgr->registerFilter("output", array($this, 'supplementaryMetadataFilter'));
		} else {
			$smarty =& $params[1];
			$output =& $params[2];
			$formFields = null;
			foreach ($this->json as $item) {
				$item['value'] = $smarty->get_template_vars($item['name']);
				$formFields[] = $item;
			}
			/*var_dump($formFields);*/
			$smarty->assign('formFields', $formFields);
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
		if (get_class($form) == 'SubmissionSubmitStep3Form') {
			$article = $form->submission;
		} elseif (get_class($form) == 'IssueEntrySubmissionReviewForm') {
			$article = $form->getSubmission();
		} elseif (get_class($form) == 'QuickSubmitForm') {
			$article = $form->submission;
		}
		if ($article) {
			$form->setData('enhTest', $article->getData('enhTest'));
		}
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
		if (get_class($form) == 'SubmissionSubmitStep3Form' ||
			get_class($form) == 'IssueEntrySubmissionReviewForm' ||
			get_class($form) == 'QuickSubmitForm') {
			$userVars[] = 'enhTest';
		} else if (get_class($form) == 'SupplementaryFileMetadataForm') {
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
		switch (get_class($form)) {
			case 'SubmissionSubmitStep3Form':
			case 'QuickSubmitForm':
				$article = $form->submission;
				break;
			case 'IssueEntrySubmissionReviewForm':
				$article = $form->getSubmission();
				break;
			case 'SupplementaryFileMetadataForm':
				$submissionFile = $form->getSubmissionFile();
				break;
		}
		if ($article != null)
			$article->setData('enhTest', $form->getData('enhTest'));
		if ($submissionFile != null) {
			$submissionFile->setData('enhTest2', $form->getData('enhTest2'));
		}
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
		switch (get_class($form)) {
			case 'ArticleDAO':
				$fields =& $params[1];
				$fields[] = 'enhTest';
				break;
			case 'SupplementaryFileDAODelegate':
				$fields =& $params[1];
				$fields[] = 'enhTest2';
				break;
		}
		return false;
	}

	function supplementaryMetadataFilter($output, $templateMgr)
	{
		if (preg_match('/<fieldset\s*id="\s*fileMetaData\s*"\s*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset + strlen($match));
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('supplementaryMetaData.tpl'));
			$newOutput .= substr($output, $offset + strlen($match));
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'supplementaryMetadataFilter'));
		}
		return $output;
	}


}
